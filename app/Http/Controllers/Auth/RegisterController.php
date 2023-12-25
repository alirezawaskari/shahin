<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Callcenter;
use App\Models\Callcenterstatus;
use App\Models\City;
use App\Models\FinancialPlansType;
use App\Models\Province;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
 {
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    protected $redirectTo = '/agency';

    public function __construct()
 {
        $this->middleware( 'guest' );
    }

    public function showRegistrationForm()
 {
        $provinces = Province::get();
        $citys = City::get();
        $services = Service::whereStatus( true )->where( 'isShow', true )->orderBy( 'created_at', 'desc' )->get();
        $financial_plans_types = FinancialPlansType::whereStatus( true )->where( 'is_show', true )->get();
        return view( 'auth.register', compact( 'financial_plans_types' , 'provinces', 'citys', 'services' ) );
    }
    protected function validator( array $data )
 {
        return Validator::make( $data, [
            'name' => [ 'required', 'string', 'max:255' ],
            'family' => [ 'required', 'string', 'max:255' ],
            'phone' => 'numeric|required|unique:users|unique:users,phone|regex:/(09)[0-9]{9}/|digits:11|',
            'province_id' => [ 'required', 'numeric' ],
            'city_id' => [ 'required', 'numeric' ],
            'service_id' => [ 'required' ],
            'area_id' => [ 'nullable', 'numeric' ],
        ] );
    }

    protected function create( array $data )
 {
        $user = User::create( [
            'name' => $data[ 'name' ],
            'family' => $data[ 'family' ],
            'phone' => Helper::numberConverter( $data[ 'phone' ] ),
            'level' => 'factor',
            'province_id' => $data[ 'province_id' ],
            'city_id' => $data[ 'city_id' ],
            'service_id' => $data[ 'service_id' ] ? $data[ 'service_id' ] : null,
            'area_id' => $data[ 'area_id' ],
            'active' => 1,
            'password' => Hash::make( Helper::numberConverter( $data[ 'phone' ] ) ),
            'representation_id' => Arr::has( $data, 'representation' ) ? $data[ 'representation' ] : null,
        ] );
        if ( $data[ 'service_id' ] == 100 )
 {
            $city_id = City::where( 'id', $data[ 'city_id' ] )->first();
            $province_id = Province::where( 'id', $data[ 'province_id' ] )->first();
            $agency = Agency::create( [
                'name' => $data[ 'name' ].' '.$data[ 'family' ],
                'phone' => Helper::numberConverter( $data[ 'phone' ] ),
                'province' => $province_id->name,
                'city' => $city_id->name,
            ] );
        }
        // assign to callcenter
        $last_user = Callcenterstatus::latest()->first();
        if ( Arr::has( $data, 'representation' ) ) {
            $admin_users = User::where( 'level', 'admin' )->where( 'admin_representation_id', $data[ 'representation' ] )->get();
        } else {
            $admin_users = User::where( 'level', 'admin' )->where( 'id', '!=', $last_user->id )->where( 'admin_representation_id', null )->get();
        }
        foreach ( $admin_users as $u ) {
            if ( $u->hasRole( 'callcenter' ) ) {
                $callcenterusers[] = $u;
            }
        }
        $task=Callcenter::create([
            'reg_id' => $user->id,
            'user_id' => array_rand( $callcenterusers )->id,
            'type' => 0,
            'status' => '0',
            'representation_id' => Arr::has( $data, 'representation' ) ? $data[ 'representation' ] : null
        ]);
        if (isset( $data[ 'service_id' ])&& !is_null($data[ 'service_id' ])){
            $task->services()->attach($data['service_id']);
        }
        Callcenterstatus::create( [
            'user_id' => array_rand( $callcenterusers )->id,
        ] );

        return $user;
    }

    public function fastregister( Request $request )
 {
        session( [ 'login_request_link' => url()->previous() ] );
        $inputs = $request->all();
        $inputs[ 'phone' ] = Helper::numberConverter( $inputs[ 'phone' ] );
        $validator = Validator::make( $inputs, [
            'name' => 'required|string',
            'family' => 'required|string',
            'province_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'gender' =>[ 'required',Rule::in(array(1,0))],
            'area_id' => 'nullable|numeric',
            'phone' => 'numeric|required|unique:users|unique:users,phone|regex:/(09)[0-9]{9}/|digits:11|',
        ] );
        if ( $validator->fails() ) {
            return redirect()->back()->withErrors( $validator )->with( [
                'fastregister' => 'error',
            ] );
        }
        $pass = rand( 12345, 99999 );
        $serviceId = $request->input( 'service_id' );
        $user = User::create( [
            'name' => $request->input( 'name' ),
            'family' => $request->input( 'family' ),
            'phone' => Helper::numberConverter( $request->input( 'phone' ) ),
            'level' => 'factor',
            'province_id' => $request->input( 'province_id' ),
            'city_id' => $request->input( 'city_id' ),
            'area_id' => $request->input( 'area_id' ),
            'sex' => $request->gender,
            'active' => 1,
            'password' => Hash::make( $pass ),
            'representation_id' => $request->has( 'representation_id' ) ? $request->input( 'representation_id' ) : null,
            'callcenter_id' => $request->has( 'callcenter_id' ) ? $request->input( 'callcenter_id' ) : null,
            'service_id' => $request->has( 'service_id' ) ? $serviceId : null,
        ] );
        if ( $request->input( 'type_service' ) == 100 )
        {
            $city_id = City::where( 'id', $request->input( 'city_id' ) )->first();
            $province_id = Province::where( 'id', $request->input( 'province_id' ) )->first();
            $agency = Agency::create( [
                'name' => $request->input( 'name' ).' '.$request->input( 'family' ),
                'phone' => Helper::numberConverter( $request->input( 'phone' ) ),
                'province' => $province_id->name,
                'city' => $city_id->name,
                'agency_type_id' => 1,
            ] );

            if ( $request->file( 'imgmeli' ) ) {
                $imgmeli = Helper::uploadImage( $request->file( 'imgmeli' ), 'agency' );
                $agency->update( [ 'imgmeli' => $imgmeli ] );
            }
            if ( $request->file( 'imgturnover' ) ) {
                $imgturnover = Helper::uploadImage( $request->file( 'imgturnover' ), 'agency' );
                $agency->update( [ 'imgturnover' => $imgturnover ] );
            }
        }
        if ( $user ) {
            //factor login
            Auth::login( $user );
            //send pass with sms
            //event( new UserActivation( $user, $pass ) );

            // assign to callcenter
            $last_user = Callcenterstatus::latest()->first();
            $admin_users = User::where( 'level', 'admin' )->where( 'province_id', $request->input( 'province_id' ) )->get();
            foreach ( $admin_users as $u ) {
                if ( $u->hasRole( 'callcenter' ) ) {
                    $callcenterusers[] = $u->id;
                }
            }
            if ( !isset( $callcenterusers ) )
 {
                $admin_users = User::where( 'level', 'admin' )->where( 'id', '!=', $last_user->id )->where( 'province_id', 5 )->get();
                foreach ( $admin_users as $u ) {
                    if ( $u->hasRole( 'callcenter' ) ) {
                        $callcenterusers[] = $u->id;
                    }
                }
            }
            $id = array_rand( $callcenterusers );
            if ( $request->input( 'callcenter_id' ) )
 {
                $user_link = User::find( $request->input( 'callcenter_id' ) );
                $agent = $user_link->hasPermission( 'callcenter' ) ? null : $user_link->id;
                $callcenter = !$user_link->hasPermission( 'callcenter' ) ? $id : $user_link->id;
            }
            $task = Callcenter::create( [
                'reg_id' => $user->id,
                'user_id' => isset( $callcenter ) ? $callcenter : $id,
                'agent_id' => isset( $agent ) ? $agent : null,
                'type' => isset( $agent ) ? 3 : 0,
                'status' => '0'
            ] );

            $task->update([
                'representation_id' => !$task->representation_id &&  $task->callcenter ? $task->callcenter->admin_representation_id : $task->representation_id
            ]);


            if ($request->service_id){
                $task->services()->attach(array($request->service_id));
            }
            Callcenterstatus::create( [
                'user_id' => $request->input( 'callcenter_id' ) != null ? $request->input( 'callcenter_id' ) : $id,
            ] );
            $id = $request->input( 'service_id' );
            $financial_plans_type_id = $request->input('financial_plans_type_id');
            $type = 'item';
            $representation = $request->representation_id;
            $callcenter = $request->callcenter_id;

            //Create First Cart Session
            $cart = [
                'items' => [],
                'total' => [
                    'price' => 0,
                    'count' => 0,
                    'price_tax' => 0,
                ],
                'callcenter' => $callcenter,
                'representation' => $representation,
                'discount' => 0,
                'task_id' => $task->id,
            ];
            if ( session()->has( 'cart' ) )
 {
                if ( session()->get( 'cart.representation' ) != $representation ) {
                    session()->forget( 'cart' );
                    session()->put( 'cart', $cart );
                    session()->put( 'cart.representation', $representation );
                    session()->put( 'cart.callcenter', $callcenter );
                    session()->put( 'cart.task_id', $task->id );
                } else {
                    if ( session()->get( 'cart.callcenter' ) != $callcenter )
 {                      session()->put( 'cart.task_id', $task->id );
                        session()->put( 'cart.callcenter', $callcenter );
                    }
                }
            } else {
                session()->put( 'cart', $cart );
            }
            //Check Item

            if ( session()->has( "cart.items.{$type}_{$id}" ) ) {
                return response( [ 'operator' => false ] );
            } else {
                //create
                if ( $type == 'service' && count( auth()->user()->factors->where( 'status', 1 ) ) > 0 && $id = 7 )
 {
                    session()->put( "cart.items.{$type}_{$id}", [
                        'id' => 14,
                        'type' => $type,
                        'count' => 1,
                        'price' => Service::find( '14' )->price,
                        'tax' => Service::find( $id )->tax,
                        'price_tax' =>( Service::find( '14' )->price )* ( Service::find( '14' )->tax ) /100,
                    ] );
                } else {
                    $tax = Service::find( $id )->tax;
                    session()->put( "cart.items.{$type}_{$id}", [
                        'id' => $id,
                        'service_id' => $serviceId,
                        'financial_plans_type_id' => $financial_plans_type_id,
                        'type' => $type,
                        'count' => 1,
                        'price' => $type == 'service' ? Service::find( $id )->price : FinancialPlansType::find( $financial_plans_type_id )->price,
                        'tax' => $tax,
                        'price_tax' =>( $type == 'service' ? Service::find( $id )->price : FinancialPlansType::find( $financial_plans_type_id )->price )* ( $tax ) /100,
                    ] );
                }
                if ( $type == 'service' && !is_null( Service::find( $id )->gift ) ) {
                    foreach ( Service::find( $id )->gift as $item ) {
                        session()->put( "cart.items.service_{$item}", [
                            'id' => $item,
                            'service_id' => $serviceId,
                            'financial_plans_type_id' => $financial_plans_type_id,
                            'type' => 'service',
                            'count' => 1,
                            'price' => Service::find( $item )->price,
                        ] );
                    }
                }
            }

            $this->calculateTotals();
            if ( $request->input( 'type_service' ) == 100 )
            {
                return redirect()->back();
            }
            return redirect( 'cart' );

        }

    }

    protected function registered( Request $request, $user )
 {
        if ( $request->has( 'representation' ) ) {
            return redirect()->back();
        }
    }

    private function calculateTotals()
 {
        $items = collect( session()->get( 'cart.items' ) );
        session()->put( 'cart.total', [
            'count' => $items->sum( 'count' ),
            'price' => $items->sum( 'price' ),
            'price_tax' => $items->sum( 'price_tax' ),
        ] );
    }

    /**
    * check user is registered or not
    * @param Request $request
    * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
    */

    public function checkUserIsRegister( Request $request )
 {
        session( [ 'login_request_link' => url()->previous() ] );
        $userIsExist = false;
        $user = User::where( 'phone', $request->phone_number )->first();
        if ( !is_null( $user ) ) {
            $userIsExist = true;
        }
        $resetPassController = new ResetPasswordController();
        $request->merge( [
            'phone' => $request->phone_number,
        ] );
        $resetPassController->reset( $request );
        return response( [ 'data'=>[ 'userIsRegistered'=>$userIsExist, 'phone'=>$request->phone_number ] ] );
    }
}
