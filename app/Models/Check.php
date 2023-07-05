<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Check extends Model implements AuditableContract
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $guarded=['id'];

    /**
     *
     */
    const TITLE = [
        'installment' => 'قسط',
        'guaranty' => 'ضمانت',
    ];

    /**
     * statuses of check
     * @param $status
     * @return string
     */
    public static function status($status)
    {
        return [
            '0' => 'در انتظار وصول',
            '1' => 'وصول شده',
            '2' => 'برگشت خورده',
            '3' => 'عودت داده شده',
        ][$status];
    }

    /**
     * Get all of the check's files.
     */
    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function histories()
    {
        return $this->morphMany(History::class, 'historiable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function checkable()
    {
        return $this->morphTo();
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $exporter=request()->exporter;
        $pay_to=request()->pay_to;
        $start_date=request()->start_date;
        $end_date=request()->end_date;
        $title=request()->title;
        $phone = request('phone');
        $status=request()->status;
        $serial_code=request()->serial_code;

        if (isset($exporter) && trim($exporter) != '') {
                $query->where('exporter', 'LIKE', '%' . $exporter. '%');
        }
        if (isset($pay_to) && trim($pay_to) != '') {
                $query->where('pay_to', 'LIKE', '%' . $pay_to. '%');
        }
        if (isset($serial_code) && trim($serial_code) != '') {
                $query->where('serial_code', 'LIKE', '%' . $serial_code. '%');
        }

        if (isset($start_date) && trim($start_date) != '' && isset($end_date) && trim($end_date) != '') {
            $query->whereDate('date', '>=', $start_date)->whereDate('created_at', '<=', $end_date);
        }
        if (isset($status) && trim($status) != '' && $status != 'all') {
            $query->whereStatus($status);
        }
        if (isset($title) && trim($title) != '' && $title != 'all') {
            $query->whereTitle($title);
        }
        if (isset($phone) && trim($phone) != '') {
            $query->whereHas('totalFactorItem', function ($query) use ($phone) {
                $query->whereHas('factor',  function ($query) use ($phone){
                    $query->whereHas('user', function ($query) use ($phone) {
                        $query->where('phone', $phone);
                    });
                });
            });
        }
        return $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function totalFactorItem()
    {
        return $this->belongsTo(TotalFactorItem::class,'checkable_id')->where('checks.checkable_type',TotalFactorItem::class);
    }
}
