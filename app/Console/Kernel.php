<?php

namespace App\Console;

use App\Models\PayoutSchedule;
use App\Models\WalletTransaction;
use App\Services\SamanTashimiClient;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // Scheduled bulk SMS campaigns: required in production because the default sync queue
        // ignores job delays (needs the system cron "* * * * * php artisan schedule:run").
        $schedule->command('sms:dispatch-due-campaigns')->everyMinute()->withoutOverlapping();

        $schedule->call(function () {
            $schedules = PayoutSchedule::whereNull('deleted_at')
                ->where('next_run_at', '<=', now())
                ->where(function($q) {
                    $q->where('last_status', 'pending')
                        ->orWhere('last_status', 'failed')->where('retry_count', '<', config('payout.max_retries', 3));
                })
                ->get();

            foreach ($schedules as $scheduleItem) {

                $user = $scheduleItem->user;
                $walletBalance = $user->wallet;

                if ($walletBalance < $scheduleItem->min_threshold) {
                    $scheduleItem->scheduleNextRun();
                    continue;
                }

                try {
                    $amountToPay = $walletBalance;
                    $shaba = $user->shaba;

                    // $response = SamanTashimi::pay([
                    //     'amount' => $amountToPay,
                    //     'shaba'  => $shaba,
                    //     'description' => 'تسویه درآمد ' . now()->toDateString(),
                    // ]);

                    $apiClient = new SamanTashimiClient();
                    $response = $apiClient->transferToShaba($shaba, $amountToPay);

                    if ($response->isSuccessful()) {

                        WalletTransaction::create([
                            'user_id'   => $user->id,
                            'reason_id' => null,
                            'resnumber' => $response->transactionId(),
                            'price'     => -1 * $amountToPay,  // منفی: چون خروج از کیف‌پول
                            'status'    => '1',
                            'type'      => WalletTransaction::TYPE['2'],
                            'created_at'=> now(),
                        ]);

                        $user->wallet = 0;
                        $user->save();

                        $scheduleItem->markSuccess();
                        $scheduleItem->scheduleNextRun();
                    } else {

                        $errorMsg = $response->errorMessage();
                        $scheduleItem->markFailed($errorMsg);
                    }

                } catch (Exception $e) {

                    $scheduleItem->markFailed($e->getMessage());
                }
            }

        })->dailyAt('02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
