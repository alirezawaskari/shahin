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
    public function ghorfeOnline()
    {
        return $this->belongsTo(GhorfeOnlineList::class , 'ghorfe_online_list_id');
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
