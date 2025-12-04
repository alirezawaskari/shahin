<?php

namespace App\Http\Controllers\Admin;

use App\Events\smsService;
use App\Events\smsServiceSession;
use App\Exports\SessionExport;
use App\Helpers\Helper;
use App\Models\FactorItem;
use App\Models\FactorItemServiceTariff;
use App\Models\Session;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Http\Controllers\Controller;
use App\Models\UserPerformance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\SessionCollection;
use App\Models\AllocationEmployeeShareCenter;
use App\Models\Center;
use App\Models\PercentageAllocationEmployee;

class SessionController extends Controller {

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function index() {
        $user = auth()->user();
        if ( $user->isAbleTo(['session-detail','session']) ) {
            $delete = $user->isAbleTo( 'session-delete' );
            $has_center = $user->center()->exists();
            $confirm = $user->isAbleTo( 'session-confirm' );
            return view( 'Admin.session.all', compact( 'delete', 'confirm' , 'has_center' ) );
        }
        return abort( 401 );
    }

    /**
     * @param Request $request
     * @return SessionCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter(Request $request) {
        if ( auth()->user()->isAbleTo( 'session' ) ) {
            $sessions = Session::filter()->where( 'agent_id', auth()->user()->id )->orderBy( 'date', 'desc' )->where( 'status', '!=', 0 );
        } elseif (auth()->user()->isAbleTo( 'session-detail')) {
            $sessions = Session::filter()->orderBy( 'date', 'desc' );
        }

        if (isset($request->excel) && $request->excel == 'on') {
            return (new SessionExport($sessions))->download('sessions.xlsx');
        }
        return new SessionCollection( $sessions , $sessions->paginate( 20 ) );
    }


    //desktop for complete sessions

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function desktopComplete() {
        $user = auth()->user();
        if ( $user->isAbleTo( 'session-desktop-complete' ) ) {
            $title = 'میزکار مراجعات';
            $delete = $user->isAbleTo( 'session-delete' );
            $has_center = $user->center()->exists();
            $type = 'complete';
            $url = 'desktop-complete/filter';
            return view( 'Admin.session.desktopComplete', compact('has_center', 'delete', 'type', 'url' , 'title') );
        }
        return abort( 401 );
    }

    /**
     * @param Request $request
     * @return mixed
     * filter for desktop complete session
     */
    public function filterDesktopComplete(Request $request) {

        $sessions = Session::filter()->where( 'status', 0 )->orderBy( 'date', 'desc' );
        if ($request->start_date || $request->end_date){
            $sessions->whereDate('date', '>=' ,Carbon::today())->whereDate('date', '<=' ,Carbon::tomorrow());
        }
        if (isset($request->excel) && $request->excel == 'on') {
            return (new SessionExport($sessions))->download('sessions.xlsx');
        }
        return new SessionCollection($sessions , $sessions->paginate(20));
    }
    /**
     * @param Request $request
     * @return mixed
     * filter for desktop complete session
     */
    public function filterDesktopCompleteTurnRatings(Request $request) {

        $sessions = Session::filter()->whereIn( 'status', [3,5,8] )->whereDate('date', '<=' ,Carbon::today())->orderBy( 'date', 'desc' );
        if ($request->start_date || $request->end_date){
            $sessions ->whereDate('date', '>=' ,Carbon::today())->whereDate('date', '<=' ,Carbon::tomorrow());
        }
        if (isset($request->excel) && $request->excel == 'on') {
            return (new SessionExport($sessions))->download('sessions.xlsx');
        }
        return new SessionCollection( $sessions , $sessions->paginate(20));
    }
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function desktopConfirm() {
        $user = auth()->user();
        if ( $user->isAbleTo('session-desktop-confirm') ) {
            $title = 'میزکار تایید جلسات';
            $delete = $user->isAbleTo( 'session-delete' );
            $has_center = $user->center()->exists();
            $type = 'confirm';
            $url = 'desktop-confirm/filter';
            return view( 'Admin.session.desktopConfirm', compact('has_center' , 'delete', 'type', 'url' , 'title') );
        }
        return abort( 401 );
    }

