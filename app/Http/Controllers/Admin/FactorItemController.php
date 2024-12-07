<?php

namespace App\Http\Controllers\Admin;

use App\Classes\External\IranSms;
use App\Events\factorItemClosing;
use App\Events\factorItemStartWorking;
use App\Events\referralCenterNotification;
use App\Events\sendlinkFactor;
use App\Events\smsNotAnswer;
use App\Events\smsService;
use App\Exports\FactorItemsExport;
use App\Helpers\CalculationHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\CheckResource;
use App\Http\Resources\FactorItemCollection;
use App\Models\Factor;
use App\Models\FactorItem;
use App\Models\FactorItemProduct;
use App\Models\FactorItemServiceTariff;
use App\Models\FinancialPlansType;
use App\Models\PercentageAllocationEmployee;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceTariff;
use App\Models\Session;
use App\Models\Tariff;
use App\Models\TotalFactorItem;
use App\Models\User;
use App\Models\UserPerformance;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FactorItemController extends Controller {

    //all factorItems list
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index() {
        $title = 'فهرست پرونده ها';
        $url = 'filter';
        $supporter_users=User::where('level' , 'admin')->where('admin_representation_id',auth()->user()->admin_representation_id)->whereHas('roles',function ($query){
            $query->where('name','admin-site');
        })->get();

        return view( 'Admin.factoritem.all' , compact('title' , 'url' , 'supporter_users'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function desktop() {
        $title = 'میزکار پرونده ها';
        $url = 'desktop/filter';
        $supporter_users=User::where('level' , 'admin')->where('admin_representation_id',auth()->user()->admin_representation_id)->whereHas('roles',function ($query){
            $query->where('name','admin-site');
        })->get();
        return view( 'Admin.factoritem.all' , compact('title' , 'url' , 'supporter_users'));
    }

    /**
     * @param FactorItem $factorItem
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse
     */
    public function show(FactorItem $factorItem ) {
        if($factorItem->type)
        {
            return redirect()->to('/dashboard/totalFactorItem/'.$factorItem->supervisor->total_factor_item_id);
        }
        //show detail file
        $user = $factorItem->factor->user;
        $result = self::setItem( $factorItem );
        $factorItem = $result['factorItem'];
        $item = $result['item'];
        $admin_users = User::where('center_id', $factorItem->center_id)->where('center_id', '!=' , null)->whereHas('employee', function ($query) {
            $query->where('show_status', 1);
        })->get();
        $center_agents = [];
        foreach ( $admin_users as $u ) {
            if ( $u->hasRole( 'doctor' ) ) {
                $center_agents[] = $u;
            }
        }

        return view( 'Admin.factoritem.show', compact('item', 'center_agents', 'factorItem', 'user' ) );
    }

    /**
     * @param FactorItem $factorItem
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(FactorItem $factorItem ) {
        $admin_users = User::where('center_id', $factorItem->center_id)->where('center_id', '!=' , null)->whereHas('employee', function ($query) {
            $query->where('show_status', 1);
        })->get();
        $center_agents = [];
        $assistants = [];
        $result = self::setItem( $factorItem );
        $factorItem = $result['factorItem'];
        $item = $result['item'];
        foreach ( $admin_users as $u ) {
            if ($u->hasRole('assistant') || $u->hasRole('supervisor') || $u->hasRole('center-manager')) {
                $assistants[] = $u;
            }
            if ( $u->hasRole( 'doctor' ) ) {
                $center_agents[] = $u;
            }
        }
        $user = $factorItem->factor->user;
        $tariffs = Tariff::where('service_id' , $factorItem->factoritemable_id)->orderBy('id' , 'desc')->get();
        $center_service_tariffs = $factorItem->center ? $factorItem->center->serviceTariffs : [];
        $products = $factorItem->center ? $factorItem->center->products : [];
        return view( 'Admin.factoritem.edit', compact('item' , 'tariffs' , 'assistants', 'center_agents', 'products', 'center_service_tariffs', 'factorItem', 'user' ) );
    }

    /**
     * @param Request $request
     * @return FactorItemCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter(Request $request ) {

        $factor_items = FactorItem::filter()->latest();
        if ( auth()->user()->center_id ) {
            $factor_items = FactorItem::where( 'confirm', true )->filter();
        }
        if ( $request->exel == 'on' ) {
            return ( new FactorItemsExport( $factor_items ) )->download( 'factorItems.xlsx' );
        }

        return new FactorItemCollection( $factor_items->paginate( 20 ) );
    }

    /**
     * @param Request $request
     * @return FactorItemCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterDesktop(Request $request ) {
        if ( auth()->user()->center_id ) {
            $factor_items = FactorItem::where( 'status_factor', 10 )->filter();
        }
        elseif (auth()->user()->isAbleTo('file-desktop')){
            $factor_items = FactorItem::whereIn( 'status_factor', array(0,2,4,14) )->where('supporter_id', auth()->user()->id)->filter();
        }
        if (auth()->user()->hasPermission('factor_item_referral_to_center'))
        {
            $factor_items = FactorItem::where( 'status_factor', 16 )->filter();
        }
        if ( $request->exel == 'true' ) {
            return ( new FactorItemsExport( $factor_items ) )->download( 'factorItems.xlsx' );
        }
        return new FactorItemCollection( $factor_items->paginate( 20 ) );
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setCenter(Request $request)
    {
        $factor_item = FactorItem::find( $request->id );
        $item = $request->item;
        $item['id'] = $request->id;
        self::formData( $item );
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:factor_items,id',
            'center_id' => 'required',
        ]);
        if ($validate->fails()){
            alert()->error(implode(" | ", $validate->errors()->all())
            , 'عملیات ناموفق ')->persistent('باشه');
            return redirect()->back();
        }
        // check factorItem have serviceTariff or not
        if ($factor_item->serviceTariffs()->exists()){
            alert()->error(' جهت تغییر مرکز پرونده برای پرونده نباید طرح درمانی ثبت شده باشد .', 'عملیات ناموفق ')->persistent('باشه');
            return redirect()->back();
        }
        // unselected center
        if($request->center_id == null)
        {
            $factor_item->update( [
                'center_id' =>  $request->center_id ,
                'status_factor' => 14,
            ] );
            alert()->success('مرکز تعیین شده با موفقیت حذف گردید.', 'عملیات موفق ')->autoclose(4000)->persistent('باشه');
            return redirect()->back();
        }
        //close older sessions
        Session::whereIn('status',[3,5])->where('factor_item_id',$factor_item->id)->update([
            'status'=>7,
            'description'=>'این ویزیت به دلیل اینکه در حالت مشاوره شد نبود و ویزیت جدیدی برای کاربر ایجاد شد توسط سیستم لغو گردید .'
        ]);

        //update center_id
        $factor_item->update( [
            'center_id' =>  $request->center_id,
            'status_factor' => 17,
        ] );
        //create session
        if ($factor_item->factoritemable->session==1){
            Session::create( [
                'status' => 3 ,
                'user_id' => auth()->user()->id,
                'factor_item_id' => $factor_item->id,
                'date' => $request->date ? Helper::vertaToMiladi($request->date) : date('Y-m-d H:i:s'),
                'description_before' => isset($item['typeConsultation']) && $item['typeConsultation'] == 'inPerson' ? $request->description.'- مشاوره حضوری' : $request->description.'مشاوره آنلاین',
           ]);
        }
        //send sms to the user
        if ( $request->check && isset($item['typeConsultation']) && $item['typeConsultation'] == 'inPerson' ) {
            event( new smsService( $factor_item->factor->user, $factor_item->center->title . ':' . $factor_item->center->address, $factor_item->date ) );
        }
        if($factor_item->center->representation_id)
        {
            $factor_item->factor->update( [ 'representation_id' => $factor_item->center->representation_id ] );
        }
        //send sms to the center manager
        event(new referralCenterNotification($factor_item->center));
        alert()->success('پرونده جهت انجام مشاوره به مرکز ارجاع داده شد.', 'عملیات موفق ')->autoclose(4000)->persistent('باشه');
        return redirect()->back();
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function turnRatings(Request $request ) {
        $factor_item = FactorItem::find( $request->id );
        if (is_null($factor_item->center_id)){
            alert()->error('لطفا ابتدا تعیین مرکز نمایید .', 'عملیات ناموفق ')->persistent('باشه');
            return redirect()->back();
        }
        //send sms factoritem start work
        if ( !$factor_item->complete && $request->check ) {
            self::smsStartWork( $factor_item );
        }
        $factor_item->update( [
             'date' => isset( $request->date ) ? $request->date : $factor_item->date,
            'complete' => true,
            'supporter_id' => auth()->user()->id,
        ] );

        Session::whereIn('status',[3,4,5,6])->where('factor_item_id',$factor_item->id)->update([
            'status'=>7,
            'description'=>'این ویزیت به دلیل اینکه در حالت مشاوره شد نبود و ویزیت جدیدی برای کاربر ایجاد شد توسط سیستم لغو گردید .'
         ]);
         if ($factor_item->factoritemable->session==1){
             Session::create( [
                 'status' => 3,
                 'user_id' => auth()->user()->id,
                 'factor_item_id' => $factor_item->id,
                 'date' => Helper::vertaToMiladi($request->date),
                 'description_before' => $request->description,
             ]);
         }

        if ( $request->check ) {
            $factor_item->factor->update( [ 'representation_id' => $factor_item->center->representation_id ] );
            event( new smsService( $factor_item->factor->user, $factor_item->center->title . ':' . $factor_item->center->address, $factor_item->date ) );
        }

        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateInformation( Request $request ) {

        $data = $request->item;

        $validator = \Validator::make( $data, [
            'phone' => 'unique:users,phone,'.$data[ 'user_id' ]
        ],
            [
                'phone.unique'=>'کاربری قبلا با این شماره موبایل  ثبت نام شده است.',
            ] );

        if ( $validator->fails() ) {
            return redirect()->back()->with( [
                'errors' => $validator->errors()
            ] );
        }

        $item = $request->item;
        $factor_item = FactorItem::find( $item[ 'id' ] );

        if ( isset( $item[ 'phone' ] ) ) {
            $item[ 'phone' ] = Helper::numberConverter( $item[ 'phone' ] );
        }

        if (!isset($factor_item->factor_values['supervisor'])) {
            self::storeImageConfirm($factor_item , 'supervisor');
        }
        self::formData( $item );

        //send sms factoritem start work
        if ( !$factor_item->complete ) {
            self::smsStartWork( $factor_item );
        }

        Session::checkAndStartEntryVisitSession($factor_item);

        $factor_item->update( [
            'status_factor' => $factor_item->status_factor <= 3 ? 14 : $factor_item->status_factor,
            'complete' => true,
            'status' => 1,
            'file_number' => $request->file_number ? $request->file_number : $factor_item->file_number,
        ] );
        //set supervisor
        if ( $request->input( 'mobile_supervisor' ) ) {
            $result = FactorItem::setSupervisor($request->input( 'mobile_supervisor' ) , $factor_item);
            if(!isset($result['factor_item']))
            {
                return redirect()->back()->with( [
                    'failByMessage' => $result['message']
                ] );
            }
            $factor_item = $result['factor_item'];
        }

        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * suspended registration for the factorItem
     */
    public function suspension( Request $request ) {
        $this->validate( $request, [
            'date' => 'required',
            'description' => 'required',
        ] );

        $factor_item = FactorItem::find( $request->id );

        $factor_item->update( [
            'status_factor' => 3,
            'expired_at' => Helper::vertaToMiladi($request->date),
        ] );

        $factor_item->factor->comments()->create([
            'user_id' => auth()->user()->id,
            'comment' => nl2br($request->description),
        ]);

        if ( $request->reason == 1 ) {
            event(new smsNotAnswer($factor_item->factor->user , auth()->user() , 'پشتیبانی'));
        }

        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * create factorItem products & factorItem service tariffs
     */
    public function createFactor( Request $request ) {
        $item = $request->item;
        $factor_item = FactorItem::find( $item[ 'id' ] );
        self::formData( $request->item );
        $user = User::find( $item[ 'user_id' ] );
        $type = isset($request->type) ? $request->type : $factor_item->type;
        //self::changeType( $factor_item, $type );
        if($factor_item->financialPlansType && !FinancialPlansType::checkFinancialPlansTypeByPaymentType($factor_item->financialPlansType,$type))
        {
            return redirect()->back()->with( [
                'failByMessage' => 'طرح پرونده انتخاب شده با نوع پرداخت پرونده مطابقت ندارد لطفا طرح پرونده مربوط به همان نوع پرداخت را انتخاب کنید.'
            ] );
        }
        FactorItem::changeType($factor_item,$type);
        if ( $factor_item->factoritemable_type == Service::class ) {
            self::createSerivceTriffsFactorItem($request , $factor_item);
        }
        else {
            self::createProductsFactorItem($request , $factor_item);
        }
        //get total price factor
        $total_price = $request->totalPrice ? ( float ) str_replace( ',', '', $request->totalPrice ) : 0;
        $other_price = isset( $item[ 'otherPrice' ] ) ? $item[ 'otherPrice' ] : 0;
        $total_price = FactorItem::getTotalPrice($factor_item , $total_price , $other_price);

        //get supervisor factoritem
        $factor_item = FactorItem::find( $item[ 'id' ] );
        $supervisor_factor_item = $factor_item->supervisor;
        //get difference price
        if ( $supervisor_factor_item->totalFactorItem && $supervisor_factor_item->totalFactorItem->confirm ) {
            FactorItem::calculateDiffrencePrice($supervisor_factor_item , $total_price);
        } else {
            FactorItem::calculate( $supervisor_factor_item, $total_price, $type, $user );
        }
        //save factor image
        if(isset($request->item['urlfactor-image']))
		{
		    self::saveFactorImage( $request->item['urlfactor-image'], $factor_item );
		}
        $status_factor = self::getStatusFactor($factor_item);
        //update user factoritem
        $factor_item->update( [
            'status_factor' => $status_factor,
            'date' => $request->date ? $request->date : $factor_item->date,
        ] );
        if ($supervisor_factor_item->type)
        {
            TotalFactorItem::updateCalculate( $supervisor_factor_item->totalFactorItem );
        }
        if (!isset($factor_item->factor_values['assistant'])) {
            self::storeImageConfirm($factor_item , 'assistant');
        }
        Session::checkAndEndEntrySession($factor_item);
        Session::changeVisitStatusByFactorItem($factor_item,6);

        Log::channel('db')->info('service tariffs created.' , ['factor_item_id' => $factor_item->id , 'registerar_id' => auth()->user()->id ,'service_tariffs' => $factor_item->serviceTariffs]);

        $url = URL::previous();
        if(Str::contains($url, 'totalFactorItem') && !$supervisor_factor_item->totalFactorItem)
        {
            return redirect(url('dashboard/factorItem/'.$factor_item->resnumber))->with( [
                'update' => 'success'
            ] );
        }
        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * upload the executive documents of the factorItem
     */
    public function uploadDocument( Request $request ) {
        //upload file documents
        $item = $request->item;
        $factor_item = FactorItem::find( $item[ 'id' ] );

        self::formData( $request->item );

        $factor_item->update( [
            'status' => $request->status,
            'status_factor' => !$factor_item->type && $factor_item->status_factor == 2 ? 10 : $factor_item->status_factor,
        ] );

        if (!isset($factor_item->factor_values['center-manager'])) {
            self::storeImageConfirm($factor_item , 'center-manager');
        }

        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * save complation the executive work of the factorItem
     */
    public function completionFileWork( Request $request ) {
        $item = $request->item;
        $factor_item = FactorItem::find( $item[ 'id' ] );
        $is_credit = FactorItem::checkCredit($factor_item->supervisor,0);
          if (FactorItem::haveInCompleteServiceTariff($factor_item)){
              return redirect()->back()->with( [
                  'failByMessage' => 'امکان اعمال اتمام کار وجود ندارد [ به دلیل کار تعیین شده ی انجام نشده ].'
              ] );
          }
        if ( !$is_credit ) {
            return redirect()->back()->with( [
                'failByMessage' => 'امکان اعمال اتمام کار وجود ندارد [ به دلیل بدهی پرداخت نشده ].'
            ] );
        }
        self::formData( $request->item );

        $factor_item->update( [ 'status_factor' => $request->status_factor ] );

        $factor_item->update( [
            'status' => $request->status,
        ] );

        self::calculateCenterRevenue( $factor_item );

        $factor_item->update( [
            'status' => $request->status,
        ] );

        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeStatusFactor( Request $request ) {
        $item = $request->item;
        $factor_item = FactorItem::find( $item[ 'id' ] );
        $factor_item->update( [
            'status_factor' => $request->status_factor,
        ] );
        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * update financial calculattions factorItem
     */
    public function updateCalculate( Request $request ) {
        $factor_item = FactorItem::find( $request->input( 'id' ) );
        $supervisor_factor_item = $factor_item->supervisor;
        $type = isset( $request->type ) ? $request->type : $supervisor_factor_item->type;
        if($factor_item->financialPlansType && !FinancialPlansType::checkFinancialPlansTypeByPaymentType($factor_item->financialPlansType,$type))
        {
            return redirect()->back()->with( [
                'failByMessage' => 'طرح پرونده انتخاب شده با نوع پرداخت پرونده مطابقت ندارد لطفا طرح پرونده مربوط به همان نوع پرداخت را انتخاب کنید.'
            ] );
        }
        $supervisor_factor_item = FactorItem::changeType( $factor_item, $type );
        $discount = $request->input(  'discount' ) ? ( float ) str_replace( ',', '', $request->input( 'discount' ) ) : $supervisor_factor_item->discount;
        $total_price = $supervisor_factor_item->total_price + $discount;
        $prepayment_price = ( $supervisor_factor_item->type ? ( float ) str_replace( ',', '', $request->input( 'prepayment' ) ) : $total_price );
        $installments_num = $request->input( 'installments_num' ) == 0 ? CalculationHelper::calculateInstallmentsNum($supervisor_factor_item) : $request->input( 'installments_num' );
        $wage = CalculationHelper::calculateWage($supervisor_factor_item , $total_price , $prepayment_price , $installments_num);
        $tax = CalculationHelper::calculateTax($wage);
        $loan = CalculationHelper::calculateLoan($total_price , $prepayment_price , $wage , $tax);
        $installment_price = CalculationHelper::calculateInstallmentsPrice($supervisor_factor_item->type , $loan , $installments_num);

        $prepayment_paid = FactorItem::getPrepaymentPaid( $supervisor_factor_item );
        $prepayment = [];
        $prepayment = Arr::add( $prepayment, 'prepayment_price', $prepayment_price );
        $prepayment = Arr::add( $prepayment, 'prepayment_debt', $prepayment_price - $prepayment_paid );
        if($supervisor_factor_item->type && $total_price <= $prepayment_price)
        {
            return redirect()->back()->with( [
                'failByMessage' => 'همکار گرامی در پرونده های اقساطی جمع کل پرونده نمی تواند از مبلغ پیش پرداخت کمتر و یا با آن برابر باشد بنابراین وضعیت پرونده را به نقدی تغییر دهید. '
            ] );
        }
        if ( $supervisor_factor_item->type && !$supervisor_factor_item->totalFactorItem->confirm ) {
            $supervisor_factor_item->update( [
                'discount' => ( float ) str_replace( ',', '', $request->input( 'discount' ) ),
                'installments_num' => $installments_num,
                'installments_price' => $installment_price,
            ] );
            $supervisor_factor_item->totalFactorItem->update( [
                'guarantee_type' => $request->guarantee_type ? $request->guarantee_type : $supervisor_factor_item->totalFactorItem->guarantee_type,
            ] );
            if ($supervisor_factor_item->property()->exists()){
                $supervisor_factor_item->property->update($prepayment);
            }else{
                $supervisor_factor_item->property()->create($prepayment);
            }
            TotalFactorItem::updateCalculate( $supervisor_factor_item->totalFactorItem );
        } else {
            $supervisor_factor_item->update( [
                'discount' => ( float ) str_replace( ',', '', $request->input( 'discount' ) ),
            ] );
        }

        $url = URL::previous();
        if(Str::contains($url, 'totalFactorItem') && !$supervisor_factor_item->totalFactorItem)
        {
            return redirect(url('dashboard/factorItem/'.$factor_item->resnumber))->with( [
                'update' => 'success'
            ] );
        }
        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * send link and pay the invoice cost or difference_price factor // factor title or difference_price title
     */
    public function payment( Request $request ) {
        $factor_item = FactorItem::find( $request->input( 'id' ) );
        $supervisor_factor_item = $factor_item->supervisor;
        $user = User::find( $request->input( 'user_id' ) );
        $price = ( float ) str_replace ( ',', '', $request->input( 'price' ) );
        if ( $request->input( 'payment_type' ) == 'credit' ) {
            $result = User::checkCredit( $user, $price, $request->input( 'password' ) , $request->input('title'));
            if ( !$result[ 'success' ] ) {
                return redirect()->back()->with( [
                    'failByMessage' => $result[ 'message' ]
                ] );
            }
        }
        if ( $request->input( 'payment_type' ) == 'wallet' ) {
            $result = User::checkWallet( $user, $price, $request->input( 'password' ) );
            if ( !$result[ 'success' ] ) {
                return redirect()->back()->with( [
                    'failByMessage' => $result[ 'message' ]
                ] );
            }
        }
        $factor = new Factor();
        $factor->title = $request->input('title');
        $factor->user_id = $supervisor_factor_item->factor->user->id;
        $factor->price = ( float ) str_replace( ',', '', Helper::numberConverter( $request->input( 'price' ) ) );
        $factor->type = $request->input( 'payment_type' );
        $factor->factor_item_id = $supervisor_factor_item->id;
        $factor->total_factor_item_id = $supervisor_factor_item->type ? $supervisor_factor_item->total_factor_item_id : null;
        $factor->callcenter_id = auth()->user()->id;
        $factor->number_card = $request->input( 'number_card' );
        $factor->payment_code = $request->input( 'payment_code' );
        $factor->representation_id = auth()->user()->admin_representation_id;
        $factor->center_id = $supervisor_factor_item->center_id;
        $factor->status = $request->input( 'payment_type' ) == 'online' ? 0 : 1;
        $factor->expired_at = now()->addDay( 3 );
        $factor->created_at = Helper::vertaToMiladi($request->payment_date);
        $factor->save();

        //upload bill_image cash factor
        if ( $request->url_bill_image ) {
            $factor->update(['bill_image' => Helper::moveImage($request->url_bill_image,'document')]);
        }

        //send sms for pay factor
        if ( $request->input( 'payment_type' ) == 'online' ) {
            event( new sendlinkFactor( $user, $factor, auth()->user()->family, 'online' ) );
        } else {
            Factor::savePayment( $factor );
        }

        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * update factorItem productItems
     */
    public function updateProductItem( Request $request ) {
        $inputs = $request->all();
        if(isset($inputs[ 'id' ]))
        {
        for ( $i = 0; $i < count( $inputs[ 'id' ] );
              $i++ ) {
            $factor_item_product = FactorItemProduct::where( 'id', $request->id[ $i ] )->first();
            $product_price = Product::find( $request->product_id[ $i ] );
            $discount = ( float ) str_replace( ',', '', $request->discount[ $i ] );
            $price = $factor_item_product->product_id != $request->product_id[ $i ] ? $product_price->price : $factor_item_product->price;
            FactorItemProduct::where( 'id', $request->id[ $i ] )->update( [
                'product_id' => $request->product_id[ $i ],
                'user_id' => $request->user_id[ $i ],
                'price' => $price,
                'discount' => $discount,
            ] );
        }
        return redirect()->back()->with( [
            'update' => 'success'
        ] );
        }
        return redirect()->back()->with( [
            'failByMessage' => 'فاکتوری برای ویرایش وجود ندارد.'
        ] );
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * update factorItem serviceTariffs
     */
    public function updateServiceTariff( Request $request ) {
        $inputs = $request->all();
        $message = [];
        if(isset($inputs[ 'id' ]))
        {
        for ( $i = 0; $i < count( $inputs[ 'id' ] );
              $i++ ) {
            $factor_item_service_tariff = FactorItemServiceTariff::find( $request->id[ $i ] );
            $service_tariff = ServiceTariff::find( $request->service_tariff_id[ $i ] );
            if($factor_item_service_tariff->sessions->isEmpty()){
                $discount = ( float ) str_replace( ',', '', $request->discount[ $i ] );
                $value = !ctype_digit($factor_item_service_tariff->value) ? 1 : (float)$factor_item_service_tariff->value;
                $requset_service_tariff_price = $factor_item_service_tariff->factorItem->factoritemable->is_change_factor_price ? ( float ) str_replace( ',', '', $request->price[ $i ]) : $factor_item_service_tariff->price;
                $price = $factor_item_service_tariff->service_tariff_id != $request->service_tariff_id[ $i ] ? $service_tariff->price*$value : $requset_service_tariff_price;
                $factor_item_service_tariff->update( [
                    'service_tariff_id' => $request->service_tariff_id[ $i ],
                    'user_id' => $request->user_id[ $i ],
                    'treatment_duration' => $request->treatment_duration[ $i ],
                    'session_number' => $request->session_number[ $i ],
                    'price' => $price,
                    'discount' => $discount,
                ] );
            } else {
                $factor_item_service_tariff->update( [
                    'user_id' => $request->user_id[ $i ],
                ] );
                $message[$request->id[ $i ]] = "برای این فاکتور قبلا جلسه ثبت شده است.";
            }
        }
        //get supervisor factoritem
        $factor_item = $factor_item_service_tariff->factorItem;
        $user = $factor_item->factor->user;
        $type = $factor_item->type;
        //get total price factor
        $total_price = 0;
        $other_price = isset( $factor_item->factor_value[ 'otherPrice' ] ) ? $factor_item->factor_value[ 'otherPrice' ] : 0;
        $total_price = FactorItem::getTotalPrice($factor_item , $total_price , $other_price);
        //get supervisor factorItem
        $supervisor_factor_item = $factor_item->supervisor;
        //get difference price
        if ( $supervisor_factor_item->totalFactorItem && $supervisor_factor_item->totalFactorItem->confirm ) {
            FactorItem::calculateDiffrencePrice( $supervisor_factor_item , $total_price );
        }
        else{
            //calculate
            FactorItem::calculate( $supervisor_factor_item, $total_price, $type, $user );
        }
        if($supervisor_factor_item->totalFactorItem)
        {
            TotalFactorItem::updateCalculate($supervisor_factor_item->totalFactorItem);
        }
        Log::channel('db')->info('service tariffs updated.' , ['factor_item_id' => $factor_item->id , 'registerar_id' => auth()->user()->id , 'service_tariffs' => $factor_item->serviceTariffs]);
        return redirect()->back()->with( [
            'update' => 'success',
            'message' => $message,
        ] );
        }
        return redirect()->back()->with( [
            'failByMessage' => 'فاکتوری برای ویرایش وجود ندارد.'
        ] );
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function comment( Request $request ) {
        $this->validate( $request, [
            'comment' => 'required|string',
            'factor_id' => 'required|numeric'
        ] );

        $factor = Factor::findOrFail( $request->input( 'factor_id' ) );

        $factor->comments()->create( [
            'user_id' => auth()->user()->id,
            'comment' => nl2br( $request->input( 'comment' ) ),
        ] );

        return redirect()->back()->with( [
            'save' => true
        ] );
    }
    /**
     * @param mixed $item
     * @return void
     * save the executive formItems of the factorItem
     */
    private function formData( $item ) {
        $factor_item = FactorItem::find( $item[ 'id' ] );
        if ( isset( $factor_item->factoritemable->beforeforms ) ) {
            $values = $factor_item->factor_values ? $factor_item->factor_values : [];
            foreach ( $factor_item->factoritemable->beforeforms as $key => $form ) {
                if ( $form->data_save_level == 'user' ) {
                    if ( isset( $item[ $form->name ] ) ) {
                        User::where( 'id', $item[ 'user_id' ] )->update( [ $form->name => $item[ $form->name ] ] );
                    }
                    if ( $form->type == 'file' ) {
                        if ( isset( $item[ $form->name ] ) ) {
                            $image = $item[ $form->name ];
                            if ( $image ) {
                                $url = Helper::moveImage($item['url' . $form->name],'document');
                                if ( isset( $values[ $form->name ] ) ) {
                                      $values[ $form->name ] != $url ? Helper::deleteImage( $values[ $form->name ] ) : '';
                                }
                                $values[ $form->name ] = $url;
                                User::where( 'id', $item[ 'user_id' ] )->update( [ $form->name => $url ] );
                            }
                        }
                    }
                }
                if ( $form->data_save_level == 'factor_item_factor_values') {
                    if ( $form->type == 'file' ) {
                        if ( isset( $item[ $form->name ] ) ) {
                            $image = $item[ $form->name ];
                            if ( $image ) {
                                $url = Helper::moveImage($item['url' . $form->name],'document');
                                if ( isset( $values[ $form->name ] ) ) {
                                    $values[ $form->name ] != $url ? Helper::deleteImage( $values[ $form->name ] ) : '';
                                    $values[ $form->name ] = $url;
                                }
                                $values = Arr::add( $values, $form->name, $url );
                            }
                        }
                    }
                    elseif ( $form->type == 'multi_file' ) {
                        if ( isset( $item[ $form->name ] ) ) {
                            $images = isset( $values[ $form->name ] ) ? $values[ $form->name ] : [];
                            $files = $item[ 'url' . $form->name ];
                            foreach ( $files as $file ) {
                                if ( $file ) {
                                    if ( isset( $images ) ) {
                                        $url = Helper::moveImage($file,'document');
                                        $images[] = $url;
                                    }
                                }
                            }
                            if (isset($values[$form->name])) {
                                $values[$form->name] = $images;
                            }
                            $values = Arr::add( $values, $form->name, $images );
                        }
                    }
                    else{
                        if ( isset( $item[ $form->name ] ) ) {
                            if ( isset( $values[ $form->name ] ) ) {
                                $values[ $form->name ] = $item[ $form->name ];
                            }
                            $values = Arr::add( $values, $form->name, $item[ $form->name ] );
                        }
                    }
                }
                $factor_item->update( [
                    'factor_values' => !is_null( $values ) ? $values : null,
                ] );
            }
        }
    }
    /**
     * @param FactorItem $factor_item
     * @return void
     * calculate center revenue after complation file work
     */
    private function calculateCenterRevenue( FactorItem $factor_item ) {
        //calculate center revenue in supervisor factorItem
        $price_difference_paid = 0;
        $supervisor_factor_item = $factor_item->supervisor;
        $total_factor_item = $supervisor_factor_item->totalFactorItem;
        $admin_users = User::where( 'level', 'admin' )->where( 'center_id', $factor_item->center_id )->get();
        foreach ( $admin_users as $u ) {
            if ( $u->hasRole( 'store-manager' ) ) {
                $user = $u;
            }
        }
        if ( !count( $factor_item->userPerformances ) && isset( $user ) && $factor_item->total_price ) {

            if ( isset( $factor_item->property->price_difference_total ) ) {
                $price_difference_paid = $factor_item->property->price_difference_total > 0 ? $factor_item->property->price_difference_total - $factor_item->property->price_difference_debt : $factor_item->property->price_difference_debt;
            }
            $total_price = $factor_item->total_price + $price_difference_paid;
            //create session for calculate job done price
            Session::create([
                'factor_item_id' => $factor_item->id,
                'price' => $total_price,
                'status' => 1,
                'user_id' => auth()->user()->id,
                'confirmer_id' => auth()->user()->id,
                'agent_id' => $user->id,
            ]);

            //deduct total price from credit totalFactorItem
            if ( $factor_item->type ) {
                $total_factor_item->update( [
                    'credit' => $total_factor_item->credit - $factor_item->total_price - $price_difference_paid
                ] );
                $total_factor_item->factorItems()->where( 'factoritemable_id', $factor_item->factoritemable_id )->where( 'factoritemable_type', $factor_item->factoritemable_type )->update( [
                    'credit' => $supervisor_factor_item->credit - $factor_item->total_price - $price_difference_paid,
                ] );

                $total_factor_item->factorItems()->where( 'factoritemable_id', $factor_item->factoritemable_id )->where( 'factoritemable_type', $factor_item->factoritemable_type )->where( 'status_factor', '!=', 7 )->update( [
                    'total_price' => $supervisor_factor_item->credit,
                ] );
            }

            for ( $i = 2; $i <= 11; $i++ ) {
                //create userPerformance
                $performance = new UserPerformance();
                $performance->price = $total_price / 10;
                $performance->user_id = $user->id;
                $performance->callcenter_id = auth()->user()->id;
                $performance->description = 'پرداخت مرحله ' . ( $i - 1 ) . ' به ' . $factor_item->center->title;
                $performance->date = Carbon::parse( $factor_item->approval_date )->addMonths( $i );
                $performance->factor_item_id = $factor_item->id;
                $performance->save();

                //create walletTransaction
                $resNum = rand( 12, 99 ) . substr( Carbon::now()->timestamp, 6, 11 ) . substr( auth()->user()->phone, 7, 11 );
                WalletTransaction::create( [
                    'user_id' => $performance->user_id,
                    'reason_id' => $performance->id,
                    'resnumber' => $resNum,
                    'price' => $performance->price * ( $user->employee->percent / 100 ),
                    'status' => '1',
                    'type' => '1',
                    'created_at' => $performance->date,
                ] );
            }
            //set emplloyee performance from percentageAllocation employee
            PercentageAllocationEmployee::setUserPerformance($factor_item , 'all_total_price' , $total_price , now() , null);
        }
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * return payment by factor title in cash factorItem
     */
    public function returnPayment( Request $request ) {
        $factor_item = FactorItem::find( $request->input( 'id' ) );
        $prepayment_paid = FactorItem::getPrepaymentPaid($factor_item);
        $factor_item_discount_price = $factor_item->discount;
        $prepayment_price_used = FactorItem::getSessionsSumPrice($factor_item , 'all') + $factor_item_discount_price;
        $price =  (float) str_replace(',', '', Helper::numberConverter($request->input('price')));
        $prepayment['prepayment_debt']=$factor_item->property->prepayment_debt;

        //return payment from prepayment price paid
        if ($request->title == 'factor') {
            if($prepayment_paid - $prepayment_price_used >= $price)
            {
                $prepayment['prepayment_debt'] = $prepayment['prepayment_debt'] + $price;
                $factor_item->property->update([
                    'prepayment_debt' => $prepayment['prepayment_debt'],
                ]);
            }
            else
            {
                return redirect()->back()->with([
                    'failByMessage' => ' پرداختی مصرف نشده این پرونده کمتر از '.number_format($price).'می باشد.'
                ]);
            }
        }

        $factor = new Factor();
        $factor->title = $request->title;
        $factor->user_id = $request->input( 'user_id' );
        $factor->price = $price;
        $factor->type = $request->input( 'payment_type' );
        $factor->factor_item_id = $request->input( 'id' );
        $factor->callcenter_id = auth()->user()->id;
        $factor->number_card = $request->input( 'number_card' );
        $factor->payment_code = $request->input( 'payment_code' );
        $factor->representation_id = auth()->user()->admin_representation_id;
        $factor->center_id = $factor_item->center_id;
        $factor->status = 2;
        $factor->created_at = Helper::vertaToMiladi($request->payment_date);
        $factor->save();

        //increase credit user
        if ( $factor->type == 'increase_credit' ) {
            $factor->user->update( [ 'credit' => $factor->user->credit + $factor->price ] );
        }

        //upload bill_image cash factor
        if ( $request->url_bill_image ) {
            $factor->update( [ 'bill_image' => $request->url_bill_image ] );
        }

        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param FactorItem $factor_item
     * @return array
     * set factorItem items for use in show && edit
     */
    private function setItem( FactorItem $factor_item ) {
        $supervisor_factor_item = $factor_item->supervisor;
        $factor_item[ 'id' ] = $factor_item->id;
        $factor_item[ 'factor' ] = $factor_item->factor;
        $factor_item[ 'factoritemable' ] = $factor_item->factoritemable;
        $factor_item[ 'service_item' ] = !is_null( $factor_item->service_item_id ) ? $factor_item->service_item : null;
        $factor_item[ 'centers' ] = FactorItem::getSelectableCenters($factor_item);
        $factor_item[ 'forms' ] = isset( $factor_item->factoritemable->beforeforms ) ? $factor_item->factoritemable->beforeforms : null;
        $factor_item[ 'site_forms' ] = isset( $factor_item->factoritemable->beforeforms ) ? $factor_item->factoritemable->beforeforms->where( 'level', 'site' ) : null;
        $factor_item[ 'complete' ] = $factor_item->complete;
        $factor_item[ 'discount' ] = $supervisor_factor_item->discount ;
        $factor_item[ 'total_price' ] = $supervisor_factor_item->total_price + $supervisor_factor_item[ 'discount' ];
        $factor_item[ 'installments_num' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->installments_num : '';
        $factor_item[ 'installments_price' ] = number_format($supervisor_factor_item->installments_price);
        $factor_item[ 'values' ] = $factor_item->values;
        $factor_item[ 'factor_values' ] = $factor_item->factor_values;
        $factor_item[ 'supervisor_id' ] = $factor_item->supervisor_id;
        $factor_item[ 'factor_image' ] = $factor_item->factor_image;
        $factor_item[ 'status_factor' ] = $factor_item->status_factor;
        $factor_item[ 'status_factor_text' ] = FactorItem::statusFactor( $factor_item->status_factor );
        $factor_item[ 'confirms' ] = $factor_item->confirms;
        $factor_item[ 'center' ] = $factor_item->center;
        $factor_item[ 'date' ] = $factor_item->date ? Helper::vertaToMiladi($factor_item->date) : now();
        $factor_item[ 'type' ] = $factor_item->type;
        $factor_item[ 'file_number' ] = $factor_item->file_number ? $factor_item->file_number : 'ندارد';
        $factor_item[ 'approval_date' ] = verta( $supervisor_factor_item->approval_date )->format( 'Y/m/d' );
        $factor_item[ 'status_mali' ] = FactorItem::statusMali( $supervisor_factor_item->status_mali );
        $factor_item[ 'status' ] = $factor_item->status;
        $factor_item[ 'products' ] = $factor_item->products;
        $factor_item[ 'service_tariffs' ] = $factor_item->serviceTariffs;
        $factor_item[ 'date_first_pay' ] = isset( $factor_item->factors->where( 'title', '!=', 'register' )->where( 'status', true )->first()->created_at ) ? verta( $factor_item->factors->where( 'title', '!=', 'register' )->first()->created_at )->format( 'Y/m/d' ) : '';
        $factor_item[ 'factorItems' ] = FactorItem::getFatorItemCollection( $factor_item )->get();
        $factor_item[ 'sessions' ] = $factor_item->sessions;
        $factor_item[ 'histories' ] = $factor_item->histories;
        $factor_item[ 'address' ] = $factor_item->factor->address;
        $factor_item[ 'user_histories'] = Helper::userHistories( $factor_item->factor->user );
        $factor_item[ 'financial_plans_type' ] = FactorItem::getFnancialPlansType($factor_item);
        $factor_item[ 'financial_plans_types_list' ] = FinancialPlansType::getFinancialPlansTypeByItem($factor_item->factoritemable);
        //fill prepayment array
        $factor_item['prepayment'] = self::getPrepayment($supervisor_factor_item);
        //fill price_difference array
        $factor_item['price_difference'] = self::getPriceDifference($supervisor_factor_item);
        //information factorItem mali
        $item = [];
        $item[ 'id' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->id : $factor_item->id;
        $item[ 'total_factor_item' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem : [];
        $item[ 'total_debt' ] = $supervisor_factor_item->type ? TotalFactorItem::calculateTotalDebt($supervisor_factor_item->totalFactorItem) : FactorItem::calculateTotalDebt($supervisor_factor_item);
        $item[ 'confirm' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->confirm : false;
        $item[ 'credit' ] = $supervisor_factor_item->type ? TotalFactorItem::calculateCredit($supervisor_factor_item->totalFactorItem) : FactorItem::calculateCredit($supervisor_factor_item);
        $item[ 'discount' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->factorItems->sum('discount') : $supervisor_factor_item->discount ;
        $item[ 'total_price' ] = ($supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->total_price : $supervisor_factor_item->total_price) + $item[ 'discount' ];
        $item[ 'installments_num' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->installments_num : '';
        $item[ 'guarantee_type' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->guarantee_type : '';
        $item[ 'guarantee_types_list' ] = $supervisor_factor_item->type ? TotalFactorItem::getGuaranteeTypeByFinancialPlansType($supervisor_factor_item->totalFactorItem) : [];
        $item[ 'percent_fines' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->percent_fines : '';
        $item[ 'payment' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->factor : $supervisor_factor_item->factor;
        $item[ 'payments' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->factors : $supervisor_factor_item->factors;
        $item[ 'has_payment' ] = $supervisor_factor_item->purchase_type ? $supervisor_factor_item->factors()->where( 'status', 1 )->exists() : $supervisor_factor_item->factor()->where( 'status', 1 )->exists();

        //fill prepayment array
        $item['prepayment'] = self::getPrepayment($supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem:$supervisor_factor_item);
        //fill price_difference array
        $item['price_difference'] = self::getPriceDifference($supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem:$supervisor_factor_item);

        return ['factorItem' => $factor_item , 'item' => $item];
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * change factorItem factoritemable
     */
    public function changeFactoritemable( Request $request ) {
        $inputs = $request->all();
        $factor_item = FactorItem::find( $request->factor_item );

        $factoritemable_type = ( $inputs[ 'cat_type'.$request->factor_item ] == 'category' ? Category::class : Service::class );
        $factoritemable_id = ( $inputs[ 'cat_type'.$request->factor_item ] == 'category' ? $inputs[ 'category'.$request->factor_item ] : $inputs[ 'service'.$request->factor_item ] );

        $factor_item->update( [
            'factoritemable_id' =>  $factoritemable_id,
            'factoritemable_type' => $factoritemable_type,
            'service_item_id' => null,
        ] );

        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * closing the factorItem in factorItem of non-payment
     */
    public function close( Request $request ) {
        $this->validate( $request, [
            'comment' => 'required|string',
        ] );
        $factor_item = FactorItem::find( $request->id );

        $check_payment=FactorItem::checkPayment($factor_item);
        if ($check_payment){
            return redirect()->back()->with( [
                'failByMessage' => ' به دلیل وجود پرداختی برای این پرونده ، امکان مختومه نمودن وجود ندارد .'
            ] );
        }
        $factor_item->update( [ 'status_factor' => 15 ] );

        $factor_item_able = $factor_item->factoritemable->title;
        $userMobile = $factor_item->factor->user->phone;
        $user = $factor_item->factor->user->name.' '.$factor_item->factor->user->family;
        if (isset($userMobile)) {
            $body = [
                'param1' => $user,
                'param2' => $factor_item_able,
            ];
            IranSms::sendSingleSms('factoritemClosing',$userMobile,$body);
        }
        //event(new factorItemClosing($factor_item->factor->user , $factor_item));

        if ( $request->comment ) {
            $factor_item->factor->comments()->create( [
                'user_id' => auth()->user()->id,
                'comment' => $request->comment,
            ] );
        }

        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function transfer(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validation->fails()){
            return response(['status'=>'error','messages'=>$validation->messages()->all()]);
        }
        $factor_items = FactorItem::whereIn('id' , $request->id)->get();
        $factor_items->map(function ($factor_item) use ($request){
            //transfer all factorItems of the selected factorItem subset
            foreach($factor_item->getFatorItemCollection($factor_item)->get() as $item)
            {
                if($request->transfer_type == 'representation' && $request->selected_representation)
                {
                    FactorItem::createRepresentation($item , $request->selected_representation);
                }
                elseif($request->transfer_type == 'supporter' && $request->selected_supporter)
                {
                    $item->update(['supporter_id' => $request->selected_supporter]);
                }
                elseif($request->transfer_type == 'center' && $request->selected_center)
                {
                    $item->update(['center_id' => $request->selected_center]);
                }
            }
            //transfer all totalFactorItem factorItem's if factorItem selected is installment
            if($factor_item->total_factor_item_id)
            {
                foreach(FactorItem::where('total_factor_item_id' , $factor_item->total_factor_item_id)->get() as $item){
                    if($request->transfer_type == 'representation' && $request->selected_representation)
                    {
                        FactorItem::createRepresentation($item , $request->selected_representation);
                    }
                    elseif($request->transfer_type == 'supporter' && $request->selected_supporter)
                    {
                        $item->update(['supporter_id' => $request->selected_supporter]);
                    }
                }
            }
        });

        return response([
            'operator' => true,
            'status'=>'success',
            'messages'=>'انتقال با موفقیت انجام گرفت .'
        ]);
    }
    /**
     * @param \App\Models\FactorItem $factorItem
     * @return \Illuminate\Http\RedirectResponse
     */
    public function referral(FactorItem $factorItem)
    {
        $factorItem->update(['status_factor' => 16]);

        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param FactorItem $factor_item
     * @return void
     * send start work sms of the factorItem
     */
    private function smsStartWork( FactorItem $factor_item ) {
        $pass = rand( 123456, 999999 );
        $user = $factor_item->factor->user;
        event( new factorItemStartWorking( $user, $factor_item, $pass ) );
        $pass = Hash::make( $pass );
        $user->update( [ 'password' => $pass ] );
    }
    /**
     * @param mixed $url_factor_image
     * @param FactorItem $factor_item_user
     * @return void
     * save factorItem factor images
     */
    private function saveFactorImage( $url_factor_image, FactorItem $factor_item_user ) {
        $url_factor_image = $url_factor_image;
        $factor_images = isset( $factor_item_user->factor_image ) ? $factor_item_user->factor_image : [];
        if ( isset( $url_factor_image ) ) {
            foreach ( $url_factor_image as $file ) {
                if ( $file ) {
                    if ( isset( $factor_images ) ) {
                        $factor_images[] = $file;
                    }
                }
            }
        }
        $factor_item_user->update( [ 'factor_image' => $factor_images ] );
    }
    /**
     * @param mixed $item //factorItem or totalFactorItem
     * @return array
     */
    private function getPrepayment($item)
    {
        $prepayment_price = $item->property ? $item->property->prepayment_price : 0;
        $prepayment_debt = $item->property ? $item->property->prepayment_debt : 0;
        $prepayment_count = $item->property ? $item->property->prepayment_count : 0;
        $prepayment_num = $item->property ? $item->property->prepayment_num : 0;
        $prepayment = array('price' => $prepayment_price , 'debt' => $prepayment_debt , 'count' => $prepayment_count , 'num' => $prepayment_num);

        return $prepayment;
    }
    /**
     * @param mixed $item // factorItem or totalFactoItem
     * @retrun int
     */
    private function getPriceDifference($item)
    {
        $price_difference_installment = $item->property ? $item->property->price_difference_installment : 0;
        $price_difference_total = $item->property ? $item->property->price_difference_total : 0;
        $price_difference_debt = $item->property ? $item->property->price_difference_debt : 0;
        $price_difference = array('installment' => $price_difference_installment , 'total' => $price_difference_total , 'debt' => $price_difference_debt);

        return $price_difference;
    }
    /**
     * @param $request
     * @param FactorItem $factor_item
     * @return void
     */
    private function createSerivceTriffsFactorItem($request , FactorItem $factor_item)
    {
        for ($i = 0; $i < ($request->service_tariff_id ? count($request->service_tariff_id) : 0); $i++) {
            $service_tariff = ServiceTariff::find( $request->service_tariff_id[ $i ] );
            $value = isset($request->value[ $i+1 ]) ? $request->value[ $i+1 ] : null;
            $value = isset( $request->dentist_position[ $i+1 ] ) ? $request->dentist_position[ $i+1 ] . ' - ' . $request->dentist_number[ $i+1 ] : $value;
            $price = !is_null( $value ) && is_int($value) ? $service_tariff->price*$value : $service_tariff->price;
            $treatment_duration = isset($request->treatment_duration[ $i+1 ]) ? $request->treatment_duration[ $i+1 ] : null;
		    $doctor_id = isset($request->doctor_id[ $i+1 ]) ? $request->doctor_id[ $i+1 ] : null;
			$session_number = isset($request->session_number[ $i+1 ]) ? $request->session_number[ $i+1 ] : null;

            $factor_item->serviceTariffs()->attach( [ $request->service_tariff_id[ $i ] => [ 'price' => $price, 'value' => $value, 'user_id' => $doctor_id, 'session_number' => $session_number, 'treatment_duration' => $treatment_duration] ] );
        }
    }
    /**
     * @param $request
     * @param FactorItem $factor_item
     * @return void
     */
    private function createProductsFactorItem($request , FactorItem $factor_item)
    {
        for ( $i = 0; $i < ( $request->product_id ? count( $request->product_id ) : 0 ); $i++ ) {
            foreach ( $request->product_id[ $i ] as $temp ) {
                $product_price = Product::find( $temp );
                $factor_item->products()->attach( [ $temp => [ 'price' => $product_price->price ] ] );
            }
        }
    }
    /**
     * @param FactorItem $factor_item
     * @return int
     */
    private function getStatusFactor(FactorItem $factor_item)
    {
        $status_factor = $factor_item->status_factor;
        $supervisor_factor_item = $factor_item->supervisor;
        //change of status in completion files
        if ( $factor_item->status_factor == '7' ) {
            $status_factor = 6;
        }
        //change of status in credit files a waiting approval
        if($supervisor_factor_item->type && $supervisor_factor_item->status_factor == '2' )
        {
            $status_factor = 9;
            $supervisor_factor_item->totalFactorItem->update(['total_factor_item_status_id' => $status_factor]);
        }

        return $status_factor;
    }
    /**
     * @param \App\Models\FactorItem $factorItem
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * send initial link whitout total price
     */
    public function sendInitialLink(FactorItem $factorItem , Request $request)
    {
        //calculate
        $user = $factorItem->factor->user;
        $price = (float)str_replace(',', '', Helper::numberConverter($request->input('price')));
        $prepayment = [];
        $total_price = $factorItem->total_price ? $factorItem->total_price : $price;
        $total_price = $total_price + $factorItem->discount;
        //calculate
        FactorItem::calculate( $factorItem, $total_price, $factorItem->type, $user );
        $prepayment['num'] = $factorItem->property->prepayment_num;
        $prepayment['count'] = $factorItem->property->prepayment_count;

        $factor = new Factor();
        $factor->title = 'factor';
        $factor->user_id = $user->id;
        $factor->price = $price;
        $factor->level = $prepayment['num'] - $prepayment['count'] + 1;
        $factor->type = 'online';
        $factor->factor_item_id = $factorItem->id;
        $factor->callcenter_id = auth()->user()->id;
        $factor->representation_id = auth()->user()->admin_representation_id;
        $factor->center_id = $factorItem->center_id;
        $factor->expired_at = now()->addDay(3);
        $factor->save();

        event(new sendlinkFactor($user, $factor, auth()->user()->family, 'online'));

        return redirect()->back()->with([
            'send' => true
        ]);
    }
    /**
     * @param FactorItem $factor_item
     * @param $value
     * @return void
     * store image confirm by role user
     */
    private function storeImageConfirm(FactorItem $factor_item , $value)
    {
        $values = $factor_item->factor_values ? $factor_item->factor_values : [];
        $factor_values = Arr::add($values, $value, auth()->user()->id);
        $factor_item->update([
            'factor_values' => $factor_values,
        ]);
    }

    /**
     * @param FactorItem $factorItem
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeFinancialPlansType(Request $request , FactorItem $factorItem)
    {
        $factor_items_collection = FactorItem::getFatorItemCollection( $factorItem->supervisor );
        $factor_items_collection->update(['financial_plans_type_id' => $request->financial_plans_type]);
        $factorItem = FactorItem::find($factorItem->id);
        //calculate registeration debt if factorItem type is credit
        $factorItem->supervisor->type ? self::calculateRegisterationDebt($factorItem->supervisor) : '';
        //check if the financialPlansType selected matches the factorItem payment type
        if($factorItem->financialPlansType && !FinancialPlansType::checkFinancialPlansTypeByPaymentType($factorItem->financialPlansType,$request->type))
        {
            return redirect()->back()->with( [
                'failByMessage' => 'طرح پرونده انتخاب شده با نوع پرداخت پرونده مطابقت ندارد لطفا طرح پرونده مربوط به همان نوع پرداخت را انتخاب کنید.'
            ] );
        }
        $supervisor_factor_item = FactorItem::changeType($factorItem,$request->type);
        $user = $supervisor_factor_item->factor->user;
        FactorItem::calculate($supervisor_factor_item, $supervisor_factor_item->total_price, $supervisor_factor_item->type, $user);
        $supervisor_factor_item->type ? TotalFactorItem::updateCalculate( $supervisor_factor_item->totalFactorItem ) : '';

        $url = URL::previous();
        if(Str::contains($url, 'totalFactorItem') && !$supervisor_factor_item->totalFactorItem)
        {
            return redirect(url('dashboard/factorItem/'.$factorItem->resnumber))->with( [
                'update' => 'success'
            ] );
        }
        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param FactorItem $factor_item //factorItem supervisor
     * @return void
     */
    private function calculateRegisterationDebt(FactorItem $factor_item)
    {
        $total_factor_item  = $factor_item->totalFactorItem;
        $registeration_debt_paid = $total_factor_item->factors->where('title' , 'registeration_debt')->where('status' , true)->sum('price') + $total_factor_item->factor->price;
        $financial_plans_type_price = $total_factor_item::getFinancialPlansTypePriceByFactorItems($total_factor_item);
        $diffrence_registeration_price = $financial_plans_type_price - $registeration_debt_paid;
        if($diffrence_registeration_price < 0)
        {
            $user = $factor_item->factor->user;
            $user->update(['credit' => $user->credit + $diffrence_registeration_price]);
            $user->creditDetails()->create([
                'price' => $diffrence_registeration_price,
                'registrar_id' => auth()->user()->id,
                'type' => 'increase',
                'description' => 'اضافه پرداخت هزینه ثبت نام با تغییر طرح پرونده',
            ]);
        }
        $total_factor_item->property->update(['registeration_debt' => $diffrence_registeration_price]);
    }
    /**
     *@param \App\Models\FactorItem $factorItem
     *@param \Illuminate\Http\Request $request
     *@return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory|void
     */
    public function getForm(FactorItem $factorItem , Request $request)
    {
        $user = $factorItem->factor->user;
        $result = self::setItem( $factorItem );
        $factorItem = $result['factorItem'];
        if($request->ajax()){
            $view=view('Admin.factoritem.show.registerationInitialConsutation', compact('factorItem', 'user'))->render();
            return response([
                'data'=>[
                    'view'=> $view
                ]
            ]);
        }
    }
    /**
    *@param Request $request
    *@return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function setRegisterationInitialConsutation(Request $request)
    {
        $item = $request->item;
        $factor_item = FactorItem::find( $item[ 'id' ] );
        self::formData( $item );

        Session::changeVisitStatusByFactorItem($factor_item,6);

        if($factor_item->totalFactoItem && $factor_item->totalFactoItem->confirm)
        {
            return redirect()->back()->with( [
                'update' => 'success'
            ] );
        }
        $factor_item->update( [
            'status_factor' => 2,
        ] );
        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param \App\Models\FactorItem $factorItem
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startWork(FactorItem $factorItem)
    {
        $factorItem->update( [
            'status_factor' => 10,
            'confirm' => true,
        ] );
        //close older sessions
        Session::whereIn('status',[3,5])->where('factor_item_id',$factorItem->id)->update([
            'status'=>7,
            'description'=>'این ویزیت به دلیل اینکه در حالت مشاوره شد نبود و ویزیت جدیدی برای کاربر ایجاد شد توسط سیستم لغو گردید .'
        ]);
        //create session
        if ($factorItem->factoritemable->session==1){
        Session::create( [
            'status' => 5 ,
            'user_id' => auth()->user()->id,
            'factor_item_id' => $factorItem->id,
            'date' => date('Y-m-d H:i:s'),
            'description_before' => 'این پرونده آماده اجرا جهت شروع کار می باشد.',
        ]);
        }
        return redirect()->back()->with( [
            'update' => 'success'
        ] );

    }
}
