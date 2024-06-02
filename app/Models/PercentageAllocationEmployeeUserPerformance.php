<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PercentageAllocationEmployeeUserPerformance extends Model {
    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function userPerformance() {
        return $this->belongsTo( UserPerformance::class );
    }
    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function percentageAllocationEmployee() {
        return $this->belongsTo( PercentageAllocationEmployee::class );
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
}
