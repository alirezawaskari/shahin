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
use Illuminate\Validation\Rule;

class EmployeeRegisterController extends Controller
 {
    public function showRegistrationForm()
    {
        return view( 'auth.employee.register' );
    }
    protected function validator( array $data )
    {
        return Validator::make( $data, [
            'cellphone' => 'numeric|required|regex:/(09)[0-9]{9}/|digits:11|',
            'nationalCpde' => 'numeric|req|digits:10|',
        ] );
    }

    protected function register( array $data )
    {
        $client = new nusoap_client(config( 'services.emta.url' ).'/api/v1/oauth/UserIsExist');
        $client->soap_defencoding = 'UTF-8';
        try {
            $result = $client->call('PaymentVerification', [
                [
                    'client_id' => config('services.emta.ClientId'),
                    'client_secret' => config('services.emta.SecretId'),
                    'cellphone' => $data['phone'],
                    'verificationCode' => $data['verificationCode'],
                    'nationalCode' => $data['nationalCode'],
                    'birthDate' => $data['birthDate'],
                ],
            ]);
        } catch (\Exception $e) {
                Log::error($e->getMessage());
        }
        return true;
    }

    /**
    * check user is registered or not
    * @param Request $request
    * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
    */

    public function checkUserIsRegister( Request $request )
    {
        $client = new nusoap_client(config( 'services.emta.url' ).'/api/v1/oauth/UserIsExist');
        $client->soap_defencoding = 'UTF-8';
        try {
            $result = $client->call('PaymentVerification', [
                [
                    'client_id' => config('services.emta.ClientId'),
                    'client_secret' => config('services.emta.SecretId'),
                    'cellphone' => $request->phone,
                ],
            ]);
        } catch (\Exception $e) {
                Log::error($e->getMessage());
        }
        
        session( [ 'login_request_link' => url()->previous() ] );
        $isExists = $result['isExists'];

        if(!$isExists)
        {
            $client = new nusoap_client(config( 'services.emta.url' ).'/api/v1/user/RegisterByPartner');
            $client->soap_defencoding = 'UTF-8';
            try {
                $result = $client->call('PaymentVerification', [
                    [
                        'client_id' => config('services.emta.ClientId'),
                        'client_secret' => config('services.emta.SecretId'),
                        'cellphone' => $request->phone,
                    ],
                ]);
            } catch (\Exception $e) {
                    Log::error($e->getMessage());
            }
            
        }
        $request->merge( [
            'phone' => $request->phone_number,
        ] );
        if($result['registerState'])
        {
            return response( [ 'data'=>[ 'userIsRegistered'=>$isExists, 'phone'=>$request->phone_number ] ] );
        }
    }
}
