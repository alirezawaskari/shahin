<?php

namespace App\Http\Controllers;

use App\Lib\zarinpal;
use App\Lib\saman;
use App\Models\Factor;
use Illuminate\Http\Request;
use nusoap_client;

class PaymentController extends Controller
{
    /** 
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function pay(Request $request)
    {
        $gateway = 'saman';
        $factor = Factor::findOrFail($request->input('factor_id'));
        $user = $factor->user;
        // event(new sendlinkFactor($user, $factor, $user->family, 'card'));
        //  exit('شماره کارت جهت واریز به موبایل شما ارسال گردید' );
       if ($request->input('payment') == 'online') {
           session()->put('resnumber', $factor->resnumber);
           if ($gateway == 'zarinpal'){
            $amount = $factor->price/10;
            $order = new zarinpal();
           $res = $order->pay($amount);
           $factor->factorable_type = $res;
           $factor->save();

           return redirect(config( 'services.zarinpal.url' ).'/pg/StartPay/' . $res);
                }
                if ($gateway == 'saman') {
                    $amount = $factor->price;
                    $gateway = new saman();
                    $tokenResult = $gateway->token($amount);

                    if (is_array($tokenResult) && isset($tokenResult['errorCode'])) {
                        return response()->json([
                            'success'      => false,
                            'message'             => 'خطا در دریافت توکن سامان',
                            'gateway'     => 'saman',
                            'errorCode'   => $tokenResult['errorCode'],
                            'errorDesc'   => $tokenResult['errorDesc'],
                            "status" => 200
                        ], 200);
                    }

                    return redirect('https://sep.shaparak.ir/OnlinePG/SendToken?token=' . $tokenResult);
                }
       } else {
           if ($factor->user->wallet >= $factor->price) {
               $factor->update([
                   'status' => true,
                   'type' => 'wallet',
                   'confirm' => true,
               ]);

               Factor::savePayment($factor);

               $user->update([
                   'wallet' => $user->wallet - $factor->price,
               ]);

               self::sendSMS($factor);

               return redirect(route('checkout', [$factor->resnumber]))->with([
                   'payment' => 'success',
               ]);
           } else {
               abort(404);
           }
       }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|string
     */
    public function callback(Request $request)
    {
        if ($request->has('Authority')) {
            return $this->handleZarinpalCallback($request);
        }
        return $this->handleSamanCallback($request);
    }
    protected function handleZarinpalCallback(Request $request)
    {
        $authority = $request->get('Authority');
        $status    = $request->get('Status');

        $factor = Factor::where('factorable_type', $authority)->firstOrFail();

        if ($status === 'OK') {
            $client = new nusoap_client(
                'https://www.zarinpal.com/pg/services/WebGate/wsdl',
                ['encoding' => 'UTF-8']
            );
            $result = $client->call('PaymentVerification', [[
                'MerchantID' => config('services.zarinpal.id'),
                'Authority'  => $authority,
                'Amount'     => $factor->price / 10,
            ]]);

            if (!empty($result['Status']) && $result['Status'] == 100) {
                $factor->update([
                    'status'    => true,
                    'type'      => 'online',
                    'confirm'   => true,
                    'refnumber' => $result['RefID'],
                ]);
                Factor::savePayment($factor);
                Factor::invoiceRevenueSharing($factor);
                self::sendSMS($factor);

                return redirect(route('checkout', [$factor->resnumber]))->with([
                    'payment' => 'success',
                ]);
            }

return 'عضو محترم شبکه لاله ؛ در حال حاضر اتصال به سامانه امکانپذیر نمیباشد، نگران نباشید در اسرع وقت این مشکل برطرف میگردد، در صورت عدم اتصال به سایت لطفا با شماره پشتیبانی لاله تماس حاصل نمائید
۰۹۳۶۶۳۴۲۱۴۵
اداره کل فناوری و‌اطلاعات سجا
شبکه خدمات درمانی و‌رفاهی لاله';
        }
        return redirect(route('factor', [$factor->resnumber]));
    }
    protected function handleSamanCallback(Request $request){

        $token  = $request->get('token', $request->get('Token'));
        $status = $request->get('status', $request->get('Status'));

        $factor = Factor::where('factorable_type', $token)->firstOrFail();

        if ($status !== 'OK') {
return 'عضو محترم شبکه لاله ؛ در حال حاضر اتصال به سامانه امکانپذیر نمیباشد، نگران نباشید در اسرع وقت این مشکل برطرف میگردد، در صورت عدم اتصال به سایت لطفا با شماره پشتیبانی لاله تماس حاصل نمائید
۰۹۳۶۶۳۴۲۱۴۵
اداره کل فناوری و‌اطلاعات سجا
شبکه خدمات درمانی و‌رفاهی لاله';
        }

        $response = \Illuminate\Support\Facades\Http::post('https://sep.shaparak.ir/onlinepg/onlinepg', [
            'action'      => 'verify',
            'TerminalId'  => config('services.saman.id'),
            'Token'       => $token,
        ]);

        if ($response->ok() && $response->json('status') == 1) {
            $factor->update([
                'status'    => true,
                'type'      => 'online',
                'confirm'   => true,
                'refnumber' => $response->json('refNum'),
            ]);
            Factor::savePayment($factor);
            Factor::invoiceRevenueSharing($factor);

            self::sendSMS($factor);

                return redirect(route('checkout', [$factor->resnumber]))->with([
                    'payment' => 'success',
                ]);
            }

        return redirect(route('factor', [$factor->resnumber]));
    }

    /**
     * @param Factor $factor
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function fastpay(Factor $factor)
    {
        if ($factor && $factor->status != 1) {
            //$factor->title == 'register' ? event(new sendlinkFactor($factor->user, $factor, $factor->user->family, 'card')) : '';
            return redirect(route('factor', [$factor->resnumber]));
        } else {
            return redirect(route('checkout', [$factor->resnumber]));
        }
    }

    /**
     * @param $factor
     */
    private static function sendSMS($factor)
    {
        //event(new successFactor($factor->user, $factor->resnumber));
        /*  if ($factor->service->sms) {
			  event(new smsService($factor->user, $factor->service, $factor));
		  }
		  foreach ($factor->gifts as $gift) {
			  if ($gift->service->sms) {
				  event(new smsService($gift->user, $gift->service, $gift));
			  }
		  }*/
    }

    /**
     * @param $factor
     * @param $payment
     */
    private static function setGiftStatus($factor, $payment)
    {
        /*   foreach ($factor->gifts as $gift) {
			   $gift->update([
				   'status' => true,
				   'type' => $payment,
			   ]);
		   }*/
    }
}



