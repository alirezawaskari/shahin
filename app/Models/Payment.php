<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function factoritem()
    {
        return $this->belongsTo(FactorItem::class , 'factor_item_id');
    }

    /**
     *
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->resnumber = self::makeUniqueTracking();
        });
    }

    /**
     * @return string
     */
    private static function makeUniqueTracking()
    {
        do {
            $resNumber = rand(12, 99) . substr(Carbon::now()->timestamp, 6, 11) . substr(auth()->user()->phone, 7, 11);
            $found = self::where('resnumber', $resNumber)->first();
        } while (!is_null($found));
        return $resNumber;
    }

    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'resnumber';
    }
}
