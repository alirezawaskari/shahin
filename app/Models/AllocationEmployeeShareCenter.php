<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AllocationEmployeeShareCenter extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function serviceTariff()
    {
        return $this->belongsTo(Service::class);
    }
     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent()
    {
        return $this->belongsTo(User::class , 'agent_id');
    }
       /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $title = request('title');

        if (isset($title) && trim($title) != '') {
            $query->where('title','like', '%'.$title.'%');
        }
        return $query;
    }
}
