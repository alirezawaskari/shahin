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

}