    //desktop for confirm session

    /**
     * @param Request $request
     * @return SessionCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterDesktopConfirm(Request $request) {
        $sessions = Session::filter()->where( 'status', 2 )->orderBy( 'date', 'desc' );
        if (isset($request->excel) && $request->excel == 'on') {
            return (new SessionExport($sessions))->download('sessions.xlsx');
        }
        return new SessionCollection( $sessions , $sessions->paginate( 20 ) );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request ) {
        $input = $request->all();

        $this->validate($request, [
            'agent_id' => 'required',
        ]);
        $input[ 'date' ] = Helper::vertaToMiladi(Helper::numberConverter($input[ 'date' ]));
        unset($input[ 'check' ]);
        $session = Session::create( $input );
        if($request->check)
        {
            event( new smsServiceSession( $session->factorItem, $request->date ) );
        }

        return redirect()->back();
    }
    /**
     * @param Session $session
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update( Session $session, Request $request ) {
        $validator = \Validator::make($request->all(), [
            'agent_id' => 'required',
        ]);

        if ($validator->fails()) {
            $arr = array( 'errors' => 'لطفا تمام مقادیر را پر کنید' , 'status' => true);
            return Response()->json( $arr );
        }

        $data = $request->all();
        $session = Session::find( $data[ 'id' ] );
        //if selected service tariff for created session
        if ( isset( $data[ 'factor_item_service_tariff_id' ] ) )
        {
            //create session by select multi service tariffs
            foreach ( $data[ 'factor_item_service_tariff_id' ] as $item ) {
                $factor_item_service_tariff = FactorItemServiceTariff::where( 'id', $item )->first();
                // if($factor_item_service_tariff->sessions->where('status' , 2)->count())
                // {
                //     $arr = array( 'errors' => 'جلسه تایید نشده برای این درمان وجود دارد.', 'status' => true );
                //     return Response()->json( $arr );
                // }
                $price = $factor_item_service_tariff ? ( $factor_item_service_tariff->price - $factor_item_service_tariff->discount ) / $factor_item_service_tariff->session_number : 0;
                $factor_item = $session->factorItem;
                $agent = $request->agent_id ? User::find( $request->agent_id ) : '';
                if ( $session->factor_item_service_tariff_id ) {
                    //create new session if seted service tariff for created session
                    $session = Session::create( [
                        'description' => $session->description,
                        'status' => 2,
                        'price' => $price,
                        'laboratory_price' => $session->laboratory_price,
                        'user_id' => auth()->user()->id,
                        'agent_id' => isset( $agent->id ) ? $agent->id : null,
                        'factor_item_service_tariff_id' => $item,
                        'assistant_id' => $session->assistant_id,
                        'factor_item_id' => $session->factor_item_id,
                        'date' => $session->date,
                        'description_before' => $session->description_before,
                    ] );
                } else {
                    //update created session if not selected service tariff for created session
                    $session->update( [
                        'description' => $data[ 'description' ],
                        'status' => 2,
                        'price' => $price,
                        'laboratory_price' => ( float ) str_replace( ',', '', $data[ 'laboratory_price' ] ),
                        'user_id' => auth()->user()->id,
                        'agent_id' => isset( $agent->id ) ? $agent->id : null,
                        'factor_item_service_tariff_id' => $item,
                        'assistant_id' => $data[ 'assistant_id' ],
                    ] );
                }

                //update credit file
                self::updateCreditFile($session);

                $agent ? $session->factorItemServiceTariff->update(['user_id' => $agent->id]) : '';
            }
        } else {
            $session->update( [
                'description' => $data[ 'description' ],
                'status' => 2,
                'price' => 0,
                'laboratory_price' => ( float ) str_replace( ',', '', $data[ 'laboratory_price' ] ),
                'user_id' => auth()->user()->id,
                'assistant_id' => $data[ 'assistant_id' ],
            ] );
            Session::userSessionEndEntry($session);
        }

        $factor_item = $session->factorItem;
        if($factor_item->type == 0 || $factor_item->status_factor == 10)
        {
            $factor_item->update( [
                'status_factor' => 6,
                'confirm' => 1
            ] );
        }
        $arr = array( 'msg' => 'باموفقیت ثبت شد', 'status' => true);
        return Response()->json( $arr );
    }
    /**
     * @param Session $session
     * @param FactorItemServiceTariff $factor_item_service_tariff
     * @param User $agent
     * @return void
     * calculate agent's income after end factor_item_service_tariff's work
     */
    private function calculateEndWork( Session $session, FactorItemServiceTariff $factor_item_service_tariff, User $agent ) {
        if ( $factor_item_service_tariff->status && $session->agent_id ) {
            $price = $factor_item_service_tariff->sessions->where( 'agent_id', $agent->id )->where( 'status', 1 )->sum( 'price' );
            $laboratory_price = $factor_item_service_tariff->sessions->where( 'agent_id', $agent->id )->where( 'status', 1 )->sum( 'laboratory_price' );
            $user_performance_price = $price * ( $agent->employee->percent / 100 ) - ( $laboratory_price / 2 );
            $user_performance_sessions_done = $factor_item_service_tariff->sessions->where( 'agent_id', $agent->id )->where( 'status', 1 );
            $description = '';

            foreach ( $user_performance_sessions_done as $session ) {
                $description = $description.'/'.$factor_item_service_tariff->value . ' - ' . $session->description . '- هزینه لابراتوار' . number_format( $session->laboratory_price / 2 ) . '(ریال)';
            }
            //set emplloyee performance from percentageAllocation employee
            $user_performance = UserPerformance::store(null, $session->agent , $description , $price , $user_performance_price , $session->factorItem , $session->date);
            //set user performance sessions done
            UserPerformance::setSessionsDone($user_performance , $user_performance_sessions_done);
        }
    }
    /**
     * @param Session $session
     * @param FactorItemServiceTariff $factor_item_service_tariff
     * @param User $agent
     * @return void
     * calculate agent's income after end session
     */
    private function calculateEndSession(Session $session, FactorItemServiceTariff $factor_item_service_tariff, User $agent ) {
        $user_performance = UserPerformance::create( [
            'factor_item_id' => $session->factor_item_id,
            'description' => $factor_item_service_tariff->value . ' - ' . $session->description . '- هزینه لابراتوار' . number_format( $session->laboratory_price / 2 ) . '(ریال)',
            'price' => $session->price,
            'ancillary_costs' => $session->laboratory_price,
            'date' => $session->date,
            'callcenter_id' => auth()->user()->id,
            'user_id' => $session->agent_id,
        ] );

        $resNum = rand( 12, 99 ) . substr( Carbon::now()->timestamp, 6, 11 ) . substr( $agent->phone, 7, 11 );

        $WalletTransaction = WalletTransaction::create( [
            'user_id' => $session->agent_id,
            'reason_id' => $user_performance->id,
            'resnumber' => $resNum,
            'price' => $session->price * ( $agent->employee->percent / 100 ) - ( $session->laboratory_price / 2 ),
            'status' => '1',
            'type' => '1',
            'created_at' => $session->date,
        ] );
        $agent ? $agent->update( [ 'wallet' => $agent->wallet + $WalletTransaction->price ] ) : '';
    }
    /**
     * @param \App\Models\Session $session
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirm( Session $session ) {
        if($session->status == 1)
        {
            return redirect()->back()->with( [
                'failByMessage' => 'این جلسه قبلا تایید شده است.'
            ] );
        }

        $factor_item = $session->factorItem;
        $supervisor_factor_item = $factor_item->supervisor;
        $is_credit = FactorItem::checkCredit( $supervisor_factor_item, $session->price );
        if ( !$is_credit ) {
			return redirect()->back()->with( [
                'failByMessage' => 'مبلغ پرداختی کاربر کمتر از حد مجاز می باشد'
            ] );
        }

        $agent = $session->agent_id ? User::find( $session->agent_id ) : '';
        $factor_item_service_tariff = $session->factor_item_service_tariff_id ? $session->factorItemServiceTariff : '';
        $session->update( [ 'status' => 1 , 'confirmer_id' => auth()->user()->id] );
        if ($factor_item_service_tariff) {
            $session_count = count( $factor_item_service_tariff->sessions->where( 'status', 1 ) );
            $session_number = $factor_item_service_tariff->session_number;
            $price = $factor_item_service_tariff ? ($factor_item_service_tariff->price - $factor_item_service_tariff->discount) : 0;
            if($session_count === $session_number)
            {
                $factor_item_service_tariff->update( [ 'status' => 1 ] );
                //set emplloyee performance from percentageAllocation employee
                PercentageAllocationEmployee::setUserPerformance($factor_item , 'all_total_price' , $price , $session->date , $factor_item_service_tariff);
                //set emplloyee performance from allocation employee share centers
                AllocationEmployeeShareCenter::setUserPerformance($factor_item , $factor_item_service_tariff , $price , $agent , $session->date);
            }
            self::checkServiceTariffMultiStage($session_count,$factor_item_service_tariff,$factor_item,$price,$session->date);
            if ($session->agent_id && isset( $session->agent->employee ) ) {
                if ( $session->agent->employee->pay_type == 'pay_end_work' ) {
                    $this->calculateEndWork( $session, $factor_item_service_tariff, $agent );
                } else {
                    $this->calculateEndSession( $session, $factor_item_service_tariff, $agent );
                }
            }
        }

        // $factor_item = $session->factorItem;

        // //update credit file
        // if ( $factor_item->type ) {
        //     $total_factor_item = $supervisor_factor_item->totalFactorItem;
        //     $total_factor_item->update( [ 'credit' => $total_factor_item->credit - $session->price ] );

        //     $total_factor_item->factorItems()
        //     ->where( 'factoritemable_id', $factor_item->factoritemable_id )
        //     ->where( 'factoritemable_type', $factor_item->factoritemable_type )
        //     ->update( [
        //         'credit' => $supervisor_factor_item->credit - $session->price,
        //     ] );
        // }

        return redirect()->back()->with( [
            'update' => 'success'
        ] );
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rejectSession( Request $request) {
        $session = Session::find( $request->session_id );
        $session->update( [ 'status' => 7 , 'description' => $request->description ] );
        event( new smsService( $session->factorItem , 'rejectSession') );
        return redirect()->back();
    }
    /**
     * @param \App\Models\Session $session
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse
     */
    public function destroy( Session $session ) {

        if ($session->status == 1 && !auth()->user()->isAbleTo('confirmed-session-delete'))
            {
                if(\request()->ajax()){
                        return response(['message'=>'این جلسه تایید شده است.','status'=>'error']);
                 }else{
                    return redirect()->back()->with( [
                        'failByMessage' => 'این جلسه تایید شده است.'
                    ] );
                }
        }

        $user_performances = $session->userPerformances;
        isset( $session->factorItemServiceTariff ) ? $session->factorItemServiceTariff->update( [ 'status' => 0 ] ) : '';
        $factor_item = $session->factorItem;
        $price = isset( $session->factorItemServiceTariff ) ? ($session->factorItemServiceTariff->price - $session->factorItemServiceTariff->discount) : 0;
        $supervisor_factor_item = $factor_item->supervisor;
        if ( $user_performances->count() > 0 ) {
            //update credit file
            if ( $supervisor_factor_item->type ) {
                //$user_performance->factorItem->update( [ 'credit' => $user_performance->factorItem->credit + $user_performance->price ] );
                $total_factor_item = $supervisor_factor_item->totalFactorItem;
                $total_factor_item->update( [ 'credit' => $total_factor_item->credit + $price ] );
                $total_factor_item->factorItems()->where( 'factoritemable_id', $supervisor_factor_item->factoritemable_id )->where( 'factoritemable_type', $supervisor_factor_item->factoritemable_type )->update( [
                    'credit' => $supervisor_factor_item->credit + $price,
                ] );
            }
            foreach($user_performances as $user_performance)
            {
                $user_performance->sessions()->delete();
                $wallet_transaction = $user_performance->walletTransaction;
                $user_performance->walletTransaction()->delete();
                $user_performance->delete();
            }

            $wallet_transaction->user->update( [ 'wallet' => $wallet_transaction->user->wallet - $wallet_transaction->price ] );
        } else {
            //update credit file
            if ( $supervisor_factor_item->type && $session->status == 1 ) {
                //$user_performance->factorItem->update( [ 'credit' => $user_performance->factorItem->credit + $user_performance->price ] );
                $total_factor_item = $supervisor_factor_item->totalFactorItem;
                $total_factor_item->update( [ 'credit' => $total_factor_item->credit + $session->price ] );
                $total_factor_item->factorItems()->where( 'factoritemable_id', $supervisor_factor_item->factoritemable_id )->where( 'factoritemable_type', $supervisor_factor_item->factoritemable_type )->update( [
                    'credit' => $supervisor_factor_item->credit + $session->price,
                ] );
            }
            $session->delete();
        }
        if(\request()->ajax()){

        }
        return redirect()->back()->with( [
            'delete' => 'success'
        ] );
    }
    /**
     * @param Session $session
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function userSessionStartEntry(Session $session)
    {
        Session::userSessionStartEntry($session);
        return response([
            'status'=>'success',
            'message'=>'ورود کاربر با موفقیت ثبت شد .'
        ]);
    }

    /**
     * @param Session $session
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function userSessionEndEntry(Session $session)
    {
        Session::userSessionEndEntry($session);
        return response([
            'status'=>'success',
            'message'=>'خروج کاربر با موفقیت ثبت شد .'
        ]);
    }
    /**
     * @param mixed $session_count
     * @param mixed $factor_item_service_tariff
     * @param mixed $factor_item
     * @param mixed $price
     * @param mixed $date
     * @return void
     */
    private function checkServiceTariffMultiStage($session_count,$factor_item_service_tariff,$factor_item,$price,$date)
    {
        if(($factor_item_service_tariff->serviceTariff->multi_stage == 2 && $session_count == 1) || $factor_item_service_tariff->status == 1)
        {
            //set superadmin center performance
            $purchase_price = $factor_item_service_tariff->serviceTariff->purchase_price/$factor_item_service_tariff->serviceTariff->multi_stage;
            Center::setUserPerformance($factor_item , $factor_item_service_tariff , $price ,$purchase_price , $date);
        }
    }
    /**
     * Summary of updateCreditFile
     * @param \App\Models\Session $session
     * @return void
     */
    private function updateCreditFile(Session $session)
    {
        $factor_item = $session->factorItem;
        $supervisor_factor_item = $factor_item->supervisor;
        if ( $factor_item->type ) {
            $total_factor_item = $supervisor_factor_item->totalFactorItem;
            $total_factor_item->update( [ 'credit' => $total_factor_item->credit - $session->price ] );
            $total_factor_item->factorItems()
                ->where( 'factoritemable_id', $factor_item->factoritemable_id )
                ->where( 'factoritemable_type', $factor_item->factoritemable_type )
                ->update( [
                    'credit' => $supervisor_factor_item->credit - $session->price,
            ] );
        }
    }
}
