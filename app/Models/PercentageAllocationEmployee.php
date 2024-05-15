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
    public static function setUserPerformance( FactorItem $factor_item, $type, $price ) {
        $description = self::TYPE[$type];
        if ( $type == 'register' ) {
            $percentage_allocation_employees = $factor_item->factoritemable()->percentageAllocationEmployees()->where( 'type', 'register' )->where( 'start_allocation_date', '<=', now() )->where( 'end_allocation_date', '>=', now() );
        }
        else
        {
            $percentage_allocation_employees = $factor_item->center()->percentageAllocationEmployees()->where( 'type', 'register' )->where( 'start_allocation_date', '<=', now() )->where( 'end_allocation_date', '>=', now() );
        }
        foreach ( $percentage_allocation_employees as $item ) {
            UserPerformance::store( $item, $description, $price, $factor_item );
        }
    }
}
