<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductOrderCollection;
use App\Models\Address;
use App\Models\Area;
use App\Models\Category;
use App\Models\Factor;
use App\Models\FactorAddress;
use App\Models\Province;
use App\Services\V1\Admin\ContractResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * "مدیریت سفارشات کالا" - the product-order lifecycle:
 *
 *   پرداخت موفق -> سفارش ایجاد شد (در انتظار ارسال) -> تعیین آدرس و هزینه ارسال -> ارسال شده
 *
 * An order IS the paid factor that carries کالا rows (factor_items -> factor_item_products);
 * no separate order record is created at payment time, the invoice already is one. Its
 * shipment state lives on `factors` (shipping_*) and its shipping address is snapshotted
 * into `factor_addresses` so later edits to the user's address book cannot rewrite the
 * address of an order that already went out.
 *
 * Once shipping_status reaches SHIPPING_SHIPPED the order is closed: address, shipping cost
 * and tracking code all stop accepting writes (guardEditable()).
 */
class ProductOrderController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        //parent_id 1 is the کالا root - the same source the shared product filter uses
        $categories = Category::filter()->where('parent_id', 1)->where('isActive', 1)->get();
        $statuses = Factor::SHIPPING_STATUS;

        return view('Admin.productOrder.all', compact('categories', 'statuses'));
    }

    /**
     * @param Request $request
     * @return ProductOrderCollection
     */
    public function filter(Request $request)
    {
        $orders = Factor::productOrder()->orderFilter();

        //taken before paginate(), which puts LIMIT/OFFSET on this very builder - summing
        //afterwards returns nothing at all from page 2 on
        $total_price = (clone $orders)->sum(DB::raw('price + shipping_cost'));

        $paginated = $orders->with([
            'user',
            'factorAddress',
            'factorItems.products.product',
        ])->latest()->paginate(20);

        return new ProductOrderCollection($total_price, $paginated);
    }

    /**
     * @param Factor $factor
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function show(Factor $factor)
    {
        $this->guardIsOrder($factor);

        $factor->load([
            'user',
            'factorAddress',
            'shippedBy',
            'factorItems.factoritemable',
            'factorItems.products.product',
            'factorItems.products.productAttribute',
        ]);

        //the user's address book, which the admin picks from / edits on this page. It is
        //intentionally read live: it is the source to copy FROM, never what the order shows.
        $addresses = Address::where('user_id', $factor->user_id)->latest()->get();
        $provinces = Province::orderBy('name')->get();
        $areas = Area::orderBy('number')->get();

        // فاکتور خرید: the contract attached to this order, resolved through the
        // ContractUsage relation rather than copied onto the factor.
        $contractUsage = app(ContractResolver::class)->forFactor($factor);

        return view('Admin.productOrder.show', compact('factor', 'addresses', 'provinces', 'areas', 'contractUsage'));
    }

    /**
     * Pick one of the user's addresses as the shipping address of this order. The row is
     * copied into `factor_addresses`; `factors.address_id` keeps pointing at the source so
     * the radio button stays selected on the next render.
     *
     * @param Factor $factor
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setAddress(Factor $factor, Request $request)
    {
        $this->guardIsOrder($factor);

        if ($locked = $this->guardEditable($factor)) {
            return $locked;
        }

        $this->validate($request, [
            'address_id' => 'required|integer|exists:addresses,id',
        ]);

        $address = Address::findOrFail($request->input('address_id'));
        $this->guardOwnsAddress($factor, $address);

        $this->snapshotAddress($factor, $address);

        return redirect()->back()->with(['successByMessage' => 'آدرس ارسال سفارش ثبت شد.']);
    }

    /**
     * Add a new address to the buyer's address book and make it this order's shipping
     * address straight away - that is the only reason an admin adds one here.
     *
     * @param Factor $factor
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeAddress(Factor $factor, Request $request)
    {
        $this->guardIsOrder($factor);

        if ($locked = $this->guardEditable($factor)) {
            return $locked;
        }

        $inputs = $this->validate($request, $this->addressRules());

        $address = Address::create(array_merge($inputs, [
            'user_id' => $factor->user_id,
            'lat' => $request->input('lat', ''),
            'lon' => $request->input('lon', ''),
        ]));

        $this->snapshotAddress($factor, $address);

        return redirect()->back()->with(['successByMessage' => 'آدرس جدید ثبت و به عنوان آدرس ارسال انتخاب شد.']);
    }

    /**
     * Edit one of the buyer's addresses.
     *
     * This never rewrites the address of an already-shipped order - those keep their own
     * `factor_addresses` copy. It DOES refresh the snapshot of THIS order when the edited
     * address is the one currently selected on it, because that is the admin correcting the
     * address they are about to ship to.
     *
     * @param Factor $factor
     * @param Address $address
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAddress(Factor $factor, Address $address, Request $request)
    {
        $this->guardIsOrder($factor);
        $this->guardOwnsAddress($factor, $address);

        if ($locked = $this->guardEditable($factor)) {
            return $locked;
        }

        $inputs = $this->validate($request, $this->addressRules());

        $address->update($inputs);

        if ((int) $factor->address_id === (int) $address->id) {
            $this->snapshotAddress($factor, $address->refresh());
        }

        return redirect()->back()->with(['successByMessage' => 'آدرس با موفقیت ویرایش شد.']);
    }

    /**
     * Remove an address from the buyer's address book.
     *
     * Previous orders are untouched: their address lives in `factor_addresses`. If the
     * deleted address was the one selected for THIS (not yet shipped) order, only the
     * pointer is cleared - the snapshot is dropped too, so the order goes back to
     * "آدرس ثبت نشده" and cannot be shipped until a new one is chosen.
     *
     * @param Factor $factor
     * @param Address $address
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function destroyAddress(Factor $factor, Address $address, Request $request)
    {
        $this->guardIsOrder($factor);
        $this->guardOwnsAddress($factor, $address);

        if ($locked = $this->guardEditable($factor)) {
            return $locked;
        }

        DB::transaction(function () use ($factor, $address) {
            if ((int) $factor->address_id === (int) $address->id) {
                $factor->factorAddress()->delete();
                $factor->update(['address_id' => null]);
            }
            $address->delete();
        });

        if ($request->ajax()) {
            return response(['status' => 'success', 'message' => 'آدرس حذف شد.']);
        }

        return redirect()->back()->with(['successByMessage' => 'آدرس حذف شد.']);
    }

    /**
     * @param Factor $factor
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setShippingCost(Factor $factor, Request $request)
    {
        $this->guardIsOrder($factor);

        if ($locked = $this->guardEditable($factor)) {
            return $locked;
        }

        //the input is thousand-separated by the shared price mask
        $request->merge(['shipping_cost' => str_replace(',', '', (string) $request->input('shipping_cost'))]);

        $this->validate($request, [
            'shipping_cost' => 'required|numeric|min:0',
        ]);

        $factor->update(['shipping_cost' => (int) $request->input('shipping_cost')]);

        return redirect()->back()->with(['successByMessage' => 'هزینه ارسال ثبت شد.']);
    }

    /**
     * Close the order: mark it shipped, stamp the moment and store the tracking code.
     *
     * Shipping without an address is refused here and not only in the UI - the snapshot is
     * what a courier actually receives, so an order with none is not shippable.
     *
     * @param Factor $factor
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function ship(Factor $factor, Request $request)
    {
        $this->guardIsOrder($factor);

        if ($locked = $this->guardEditable($factor)) {
            return $locked;
        }

        $this->validate($request, [
            'tracking_code' => 'nullable|string|max:191',
        ]);

        if (!$factor->factorAddress()->exists()) {
            return redirect()->back()->with([
                'failByMessage' => 'برای ارسال سفارش ابتدا باید آدرس ارسال ثبت شود.',
            ]);
        }

        $factor->update([
            'shipping_status' => Factor::SHIPPING_SHIPPED,
            'shipped_at' => now(),
            'shipped_by' => auth()->user()->id,
            'tracking_code' => $request->input('tracking_code'),
        ]);

        return redirect()->back()->with(['successByMessage' => 'سفارش ارسال شد.']);
    }

    /**
     * Copy the address onto the order and remember which one it came from.
     *
     * @param Factor $factor
     * @param Address $address
     * @return void
     */
    private function snapshotAddress(Factor $factor, Address $address): void
    {
        DB::transaction(function () use ($factor, $address) {
            FactorAddress::updateOrCreate(
                ['factor_id' => $factor->id],
                FactorAddress::snapshotOf($address)
            );
            $factor->update(['address_id' => $address->id]);
        });
    }

    /**
     * @return array
     */
    private function addressRules(): array
    {
        return [
            'province_id' => 'required|integer|exists:provinces,id',
            'city_id' => 'required|integer|exists:cities,id',
            //checked against the table, not just cast: `addresses` carries foreign keys onto
            //provinces/cities/areas on server installs, where a stale id would be a 500
            //instead of a validation message
            'area_id' => 'nullable|integer|exists:areas,id',
            'address' => 'required|string',
            'plaque' => 'required|numeric',
            'unit' => 'nullable|numeric',
            'postal_code' => 'required|string|max:191',
            'recipient_name' => 'required|string|max:191',
            'recipient_family' => 'required|string|max:191',
            'recipient_mobile' => 'required|string|max:191',
        ];
    }

    /**
     * Only a paid invoice carrying کالا rows is an order.
     *
     * @param Factor $factor
     * @return void
     */
    private function guardIsOrder(Factor $factor): void
    {
        $is_order = (int) $factor->status === 1
            && $factor->factorItems()->whereHas('products')->exists();

        abort_unless($is_order, 404);
    }

    /**
     * A shipped order is closed - nothing about its shipment may change any more.
     *
     * @param Factor $factor
     * @return \Illuminate\Http\RedirectResponse|null
     */
    private function guardEditable(Factor $factor)
    {
        if (!$factor->is_shipped) {
            return null;
        }

        return redirect()->back()->with([
            'failByMessage' => 'این سفارش ارسال شده است و قابل تغییر نیست.',
        ]);
    }

    /**
     * @param Factor $factor
     * @param Address $address
     * @return void
     */
    private function guardOwnsAddress(Factor $factor, Address $address): void
    {
        abort_unless((int) $address->user_id === (int) $factor->user_id, 403);
    }
}
