<?php

namespace App\Http\Controllers;

use App\Events\sendlinkFactor;
use App\Lib\zarinpal;
use App\Lib\saman;
use App\Models\Employee;
use App\Models\Factor;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use nusoap_client;

class PaymentController extends Controller
{
    public function pay(Request $request)
    {
        $gateway = 'zarinpal';
        $factor = Factor::findOrFail($request->input('factor_id'));
        $user = $factor->user;
        if ($request->input('payment') == 'card') {
            event(new sendlinkFactor($user, $factor, $user->family, 'card'));
            exit('شماره کارت جهت واریز به موبایل شما ارسال گردید');
        }
        if ($request->input('payment') == 'online') {
            session()->put('resnumber', $factor->resnumber);
            if ($gateway == 'zarinpal') {
                $amount = $factor->price / 10;
                $order = new zarinpal();
                $res = $order->pay($amount);
                $factor->factorable_type = $res;
                $factor->save();
                return redirect(config('services.zarinpal.url') . '/pg/StartPay/' . $res);
            }
            if ($gateway == 'saman') {
                $amount = $factor->price;
                $tokenResult = $this->doPayment($amount);
                if (is_array($tokenResult) && isset($tokenResult['errorCode'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'خطا در دریافت توکن سامان',
                        'gateway' => 'saman',
                        'errorCode' => $tokenResult['errorCode'],
                        'errorDesc' => $tokenResult['errorDesc'],
                        "status" => 200
                    ], 200);
                }
                $factor->factorable_type = $tokenResult;
                $factor->save();
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

    private function calculateIBANShares(float $totalAmount): array
    {
        $employees = Employee::where('percent', '>', 0)
            ->whereNotNull('shaba_number')
            ->get();
        $ibanShares = [];
        $totalShared = 0;

        foreach ($employees as $emp) {
            $shareAmount = intval($totalAmount * $emp->percent / 100);

            if ($shareAmount > 0) {
                $ibanShares[] = [
                    'IBAN' => $emp->shaba_number,
                    'Amount' => $shareAmount,
                    'PurchaseID' => str_pad($emp->id, 30, '0', STR_PAD_LEFT),
                ];
                $totalShared += $shareAmount;
            }
        }
        $mainIBN = Setting::firstorfail()->shaba_number;
        $remainingAmount = $totalAmount - $totalShared;
        if ($remainingAmount > 0) {
            if ($mainIBN && $mainIBN->value) {
                $ibanShares[] = [
                    'IBAN' => $mainIBN->value,
                    'Amount' => $remainingAmount,
                    'PurchaseID' => str_pad($mainIBN->id, 30, '0', STR_PAD_LEFT),
                ];
            }
        }
        return $ibanShares;
    }

    private function doPayment(float $amount)
    {
        $ibanShares = $this->calculateIBANShares($amount);
        $gateway = new Saman();
        $tokenResult = $gateway->token($amount, $ibanShares);

        return $tokenResult;
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
        $Authority = $request->get('Authority');
        $factor = Factor::where('factorable_type', $Authority)->firstOrFail();
        if ($request->get('Status') == 'OK') {
            $client = new nusoap_client(config('services.zarinpal.url') . '/pg/services/WebGate/wsdl', 'wsdl');
            $client->soap_defencoding = 'UTF-8';
            //در خط زیر یک درخواست به زرین پال ارسال می کنیم تا از صحت پرداخت کاربر مطمئن شویم
            $result = $client->call('PaymentVerification', [
                [
                    //این مقادیر را به سایت زرین پال برای دریافت تاییدیه نهایی ارسال می کنیم
                    'MerchantID' => config('services.zarinpal.id'),
                    'Authority' => $Authority,
                    'Amount' => $factor->price / 10,
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
                return 'عضو محترم شبکه لاله ؛ در حال حاضر اتصال به سامانه امکانپذیر نمیباشد، نگران نباشید در اسرع وقت این مشکل برطرف میگردد، در صورت عدم اتصال به سایت لطفا با شماره پشتیبانی لاله تماس حاصل نمائید
۰۹۳۶۶۳۴۲۱۴۵
اداره کل فناوری و‌اطلاعات سجا
شبکه خدمات درمانی و‌رفاهی لاله';
            }
        }
        return redirect(route('factor', [$factor->resnumber]));

    }

    protected function handleSamanCallback(Request $request)
    {
        try {
            $token = $request->get('token', $request->get('Token'));
            $status = $request->get('status', $request->get('Status'));

            $factor = Factor::where('factorable_type', $token)->first();

            if ($status !== 'OK') {
                return 'عضو محترم شبکه لاله ؛ در حال حاضر اتصال به سامانه امکانپذیر نمیباشد، نگران نباشید در اسرع وقت این مشکل برطرف میگردد، در صورت عدم اتصال به سایت لطفا با شماره پشتیبانی لاله تماس حاصل نمائید
۰۹۳۶۶۳۴۲۱۴۵
اداره کل فناوری و‌اطلاعات سجا
شبکه خدمات درمانی و‌رفاهی لاله';
            }
            $response = \Illuminate\Support\Facades\Http::post('https://sep.shaparak.ir/onlinepg/onlinepg', [
                'action' => 'verify',
                'TerminalId' => config('services.saman.id'),
                'Token' => $token,
            ]);
            if ($response->ok() && $response->json('status') == 1) {
                $factor->update([
                    'status' => true,
                    'type' => 'online',
                    'confirm' => true,
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
        } catch (\Exception $e) {
            Log::error('خطا در پردازش پرداخت سامان: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('factor', [$factor->resnumber])
                ->with('error', 'مشکلی در پردازش پرداخت پیش آمده. لطفاً دوباره تلاش کنید یا با پشتیبانی تماس بگیرید.');
        }

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



