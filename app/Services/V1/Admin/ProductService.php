<?php

namespace App\Services\V1\Admin;

use App\Helpers\MediaLibraryHelper;
use App\Models\GhorfeOnlineListProduct;
use App\Models\Product;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductService
{
    /**
     * Shipping methods the product form may offer: those of the current business, plus —
     * when editing — those of every business the product is listed in (a staff user with
     * no current business still sees the owner's methods).
     */
    public function shippingMethodsFor(?Product $product = null): Collection
    {
        $ghorfe_ids = $product
            ? GhorfeOnlineListProduct::where('product_id', $product->id)->pluck('ghorfe_online_list_id')
            : collect();

        if (ghorfe()) {
            $ghorfe_ids->push(ghorfe()->id);
        }

        return ShippingMethod::whereIn('ghorfe_online_list_id', $ghorfe_ids->unique()->all())
            ->orderBy('id')
            ->get();
    }

    /**
     * Save the «شرایط خرید» block of the product form (cash / installments / shipping
     * methods shown on the single-product page). Skipped when the form did not carry the
     * block, so other screens that post to the same action leave these fields alone.
     */
    public function savePurchaseInfo(Request $request, Product $product): void
    {
        $info = $request->input('purchase_info');
        if (empty($info['present'])) {
            return;
        }

        $product->update([
            'cash_purchase'             => !empty($info['cash_purchase']),
            'cash_purchase_note'        => trim($info['cash_purchase_note'] ?? '') ?: null,
            'installment_purchase'      => !empty($info['installment_purchase']),
            'installment_purchase_note' => trim($info['installment_purchase_note'] ?? '') ?: null,
        ]);

        // only methods this form could actually have offered are accepted
        $allowed = $this->shippingMethodsFor($product)->pluck('id')->all();
        $ids = array_values(array_intersect(array_map('intval', (array) ($info['shipping_method_ids'] ?? [])), $allowed));

        // keep links to other businesses' methods this user could not see in the form
        $hidden = $product->shippingMethods()->whereNotIn('shipping_methods.id', $allowed)->pluck('shipping_methods.id')->all();

        $product->shippingMethods()->sync(array_merge($ids, $hidden));
    }

    public function createGhorfeProduct($data) : void
    {
        GhorfeOnlineListProduct::create([
            'user_id' => auth()->user()->id,
            'product_id' => $data['product_id'],
            'ghorfe_online_list_id' => ghorfe()->id,
            'purchase_type' => $data['purchase_type'],
            'pay_type' => $data['pay_type'],
            'tariff_id' => null,
            'guarantee' => $data['guarantee'],
        ]);
    }
    public function saveImageProduct($request,$product) : void
    {
        // $image['name'] is the temp media id (media_id) from the ajax uploader.
        // The item flagged as main (its radio submits an 'index') goes to the
        // single-file 'image' collection; the rest go to the multi 'gallery'.
        $images = $request->input('image_name_good');
        if (! $images) {
            return;
        }
        foreach ($images as $image) {
            if (empty($image['name'])) {
                continue;
            }
            if (isset($image['index'])) {
                MediaLibraryHelper::attachTo($product, $image['name'], 'image');
            } else {
                MediaLibraryHelper::attachTo($product, $image['name'], 'gallery', [
                    'title' => $request->input('good.name'),
                ]);
            }
        }
    }
}
