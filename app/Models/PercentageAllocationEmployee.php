<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PercentageAllocationEmployee extends Model {
    use SoftDeletes;

    /**
    * @var string[]
    */
    protected $guarded = [ 'id' ];
    /**
    *types of PercentageAllocationEmployee
    */
    const TYPE = [
        'register_price' => 'برداشت از محل هزینه ثبت نام',
        'all_total_price' => 'برداشت از محل جمع کل فاکتور',
        'income_total_price' => 'برداشت از محل درآمد جمع کل فاکتور',
        'company_profit' => 'برداشت از محل کارمزد تسهیلات',
    ];
    /**
    *FactorSender type of PercentageAllocationEmployee
    */
    const FactorSender = [
        'all' => 'همه',
        'employee_factors' => 'مرتبط با همکار',
    ];
    /**
    * @return \Illuminate\Database\Eloquent\Relations\MorphTo
    */

    public function percentageAllocationEmployeeable() {
        return $this->morphTo();
    }
    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */

    public function user() {
        return $this->belongsTo( User::class );
    }
    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function agent() {
        return $this->belongsTo( User::class, 'agent_id' );
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function userPerformances() {
        return $this->belongsToMany( UserPerformance::class );
    }
    /**
    * @param $query
    * @return mixed
    */

    public function scopeFilter( $query ) {
        $type = request( 'type' );
        $user = request( 'user' );

        if ( isset( $user ) && $user != 'all' ) {
            $query->where( 'user_id', $user );
        }
        if ( isset( $type ) && $type != 'all' ) {
            $query->where( 'type', 'LIKE', '%' . $type . '%' );
        }
        return $query;
    }
    /**
    * @param \App\Models\FactorItem $factor_item
    * @param mixed $type
    * @return void
    */
    public static function setUserPerformance( FactorItem $factor_item, $type, $price , $date , $factor_item_service_tariff) {
        $description = self::TYPE[$type];
        $percentage_allocation_employees = [];
        if ( $type == 'register_price' ) {
            $percentage_allocation_employees = $factor_item->factoritemable->percentageAllocationEmployees->where( 'type', $type )->where( 'start_allocation_date', '<=', now() )->where( 'end_allocation_date', '>=', now() );
        }
        elseif($factor_item->center)
        {
            $percentage_allocation_employees = $factor_item->center->percentageAllocationEmployees->where( 'type', $type )->where( 'start_allocation_date', '<=', now() )->where( 'end_allocation_date', '>=', now() );
        }
        foreach ( $percentage_allocation_employees as $item ) {
            $is_applicable = true;
            if($item->factor_sender == 'employee_factors')
            {
                $is_applicable = self::checkPercentageAllocationEmployeeConditions($item , $factor_item);
            }
            $user_performance_price = $price * ( $item->percent / 100 );
            $is_applicable ? ($user_performance = UserPerformance::store( $item, $item->user , $description, $price, $user_performance_price, $factor_item , $date)): '';
            if($is_applicable && $type == 'all_total_price')
            {
                $user_performance_sessions_done = $factor_item_service_tariff->sessions->where( 'status', 1 );
                //set user performance sessions done
                UserPerformance::setSessionsDone($user_performance , $user_performance_sessions_done);
            }
        }

    }
    /**
     * @param mixed $item
     * @param mixed $factor_item
     * @return bool
     */
    private static function checkPercentageAllocationEmployeeConditions($item , $factor_item)
    {
        if($item->user_id == $factor_item->factor->callcenter_id)
        {
            $is_applicable = true;
            if($item->limit_type == 'count')
            {
                $employee_sucess_factors_count = $item->user->factors->where('status',1)->where('created_at' , '>=' , $item->start_allocation_date)->count();
                $is_applicable = $employee_sucess_factors_count >= $item->min_limit_type ? true : false;
                $is_applicable = $is_applicable && $employee_sucess_factors_count <= $item->max_limit_type ? true : false;
            }
            if($item->limit_type == 'price')
            {
                $employee_sucess_factors_sum_price = $item->user->factors->where('status',1)->where('created_at' , '>=' , $item->start_allocation_date)->sum('price');
                $is_applicable = $employee_sucess_factors_sum_price >= $item->min_limit_type ? true : false;
                $is_applicable = $is_applicable && $employee_sucess_factors_sum_price <= $item->max_limit_type ? true : false;
            }
            return $is_applicable;
        }

        return false;
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'user_id');
    }
    public function parent()
    {
        return $this->belongsTo(PercentageAllocationEmployee::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PercentageAllocationEmployee::class, 'parent_id');
    }
}
