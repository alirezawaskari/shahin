<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class About extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable=['ghorfe_online_list','image_two','image_three','image_four','image_five','content','content2',
        'content_one','content_two','content_three','content_four','content_five'];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ghorfeOnline()
    {
        return $this->belongsTo(GhorfeOnlineList::class , 'ghorfe_online_list_id');
    }
      /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $query->where('ghorfe_online_list_id' , null);

        return $query;
    }
}
