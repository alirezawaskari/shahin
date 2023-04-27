<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Models\PurchaseInvoice;
use App\Models\Representation;
use App\Models\CategoryStoreRoom;
use App\Models\StoreRoom;
use App\Models\CountingUnit;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductStoreRoom;
use App\Models\PurchaseInvoiceInstallment;
use App\Helpers\Helper;
use App\Http\Resources\PurchaseInvoiceCollection;
use App\Models\Factor;
use App\Models\PurchaseInvoiceFactor;
use Illuminate\Support\Arr;

class PurchaseInvoiceController extends Controller {
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function index() {
        $user = auth()->user();
        $delete = false;
        $suppliers = Supplier::all();
        if ( $user->isAbleTo( 'purchase-invoice' || 'purchase-invoice-detail' ) ) {
            $title = 'فهرست فاکتورهای خرید';
            $url = 'filter';
            if ( $user->isAbleTo( 'purchase-invoice-delete' ) ) {
                $delete = true;
            }
            return view( 'Admin.purchaseinvoice.all', compact( 'suppliers', 'title', 'url', 'delete' ) );
        }
        return abort( 401 );
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function desktop() {
        $user = auth()->user();
        $delete = false;
        $suppliers = Supplier::all();
        if ( $user->isAbleTo( ['purchase-invoice','purchase-invoice-detail'] ) ) {
            $title = 'میزکار فاکتورهای خرید';
            $url = 'desktop/filter';
            if ( $user->isAbleTo( 'purchase-invoice-delete' ) ) {
                $delete = true;
            }
            return view( 'Admin.purchaseinvoice.all', compact( 'suppliers', 'title', 'url', 'delete' ) );
        }
        return abort( 401 );
    }

    /**
     * @return PurchaseInvoiceCollection
     */
    public function filter() {
        $user = auth()->user();
        $purchase_invoices = PurchaseInvoice::filter()->latest();

        if ( $user->hasPermission( 'purchase-invoice' ) ) {
            $purchase_invoices = PurchaseInvoice::where( 'supplier_id', $user->supplier->id )->filter()->latest();
        }

        return new PurchaseInvoiceCollection( $purchase_invoices, $purchase_invoices->paginate( 20 ) );
    }

    /**
     * @return PurchaseInvoiceCollection
     */
    public function filterDesktop() {
        $user = auth()->user();
        if ( $user->hasPermission( 'purchase-invoice' ) ) {
            $purchase_invoices = PurchaseInvoice::where( 'supplier_id', $user->supplier->id )->whereIn( 'status', [ 0, 5, 7 ] )->filter()->latest();
        }
        if ( auth()->user()->hasRole( 'mali' ) ) {
            $purchase_invoices = PurchaseInvoice::whereIn( 'status', [ 1, 9 ] )->filter()->latest();
        }
        if ( auth()->user()->hasRole( 'superadmin' ) ) {
            $purchase_invoices = PurchaseInvoice::where( 'status', 2 )->filter()->latest();
        }
        if ( auth()->user()->hasRole( 'mali-representation' ) ) {
            $purchase_invoices = PurchaseInvoice::where( 'status', 3 )->filter()->latest();
        }
        if ( auth()->user()->hasRole( 'superadmin-representation' ) ) {
            $purchase_invoices = PurchaseInvoice::where( 'status', 4 )->filter()->latest();
        }
        if ( auth()->user()->hasRole( 'warehouse-keeper' ) ) {
            $purchase_invoices = PurchaseInvoice::where( 'status', 6 )->filter()->latest();
        }

        return new PurchaseInvoiceCollection( $purchase_invoices, $purchase_invoices->paginate( 20 ) );
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create() {
        $representations = Representation::whereStatus( true )->get();
        $suppliers = Supplier::all();
        return view( 'Admin.purchaseinvoice.create', compact( 'representations', 'suppliers' ) );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request ) {
        $inputs =  $request->all();

        $purchase_invoices = PurchaseInvoice::create( $inputs );

        //cache
        Cache::flush();

        return redirect( route( 'purchaseInvoice.index' ) )->with( [
            'create' => 'success'
        ] );
    }

    /**
     * @param PurchaseInvoice $purchaseInvoice
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(PurchaseInvoice $purchaseInvoice ) {
        $user = auth()->user();
        $reports = $purchaseInvoice->reports;
        $representations = Representation::whereStatus( true )->get();
        $purchase_invoice_factors = PurchaseInvoiceFactor::where('purchase_invoice_id' , $purchaseInvoice->id)->with('factor')->get();
        $financial_factors = self::getSumPricePaidFactors($purchase_invoice_factors);
        $suppliers = Supplier::all();
        $parents = Category::where( 'parent_id', 0 )->get();
        $installments = $purchaseInvoice->purchaseInvoiceInstallments;
        $status = PurchaseInvoice::status( $purchaseInvoice->status );
        $status_class = PurchaseInvoice::getClassByStatus( $purchaseInvoice->status );
        return view( 'Admin.purchaseinvoice.edit', compact('financial_factors' , 'purchase_invoice_factors' , 'installments', 'parents', 'reports', 'user', 'purchaseInvoice', 'representations', 'suppliers', 'status', 'status_class' ) );
    }

    /**
     * @param Request $request
     * @param PurchaseInvoice $purchaseInvoice
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, PurchaseInvoice $purchaseInvoice ) {
        if ( auth()->user()->hasRole( 'supplier' || 'mali' ) ) {
            $purchaseInvoice->update( [
                'status' => 1,
            ] );
        }
        //upload image factor
        $factor_images = isset( $purchaseInvoice->factor_image ) ? $purchaseInvoice->factor_image : [];
        $url_factor_image = $request->url_factor_image;
        $factor_images = isset( $purchaseInvoice->factor_image ) ? $purchaseInvoice->factor_image : [];
        if ( isset( $url_factor_image ) ) {
            foreach ( $url_factor_image as $file ) {
                if ( $file ) {
                    if ( isset( $factor_images ) ) {
                        $url = Helper::moveImage($file,'document');
                        $factor_images[] = $url;
                    }
                }
            }
        }

        $inputs = $request->all();
        $inputs[ 'total_price' ] = Helper::numberConverter( ( float ) str_replace( ',', '', $request->total_price ) );
        $inputs[ 'discount' ] = Helper::numberConverter( ( float ) str_replace( ',', '', $request->discount ) );
        $inputs[ 'prepayment' ] = Helper::numberConverter( ( float ) str_replace( ',', '', $request->prepayment ) );
        $inatallments_price = [];
        foreach ( $request->payments[ 'price' ] as $price ) {
            $inatallments_price[] = Helper::numberConverter( ( float ) str_replace( ',', '', $price ) );
        }
        unset( $inputs[ 'payments' ] );
        unset( $inputs[ 'url_factor_image' ] );
        $purchaseInvoice->update( $inputs );
        $purchaseInvoice->update( [ 'factor_image' => $factor_images ] );

        $sum_price = array_sum( $inatallments_price ) + $purchaseInvoice->purchaseInvoiceInstallments->sum( 'price' ) + $inputs[ 'prepayment' ];

        if ( $inputs[ 'total_price' ] != $sum_price ) {
            return redirect()->back()->with( [
                'failByMessage' => 'جمع فاکتور با پرداختی های ثبت شده مطابقت ندارد.'
            ] );
        }
        self::createInstallments( $request->payments, $purchaseInvoice );

        //cache
        Cache::flush();
        return redirect( route( 'purchaseInvoice.index' ) )->with( [
            'update' => 'success'
        ] );
    }

    /**
     * @param Request $request
     * @param PurchaseInvoice $purchaseInvoice
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function confirm(Request $request, PurchaseInvoice $purchaseInvoice ) {
        //approve the factor and register the signature of the mali manager
        if ( auth()->user()->hasRole( 'mali' ) ) {
            $values = $purchaseInvoice->values ? $purchaseInvoice->values : [];
            $values = Arr::add( $values, 'mali', auth()->user()->id );
            $purchaseInvoice->update( [
                'values' => $values,
                'status' => 2,
            ] );
        }
        //approve the factor and register the signature of the CEO
        if ( auth()->user()->hasRole( 'superadmin' ) && ( $purchaseInvoice->status == 2 || $purchaseInvoice->status == 1 ) ) {
            $values = $purchaseInvoice->values ? $purchaseInvoice->values : [];
            $values = Arr::add( $values, 'superadmin', auth()->user()->id );
            $purchaseInvoice->update( [
                'values' => $values,
                'status' => $purchaseInvoice->pay_time ? 5 : 9,
            ] );
        } elseif ( auth()->user()->hasRole( 'superadmin' ) && $purchaseInvoice->status == 6 ) {
            $values = $purchaseInvoice->values ? $purchaseInvoice->values : [];
            $values = Arr::add( $values, 'warehouse-keeper', auth()->user()->id );
            $purchaseInvoice->update( [
                'values' => $values,
                'status' => 8,
            ] );
        }
        //approve the factor and register the signature of the representation mali manager
        if ( auth()->user()->hasRole( 'mali_representation' ) ) {
            $values = $purchaseInvoice->values ? $purchaseInvoice->values : [];
            $values = Arr::add( $values, 'mali_representation', auth()->user()->id );
            $purchaseInvoice->update( [
                'values' => $values,
                'status' => 4,
            ] );
        }
        //approve the file and register the signature of the representation CEO
        if ( auth()->user()->hasRole( 'superadmin_representation' ) ) {
            $values = $purchaseInvoice->values ? $purchaseInvoice->values : [];
            $values = Arr::add( $values, 'superadmin_representation', auth()->user()->id );
            $purchaseInvoice->update( [
                'values' => $values,
                'status' => 9,
            ] );
        }
        //approve the factor and register the signature of the warehouse keeper
        if ( auth()->user()->hasRole( 'warehouse-keeper' ) ) {
            $values = $purchaseInvoice->values ? $purchaseInvoice->values : [];
            $values = Arr::add( $values, 'warehouse-keeper', auth()->user()->id );
            $purchaseInvoice->update( [
                'values' => $values,
                'status' => 8,
            ] );
        }

        return redirect( route( 'purchaseInvoice.index' ) )->with( [
            'update' => 'success'
        ] );
    }

    /**
     * @param PurchaseInvoice $purchaseInvoice
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function createProduct(PurchaseInvoice $purchaseInvoice ) {
        $products = Product::where( 'type', '!=', 2 )->latest()->get();
        $category_store_rooms = CategoryStoreRoom::all();
        $store_rooms = StoreRoom::all();
        $counting_units = CountingUnit::all();
        $parents = Category::where( 'parent_id', 0 )->get();
        $purchase_invoice_products = $purchaseInvoice->product_storeRoom;

        return view( 'Admin.purchaseinvoice.product', compact( 'purchase_invoice_products', 'purchaseInvoice', 'products', 'parents', 'store_rooms', 'category_store_rooms', 'counting_units' ) );
    }

    /**
     * @param Request $request
     * @param PurchaseInvoice $purchaseInvoice
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeProduct(Request $request, PurchaseInvoice $purchaseInvoice ) {
        $this->validate( request(), [
            'storage_num' => 'required',
            'row_num' => 'required',
            'col_num' => 'required',
            'purchase_price' => 'required',
            'stock' => 'required',
        ] );

        $inputs = $request->all();
        $inputs[ 'purchase_price' ] = ( float ) str_replace( ',', '', $request->purchase_price );
        $inputs[ 'sales_price' ] = ( float ) str_replace( ',', '', $request->sales_price );
        $inputs[ 'discount' ] = ( float ) str_replace( ',', '', $request->discount );
        $expire_date = Helper::vertaToMiladi( Helper::numberConverter( $request->expire_date ) );
        $inputs[ 'expire_date' ] = $expire_date;

        ProductStoreRoom::create( $inputs );
        return redirect( url( 'dashboard/create-product-purchaseInvoice', [ 'id' => $purchaseInvoice->id ] ) )->with( [
            'purchase_invoice_update' => 'success'
        ] );
    }

    /**
     * @param Request $request
     * @param PurchaseInvoice $purchaseInvoice
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function sendProduct(Request $request, PurchaseInvoice $purchaseInvoice ) {
        //upload factor post image
        if ( $request->url_post_image ) {
            $purchaseInvoice->update( [ 'post_image' => Helper::moveImage($request->url_post_image,'document'), 'postage' => Helper::numberConverter( ( float ) str_replace( ',', '', $request->postage ) ), 'status' => 6 ] );
        }

        return redirect( route( 'purchaseInvoice.index' ) )->with( [
            'update' => 'success'
        ] );
    }

    /**
     * @param Request $request
     * @param PurchaseInvoice $purchaseInvoice
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function saveReport(Request $request, PurchaseInvoice $purchaseInvoice ) {
        $purchaseInvoice->reports()->create( [
            'user_id' => auth()->user()->id,
            'report' => nl2br( $request->input( 'report' ) ),
        ] );
        $purchaseInvoice->update( [ 'status' => 7 ] );
        return redirect( route( 'purchaseInvoice.index' ) )->with( [
            'update' => 'success'
        ] );
    }

    /**
     * @param PurchaseInvoice $purchaseInvoice
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function reject(PurchaseInvoice $purchaseInvoice ) {
        $purchaseInvoice->update( [ 'status' => 10 ] );
        return redirect( route( 'purchaseInvoice.index' ) )->with( [
            'update' => 'success'
        ] );
    }

    /**
     * @param Request $request
     * @param PurchaseInvoice $purchaseInvoice
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function changeStatus(Request $request, PurchaseInvoice $purchaseInvoice ) {
        $purchaseInvoice->update( $request->all() );

        return redirect( route( 'purchaseInvoice.index' ) )->with( [
            'update' => 'success'
        ] );
    }

    /**
     * @param PurchaseInvoice $purchaseInvoice
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function destroy(PurchaseInvoice $purchaseInvoice ) {
        if ( auth()->user()->isAbleTo( 'purchase-invoice-delete' ) ) {
            $purchaseInvoice->delete();
            return response( [
                'operator' => true,
                'status'=>'success',
                'message'=>'حذف با موفقیت انجام گردید .'
            ] );
        } else {
            return response( [
                'operator' => true,
                'status'=>'unauthorized',
                'message'=>'شما مجاز به انجام این کار نیستید.'
            ] );
        }
    }

    /**
     * @param $payments
     * @param $purchaseInvoice
     * @return bool
     */
    private function createInstallments($payments, $purchaseInvoice ) {
        for ( $i =  1; $i < ( $payments[ 'price' ] ? count( $payments[ 'price' ] ) : 0 );
        $i++ ) {

            PurchaseInvoiceInstallment::create( [
                'purchase_invoice_id' => $purchaseInvoice->id,
                'price' => ( float ) str_replace( ',', '', Helper::numberConverter( $payments[ 'price' ][ $i-1 ] ) ),
                'date' => Helper::vertaToMiladi( Helper::numberConverter( $payments[ 'date' ][ $i-1 ] ) ),
            ] );
        }

        return true;
    }

    /**
     * @param Request $request
     * @param PurchaseInvoice $purchaseInvoice
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function savePayment(Request $request, PurchaseInvoice $purchaseInvoice ) {
        $user = $purchaseInvoice->supplier->user_id;

        $factor = new Factor();
        $factor->title = 'purchase_invoice_payment';
        $factor->user_id = $user;
        $factor->price = ( float ) str_replace( ',', '', Helper::numberConverter( $request->input( 'price' ) ) );
        $factor->type = $request->input( 'payment_type' );
        $factor->callcenter_id = auth()->user()->id;
        $factor->number_card = $request->input( 'number_card' );
        $factor->payment_code = $request->input( 'payment_code' );
        $factor->representation_id = auth()->user()->representation_id;
        $factor->status = 5;
        $factor->created_at = Helper::vertaToMiladi( Helper::numberConverter( $request->payment_date ) );
        $factor->save();

        //upload bill_image cash factor
        if ($request->url_bill_image) {
            $factor->update(['bill_image' => Helper::moveImage($request->url_bill_image,'document')]);
        }
        $purchaseInvoice->purchaseInvoiceFactors()->create(['factor_id' => $factor->id]);
        self::savePaidInstallments( $purchaseInvoice, $factor );
        return redirect( route( 'purchaseInvoice.index' ) )->with( [
            'update' => 'success'
        ] );
    }

    /**
     * @param Request $request
     * @param PurchaseInvoice $purchaseInvoice
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function returnPayment(Request $request, PurchaseInvoice $purchaseInvoice ) {
        $user = $purchaseInvoice->supplier->user_id;

        $factor = new Factor();
        $factor->title = 'purchase_invoice_payment';
        $factor->user_id = $user;
        $factor->price = ( float ) str_replace( ',', '', Helper::numberConverter( $request->input( 'price' ) ) );
        $factor->type = $request->input( 'payment_type' );
        $factor->callcenter_id = auth()->user()->id;
        $factor->number_card = $request->input( 'number_card' );
        $factor->payment_code = $request->input( 'payment_code' );
        $factor->representation_id = auth()->user()->representation_id;
        $factor->status = 4;
        $factor->created_at = Helper::vertaToMiladi( Helper::numberConverter( $request->payment_date ) );
        $factor->save();

        //upload bill_image cash factor
        if ($request->url_bill_image) {
            $factor->update(['bill_image' => Helper::moveImage($request->url_bill_image,'document')]);
        }
        $purchaseInvoice->purchaseInvoiceFactors()->create(['factor_id' => $factor->id]);
        self::returnPaidInstallments( $purchaseInvoice, $factor );
        return redirect( route( 'purchaseInvoice.index' ) )->with( [
            'update' => 'success'
        ] );
    }

    /**
     * @param $purchase_invoice
     * @param $factor
     */
    private function savePaidInstallments($purchase_invoice, $factor ) {
        $extra_pay_price = $purchase_invoice->extra_pay;
        $un_paid_installment = $purchase_invoice->purchaseInvoiceInstallments()->where( 'status', 0 )->orderBy( 'id', 'ASC' )->first();
        if ( $un_paid_installment && $factor->price + $extra_pay_price >= $un_paid_installment->price ) {
            $purchase_invoice->update(['status' => $purchase_invoice->status == 9 ? 5 : $purchase_invoice->status]);
            $un_paid_installment->update( [
                'factor_id' => $factor->id,
                'status' => 1,
                'pay_date' => $factor->created_at,
            ] );

            $extra_pay_price = $extra_pay_price + $factor->price - $un_paid_installment->price;
            for ( $j = 0; $extra_pay_price >= $un_paid_installment->price; $j++ ) {
                $un_paid_installment = $purchase_invoice->purchaseInvoiceInstallments()->where( 'status', 0 )->orderBy( 'id', 'ASC' )->first();
                if($un_paid_installment)
                {
                    $un_paid_installment->update( [
                        'factor_id' => $factor->id,
                        'status' => 1,
                        'pay_date' => $factor->created_at,
                    ] );
                    $extra_pay_price = $extra_pay_price - $un_paid_installment->price;
                }
            }
        } else {
            $extra_pay_price = $extra_pay_price + $factor->price;
        }
        $purchase_invoice->update(['extra_pay' => $extra_pay_price]);
    }

    /**
     * @param $purchase_invoice
     * @param $factor
     */
    private function returnPaidInstallments($purchase_invoice, $factor)
    {
        $extra_pay_price = $purchase_invoice->extra_pay;
        $paid_installment = $purchase_invoice->purchaseInvoiceInstallments()->where( 'status', 1 )->orderBy( 'id', 'DESC' )->first();
        if ( $paid_installment && $extra_pay_price - $factor->price < 0 ) {
            $return_paid_price = $factor->price - $extra_pay_price;
            $paid_installment->update( [
                'factor_id' => null,
                'status' => 0,
                'pay_date' => null,
            ] );
            $return_paid_price = $return_paid_price - $paid_installment->price;
            $extra_pay_price = $return_paid_price < 0 ? $paid_installment->price - $return_paid_price : 0;
            for ( $j = 0; $return_paid_price > 0 ; $j++ ) {
                $paid_installment = $purchase_invoice->purchaseInvoiceInstallments()->where( 'status', 1 )->orderBy( 'id', 'DESC' )->first();
                $paid_installment->update( [
                    'factor_id' => null,
                    'status' => 0,
                    'pay_date' => null,
                ] );
                $return_paid_price = $return_paid_price - $paid_installment->price;
                $extra_pay_price = $return_paid_price < 0 ? $paid_installment->price - $return_paid_price : 0;
            }
        }
        else{
            $extra_pay_price = $extra_pay_price - $factor->price;
        }
        $purchase_invoice->update(['extra_pay' => $extra_pay_price]);
    }

    /**
     * @param $purchase_invoice_factors
     * @return array
     */
    private function getSumPricePaidFactors($purchase_invoice_factors)
    {
        $paid_factors = $purchase_invoice_factors->where('factor.status' , 5)->sum('factor.price');
        $return_payment_factors = $purchase_invoice_factors->where('factor.status' , 4)->sum('factor.price');
        $sum_paid_factors =  $paid_factors - $return_payment_factors;

        $result['paid_factors'] = $paid_factors;
        $result['return_payment_factors'] = $return_payment_factors;
        $result['sum_paid_factors'] = $sum_paid_factors;

        return $result;
    }
}
