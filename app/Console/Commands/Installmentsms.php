<?php

namespace App\Console\Commands;

use App\Jobs\installmentLinkSmsJob;
use App\Models\SmsLinkFactorLog;
use Carbon\Carbon;
use App\Models\Factor;
use App\Models\TotalFactorItem;
use App\Models\TotalFactorItemInstallment;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Installmentsms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:installment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send sms installment link to users for installment pay';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $headers = [
            'apikey' => config('services.sms.apikey'),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $client = new Client([
            'headers' => $headers
        ]);
        //sending sms to installments that are due in 5 days
        $first_installments = TotalFactorItemInstallment::whereDate('date', Carbon::now()->addDays(5))->where('status', '0')->where('remember', 0)->take(10)->get();
        if (count($first_installments) > 0) {
            foreach ($first_installments as $installment) {
                $is_check = $installment->totalFactorItem ? self::checkIsExistInstallmentCheck($installment->totalFactorItem) : false;
                if ($installment->totalFactorItem && $installment->totalFactorItem->status_mali != 4 && $installment->totalFactorItem->status_mali != 0 && !$is_check) {
                    $factor = new Factor();
                    $factor->title = 'installment';
                    $factor->user_id = $installment->totalFactorItem->factor->user->id;
                    $factor->price = $installment->price;
                    $factor->level = $installment->level;
                    $factor->type = 'online';
                    $factor->total_factor_item_id = $installment->total_factor_item_id;
                    $factor->status = 0;
                    $factor->expired_at = now()->addDays(1)->addHours(23)->addMinutes(50);
                    $factor->save();

                    //send sms with job
                    $detail['receptor'] = $factor->user->phone;
                    $detail['template'] = 'rememberinstallment';
                    $detail['param1'] = number_format($installment->price);
                    $detail['param2'] = verta($installment->date)->format('Y/m/d');
                    $detail['resnumber'] = $factor->resnumber;
                    // installmentLinkSmsJob::dispatch( $detail );
                    $this->sendSms($detail);
                    //expiration of unpaid installment factors
                    $installment->totalFactorItem->factors()->where('id', '!=', $factor->id)->where('title', 'installment')->where('status', 0)->update(['expired_at' => now(), 'status' => 3]);

                    $installment->update([
                        'remember' => 1,
                        'factor_id' => $factor->id,
                    ]);
                } else {
                    $installment->update([
                        'remember' => 4,
                    ]);
                }
            }
        }
        //sending sms to installments that are due in 3 days
        $second_installments = TotalFactorItemInstallment::whereDate('date', Carbon::now()->addDays(3))->where('remember', 1)->where('status', '0')->take(10)->get();
        if (count($second_installments) > 0) {
            foreach ($second_installments as $installment) {
                $is_check = $installment->totalFactorItem ? self::checkIsExistInstallmentCheck($installment->totalFactorItem) : false;
                if ($installment->totalFactorItem->status_mali != 4 && $installment->totalFactorItem->status_mali != 0 && !$is_check) {
                    $factor = new Factor();
                    $factor->title = 'installment';
                    $factor->user_id = $installment->totalFactorItem->factor->user->id;
                    $factor->price = $installment->price;
                    $factor->level = $installment->level;
                    $factor->type = 'online';
                    $factor->total_factor_item_id = $installment->total_factor_item_id;
                    $factor->status = 0;
                    $factor->expired_at = now()->addDays(1)->addHours(23)->addMinutes(50);
                    $factor->save();

                    //send sms with job
                    $detail['receptor'] = $factor->user->phone;
                    $detail['template'] = 'rememberinstallment';
                    $detail['param1'] = number_format($installment->price);
                    $detail['param2'] = verta($installment->date)->format('Y/m/d');
                    $detail['resnumber'] = $factor->resnumber;
                    // installmentLinkSmsJob::dispatch( $detail );
                    $this->sendSms($detail);
                    //expiration of unpaid installment factors
                    $installment->totalFactoritem->factors()->where('id', '!=', $factor->id)->where('title', 'installment')->where('status', 0)->update(['expired_at' => now(), 'status' => 3]);

                    $installment->update([
                        'remember' => 2,
                        'factor_id' => $factor->id,
                    ]);
                }
            }
        }
        //sending sms to installments that are due in 1 days
        $third_installments = TotalFactorItemInstallment::whereDate('date', Carbon::now()->addDays(1))->where('remember', 2)->where('status', '0')->take(10)->get();
        if (count($third_installments) > 0) {
            foreach ($third_installments as $installment) {
                $is_check = $installment->totalFactorItem ? self::checkIsExistInstallmentCheck($installment->totalFactorItem) : false;
                if ($installment->totalFactorItem->status_mali != 4 && $installment->totalFactorItem->status_mali != 0 && !$is_check) {
                    $factor = new Factor();
                    $factor->title = 'installment';
                    $factor->user_id = $installment->totalFactorItem->factor->user->id;
                    $factor->price = $installment->price;
                    $factor->level = $installment->level;
                    $factor->type = 'online';
                    $factor->total_factor_item_id = $installment->total_factor_item_id;
                    $factor->status = 0;
                    $factor->expired_at = now()->addHours(23)->addMinutes(50);
                    $factor->save();

                    //send sms with job
                    $detail['receptor'] = $factor->user->phone;
                    $detail['template'] = 'rememberinstallment';
                    $detail['param1'] = number_format($installment->price);
                    $detail['param2'] = verta($installment->date)->format('Y/m/d');
                    $detail['resnumber'] = $factor->resnumber;
                    //installmentLinkSmsJob::dispatch( $detail );
                    $this->sendSms($detail);
                    //expiration of unpaid installment factors
                    $installment->totalFactoritem->factors()->where('id', '!=', $factor->id)->where('title', 'installment')->where('status', 0)->update(['expired_at' => now(), 'status' => 3]);

                    $installment->update([
                        'remember' => 3,
                        'factor_id' => $factor->id,
                    ]);
                }
            }
        }
        //sending sms to installments it is due today
        $final_installments = TotalFactorItemInstallment::whereDate('date', Carbon::now()->toDateString())->where('remember', 3)->where('status', '0')->take(10)->get();
        if (count($final_installments) > 0) {
            foreach ($final_installments as $installment) {
                $is_check = $installment->totalFactorItem ? self::checkIsExistInstallmentCheck($installment->totalFactorItem) : false;
                if ($installment->totalFactorItem->status_mali != 4 && $installment->totalFactorItem->status_mali != 0 && !$is_check) {
                    $factor = new Factor();
                    $factor->title = 'installment';
                    $factor->user_id = $installment->totalFactorItem->factor->user->id;
                    $factor->price = $installment->price;
                    $factor->level = $installment->level;
                    $factor->type = 'online';
                    $factor->total_factor_item_id = $installment->total_factor_item_id;
                    $factor->status = 0;
                    $factor->expired_at = now()->addDays(2)->addHours(23)->addMinutes(50);
                    $factor->save();

                    //send sms with job
                    $detail['receptor'] = $factor->user->phone;
                    $detail['template'] = 'finalrememberinstallment';
                    $detail['param1'] = number_format($installment->price);
                    $detail['param2'] = verta($installment->date)->format('Y/m/d');
                    $detail['resnumber'] = $factor->resnumber;
                    //installmentLinkSmsJob::dispatch( $detail );
                    $this->sendSms($detail);
                    $installment->totalFactorItem->update([
                        'status_mali' => 2,
                        'remember' => 0,
                    ]);

                    //expiration of unpaid installment factors
                    $installment->totalFactoritem->factors()->where('id', '!=', $factor->id)->where('title', 'installment')->where('status', 0)->update(['expired_at' => now(), 'status' => 3]);

                    $installment->update([
                        'remember' => 4,
                        'factor_id' => $factor->id,
                    ]);
                }
            }
        }
        //sending sms to installments that are 3 days have passed since it was due
        $expire_installments = TotalFactorItemInstallment::whereDate('date', Carbon::now()->subDays(3))->where('remember', 4)->where('status', '0')->take(10)->get();
        if (count($expire_installments) > 0) {
            foreach ($expire_installments as $installment) {
                $total_factor_item = $installment->totalFactorItem;
                $is_check = $installment->totalFactorItem ? self::checkIsExistInstallmentCheck($installment->totalFactorItem) : false;
                if ($installment->totalFactorItem && $total_factor_item->status_mali != 0 && !$is_check) {
                    $extra_pay = TotalFactorItem::getDebtExtraPay($total_factor_item);
                    $expire_installments_price = TotalFactorItem::getExpireInstallments($total_factor_item)->sum('price');

                    $factor = new Factor();
                    $factor->title = 'installment';
                    $factor->user_id = $total_factor_item->factor->user->id;
                    $factor->price = $extra_pay + $expire_installments_price;
                    $factor->level = $installment->level;
                    $factor->type = 'online';
                    $factor->total_factor_item_id = $installment->total_factor_item_id;
                    $factor->status = 0;
                    $factor->expired_at = now()->addDay(3);
                    $factor->save();

                    //send sms with job
                    $detail['receptor'] = $factor->user->phone;
                    $detail['template'] = 'expirerememberinstallment';
                    $detail['param1'] = $total_factor_item->percent_fines;
                    $detail['param2'] = number_format($factor->price);
                    $detail['resnumber'] = $factor->resnumber;
                    //installmentLinkSmsJob::dispatch( $detail );
                    $this->sendSms($detail);
                    //expiration of unpaid installment factors
                    $installment->totalFactoritem->factors()->where('id', '!=', $factor->id)->where('title', 'installment')->where('status', 0)->update(['expired_at' => now(), 'status' => 3]);

                    $installment->update([
                        'remember' => 5,
                        'factor_id' => $factor->id,
                    ]);
                }
            }
        }
        //change of status mali situation after 20 days
        $warning_installments = TotalFactorItemInstallment::whereDate('date', '<=', Carbon::now()->subDays(20))->where('status', '0')->take(100)->get();
        if (count($warning_installments) > 0) {
            foreach ($warning_installments as $installment) {
                if ($installment->totalFactorItem->status_mali != '3' && $installment->totalFactorItem->status_mali != '4' && $installment->totalFactorItem->status_mali != '0') {
                    $installment->totalFactorItem->update([
                        'status_mali' => 3,
                        'remember' => 0,
                    ]);
                }
            }
        }
        //change of status mali situation after 30 days
        $error_installments = TotalFactorItemInstallment::whereDate('date', '<=', Carbon::now()->subDays(30))->where('status', '0')->take(500)->get();
        if (count($error_installments) > 0) {
            foreach ($error_installments as $installment) {
                if ($installment->totalFactorItem->status_mali != '4' && $installment->totalFactorItem->status_mali != '0') {
                    $installment->totalFactorItem->update([
                        'status_mali' => 4,
                        'remember' => 0,
                    ]);
                }
            }
        }
        //sending installment payment sms with penalty if not already sent with consulting_contract_affairs_reference(3) status_mali
        $second_warning_total_factor_items = TotalFactorItem::where('status_mali', '3')->where('remember', '!=', 1)->take(100)->get();
        if (count($second_warning_total_factor_items) > 0) {
            foreach ($second_warning_total_factor_items as $total_factor_item) {
                $extra_pay = TotalFactorItem::getDebtExtraPay($total_factor_item);
                $expire_installments_price = TotalFactorItem::getExpireInstallments($total_factor_item)->sum('price');

                $factor = new Factor();
                $factor->title = 'installment';
                $factor->user_id = $total_factor_item->factor->user->id;
                $factor->price = $extra_pay + $expire_installments_price;
                $factor->type = 'online';
                $factor->total_factor_item_id = $total_factor_item->id;
                $factor->status = 0;
                $factor->expired_at = now()->addDay(1);
                $factor->save();

                //send sms with job
                $detail['receptor'] = $factor->user->phone;
                $detail['template'] = 'secondwarningfactoritems';
                $detail['param1'] = $total_factor_item->percent_fines;
                $detail['param2'] = number_format($factor->price);
                $detail['resnumber'] = $factor->resnumber;
                //installmentLinkSmsJob::dispatch( $detail );
                $this->sendSms($detail);
                //expiration of unpaid installment factors
                $total_factor_item->factors()->where('id', '!=', $factor->id)->where('title', 'installment')->where('status', 0)->update(['expired_at' => now(), 'status' => 3]);

                $total_factor_item->update(['remember' => 1]);
            }
        }
        //sending installment payment sms with penalty if not already sent with legal_reference(4) status_mali
        $legal_reference_total_factor_items = TotalFactorItem::where('status_mali', '4')->where('remember', '!=', 1)->take(100)->get();
        if (count($legal_reference_total_factor_items) > 0) {
            foreach ($legal_reference_total_factor_items as $total_factor_item) {
                $extra_pay = TotalFactorItem::getDebtExtraPay($total_factor_item);
                $expire_installments_price = TotalFactorItem::getExpireInstallments($total_factor_item)->sum('price');

                $factor = new Factor();
                $factor->title = 'installment';
                $factor->user_id = $total_factor_item->factor->user->id;
                $factor->price = $extra_pay + $expire_installments_price;
                $factor->type = 'online';
                $factor->total_factor_item_id = $total_factor_item->id;
                $factor->status = 0;
                $factor->expired_at = now()->addDay(1);
                $factor->save();

                //send sms with job
                $detail['receptor'] = $factor->user->phone;
                $detail['template'] = 'legalreferencefactoritem';
                $detail['param1'] = number_format($factor->price);
                $detail['param2'] = number_format($factor->price);
                $detail['resnumber'] = $factor->resnumber;
                //installmentLinkSmsJob::dispatch($detail);
                $this->sendSms($detail);
                //expiration of unpaid installment factors
                $total_factor_item->factors()->where('id', '!=', $factor->id)->where('title', 'installment')->where('status', 0)->update(['expired_at' => now(), 'status' => 3]);

                $total_factor_item->update(['remember' => 1]);
            }
        }
    }

    private function sendSms($detail)
    {
        $already_send_sms = $this->checkAlreadySendSmsLog($detail['receptor'], 'installmentSms');
        if (!$already_send_sms) {
            $log = new SmsLinkFactorLog();
            $log->phone = $detail['receptor'];
            $log->type = 1;
            $log->title = 'installmentSms';
            $log->save();

            $headers = [
                'apikey' => config('services.sms.apikey'),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ];
            $client = new Client([
                'headers' => $headers
            ]);

            $body = [
                'receptor' => $detail['receptor'],
                'type' => 1,
                'template' => $detail['template'],
                'param1' => $detail['param1'],
                'param2' => $detail['param2'],
                'param3' => config('app.url') . '/pf/' . $detail['resnumber'],
            ];

            try {
                $client->request('POST', 'http://api.parsasms.com/v2/send/verify', [
                    'form_params' => $body
                ]);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }

        }
    }
    /**
     * @param \App\Models\TotalFactorItem $totalFactorItem
     * @return bool
     */
    private function checkIsExistInstallmentCheck(TotalFactorItem $totalFactorItem)
    {
        return $totalFactorItem->checks()->where('title' , 'installment')->exists();
    }
    /**
     * @param mixed $phone
     * @param mixed $title
     * @return mixed
     */
    private function checkAlreadySendSmsLog($phone, $title)
    {
        return SmsLinkFactorLog::where('phone', $phone)->where('title', $title)->whereDate('created_at', Carbon::today())->exists();
    }
}
