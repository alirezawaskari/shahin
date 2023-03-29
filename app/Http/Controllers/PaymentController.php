<?php

namespace App\Http\Controllers;

use App\Events\successFactor;
use App\Events\sendlinkFactor;
use App\Models\Factor;
use App\Models\FactorItem;
use App\Models\Payment;
use App\FactorItemInstallment;
use App\Lib\zarinpal;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use nusoap_client;

class PaymentController extends Controller
{
    public function pay(Request $request)
    {
        $factor = Factor::findOrFail($request->input('factor_id'));
        $user = $factor->user;
        // event(new sendlinkFactor($user, $factor, $user->family, 'card'));
        //  exit('شماره کارت جهت واریز به موبایل شما ارسال گردید' );
       if ($request->input('payment') == 'online') {
           session()->put('resnumber', $factor->resnumber);
           $amount = $factor->price/10;
           if (!is_null($factor->coupon_id)) {
               $amount = $amount - ($amount * $factor->coupon->discount / 100);
           }

           $order = new zarinpal();
           $res = $order->pay($amount);
           $factor->factorable_type = $res;
           $factor->save();

           return redirect('https://www.zarinpal.com/pg/StartPay/' . $res);
       } else {
           if ($factor->user->wallet >= self::discount($factor)) {
               $factor->update([
                   'status' => true,
                   'type' => 'wallet',
                   'confirm' => true,
               ]);

               Factor::savePayment($factor);

               $user->update([
                   'wallet' => $user->wallet - self::discount($factor),
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

    public function callback(Request $request)
    {
        $Authority = $request->get('Authority') ;
        $factor = Factor::where('factorable_type', $Authority)->firstOrFail();
        if ($request->get('Status') == 'OK') {
            $client = new nusoap_client('https://www.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl');
            $client->soap_defencoding = 'UTF-8';
            //در خط زیر یک درخواست به زرین پال ارسال می کنیم تا از صحت پرداخت کاربر مطمئن شویم
            $result = $client->call('PaymentVerification', [
                [
                    //این مقادیر را به سایت زرین پال برای دریافت تاییدیه نهایی ارسال می کنیم
                    'MerchantID'     => config( 'services.zarinpal.id' ),
                    'Authority'      => $Authority,
                    'Amount'         => $factor->price/10,
                ],
            ]);

            if ($result['Status'] == 100) {
                $factor->update([
                    'status' => true,
                    'type' => 'online',
                    'confirm' => true,
                    'refnumber' => $result['RefID'],
                ]);

                Factor::savePayment($factor);
                Factor::invoiceRevenueSharing($factor);

                self::sendSMS($factor);

                return redirect(route('checkout', [$factor->resnumber]))->with([
                    'payment' => 'success',
                ]);
            } else {
                return 'خطایی رخ داد، درصورت کم شدن ، مبلغ تا 72 ساعت به حسابتان باز میگردد.';
            }
        }
        return redirect(route('factor', [$factor->resnumber]));
    }
    public function fastpay(Factor $factor)
    {
        if ($factor && $factor->status != 1) {
            //$factor->title == 'register' ? event(new sendlinkFactor($factor->user, $factor, $factor->user->family, 'card')) : '';
            return redirect(route('factor', [$factor->resnumber]));
        } else {
            return redirect(route('checkout', [$factor->resnumber]));
        }
    }

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
    private static function setGiftStatus($factor, $payment)
    {
        /*   foreach ($factor->gifts as $gift) {
			   $gift->update([
				   'status' => true,
				   'type' => $payment,
			   ]);
		   }*/
    }
    private static function discount($factor)
    {
        $amount = $factor->price;
        if (!is_null($factor->coupon_id)) {
            $amount = $amount - ($amount * $factor->coupon->discount / 100);
        }
        return $amount;
    }

}



