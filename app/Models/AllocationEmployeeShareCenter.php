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
    /**
    * @param \App\Models\FactorItem $factor_item
    * @param mixed $type
    * @return void
    */
    public static function setUserPerformance( FactorItem $factor_item, $factor_item_service_tariff, $price , $agent , $date) {
        $description = $factor_item_service_tariff->serviceTariff->name.' واریز از محل تخصیص سهم از تعرفه';
        $user_performance_sessions_done = $factor_item_service_tariff->sessions->where( 'status', 1 );
        $allocation_employee_share_centers = $factor_item_service_tariff->serviceTariff->allocationEmployeeShareCenters;
        foreach ( $allocation_employee_share_centers as $item ) {
            $user_role = $item->user->roles->first();
            $user_role->name == 'doctor' ? ($user_performance_sessions_done = $user_performance_sessions_done->where( 'agent_id', $agent->id)) : '';
            if(($user_role->name == 'doctor' && $item->user_id == $agent->id) || $user_role->name != 'doctor')
            {
                $user_performance = UserPerformance::store( null , $item->user , $description, $price, $item->amount, $factor_item , $date);
                //set user performance sessions done
                UserPerformance::setSessionsDone($user_performance , $user_performance_sessions_done);
            }
        }
    }
    /**
     * @param mixed $inputs
     * @param mixed $service_tariff
     * @return void
     */
    public static function store($inputs , $service_tariff)
    {
        if ( auth()->user()->hasPermission( 'allocationEmployeeShareCenter_create' ) && isset( $inputs[ 'price' ] ) ) {
            for ( $i = 0 ; $i < count( $inputs[ 'employee' ] ) ;
            $i++ ) {
                if ( $inputs[ 'price' ][ $i ] ) {
                    AllocationEmployeeShareCenter::create( [
                        'user_id' => $inputs[ 'employee' ][ $i ],
                        'agent_id' => auth()->user()->id,
                        'service_tariff_id' => $service_tariff->id,
                        'amount' => ( float ) str_replace( ',', '', $inputs[ 'price' ][ $i ] ),
                    ] );
                }
            }
        }
    }
}
