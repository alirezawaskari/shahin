<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayoutSchedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'frequency',
        'min_threshold',
        'next_run_at',
        'last_status',
        'retry_count',
        'last_error',
    ];

    protected $dates = ['next_run_at', 'deleted_at', 'created_at', 'updated_at'];

    /**
     * کاربرِ مربوطه
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * بررسی می‌کند که آیا اکنون زمان اجراست یا خیر
     */
    public function isDue(): bool
    {
        return $this->next_run_at && $this->next_run_at->lte(now());
    }

    /**
     * پس از پرداخت موفق، تعیین می‌کند تاریخ اجرای بعدی کی باشد
     */
    public function scheduleNextRun()
    {
        if ($this->frequency === 'daily') {
            // هر روز +1
            $this->next_run_at = Carbon::parse($this->next_run_at)->addDay();
        } else {
            // ماهانه: فرض کنید همان روز ماه بعد
            $this->next_run_at = Carbon::parse($this->next_run_at)->addMonth();
        }
        $this->last_status = 'pending';
        $this->retry_count = 0;
        $this->last_error = null;
        $this->save();
    }

    /**
     * اگر خطایی در فرآیند پرداخت باشد:
     * - تعداد تلاش را افزایش می‌دهد
     * - اگر تعداد تلاش از حد مجاز بیشتر شد، last_status را به 'failed' تغییر می‌دهد
     */
    public function markFailed(string $errorMessage)
    {
        $this->retry_count++;
        $this->last_error = $errorMessage;
        if ($this->retry_count >= config('payout.max_retries', 3)) {
            $this->last_status = 'failed';
        }
        $this->save();
    }

    /**
     * پس از پرداخت موفق، وضعیت را به success تغییر می‌دهد
     */
    public function markSuccess()
    {
        $this->last_status = 'success';
        $this->last_error = null;
        $this->retry_count = 0;
        $this->save();
    }
}
