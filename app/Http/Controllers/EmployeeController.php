<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\City;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EmployeeController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function showRegistrationForm()
    {
        $provinces = Province::get();
        $cities = City::get();
        return view( 'auth.employee.register' , compact('provinces', 'cities' ));
    }
    /**
     * @param array $data
     * @return \Illuminate\Validation\Validator
     */
    protected function validator( array $data )
    {
        return Validator::make( $data, [
            'cellphone' => 'numeric|required|regex:/(09)[0-9]{9}/|digits:11|',
            'nationalCpde' => 'numeric|req|digits:10|',
        ] );
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register( Request $request )
    {
        $birt_date = Helper::numberConverter(str_replace( "/", "", $request->birthDate ));
        try {
            $result = Http::post(config( 'services.emta.url' ).'/api/v1/user/VerifyUserByPartner', [
                'client_id' => config('services.emta.client_id'),
                'client_secret' => config('services.emta.secret_id'),
                'cellphone' => $request->phone,
                'verificationCode' => $request->verificationCode,
                'nationalCode' => $request->nationalCode,
                'birthDate' => $birt_date,
            ]);
        } catch (\Exception $e) {
                Log::error($e->getMessage());
        }
        $result = json_decode($result , true);
        \App\Http\Controllers\Admin\UserController::createUser($request);
        if($result['Data']['success'])
        {
            return redirect()->back()->with([
                'create' => 'success'
            ]);
        }
        return redirect()->back()->with([
            'failByMessage' => $result['Data']['message']
        ]);
    }

    /**
    * check user is registered or not
    * @param Request $request
    * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
    */

    public function checkUserIsRegister( Request $request )
    {
        $message = "";
        try {
            $result = Http::post(config( 'services.emta.url' ).'/service/v1/oauth/UserIsExist', [
                'client_id' => config('services.emta.client_id'),
                'client_secret' => config('services.emta.secret_id'),
                'cellphone' => $request->phone,
            ]);
        } catch (\Exception $e) {
                Log::error($e->getMessage());
        }
        session( [ 'login_request_link' => url()->previous() ] );
        $result = json_decode($result , true);

        $isExists = $result['Data']['IsExists'];

        if(!$isExists)
        {
            try {
                $result = Http::post(config( 'services.emta.url' ).'/api/v1/user/RegisterByPartner', [
                    'client_id' => config('services.emta.client_id'),
                    'client_secret' => config('services.emta.secret_id'),
                    'cellphone' => $request->phone,
                ]);
            } catch (\Exception $e) {
                    Log::error($e->getMessage());
            }
        }
        else
        {
            $message = $result['Data']['message'];
        }
        $request->merge( [
            'phone' => $request->phone,
        ] );

        return response( [ 'data'=>[ 'userIsRegistered'=>$isExists, 'phone'=>$request->phone , 'message'=> $message] ] );
    }
}
