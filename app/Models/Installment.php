<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class  Installment extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function installmentable()
    {
        return $this->morphTo();
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\MorphTo|object|null
     */
    public function service()
    {
        return $this->morphTo()->where('installmentable_type',Service::class)->first();
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\MorphTo|object|null
     */
    public function category()
    {
        return $this->morphTo()->where('installmentable_type',Category::class)->first();
    }
    /**
     * @param mixed $total_price
     * @param mixed $installment_able //service or category
     * @return int
     */
    public static function getInstallmentNum($total_price, $installment_able)
    {
        //calculate the number of installments from the denstriy service table
        $installment_able = Service::find(1);
        $installments = $installment_able->installments;
        foreach ($installments as $installment) {
            if ($installment->down_interval <= $total_price && $total_price < $installment->high_interval) {
                return $installment->num;
            }
        }
        return $installments->max('high_interval') <= $total_price ? $installments->max('num') : 1;
    }
}
