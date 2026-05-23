<?php

namespace App\Models;

use App\Helpers\FilterHelper;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function settingable()
    {
        return $this->morphTo();
    }
    /**
     * Summary of scopeFilter
     * @param mixed $query
     * @return void
     */
    public function scopeFilter($query)
    {
        $query = FilterHelper::getDataByCheckGhorfeExistOrNot($query , 'belongsTo' , null);
    }

}
