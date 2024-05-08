<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PercentageAllocationEmployee extends Model
{
    use SoftDeletes;

     /**
     * @var string[]
     */
    protected $guarded=['id'];
    /**
     *types of PercentageAllocationEmployee
     */
    const TYPE = [
        'register_price' => 'هزینه ثبت نام',
        'total_price' => 'جمع کل فاکتور',
        'company_profit' => 'سود شرکت',
    ];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function percentageAllocationEmployeeable()
    {
        return $this->morphTo();
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent()
    {
        return $this->belongsTo(User::class , 'agent_id');
    }
      /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
     {
          $type = request('type');
          $user = request('user');

          if(isset($user) && $user != 'all')
          {
             $query->where('user_id', $user);
          }
          if(isset($type) && $type != 'all')
          {
            $query->where('type', 'LIKE', '%' . $type . '%');
          }
          return $query;
     }
}
