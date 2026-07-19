<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TotalFactorItemContractSettlement extends Model
{
    /**
     * @var array
     */

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function totalFactorItem()
    {
        return $this->belongsTo(TotalFactorItem::class , 'item_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function applicant()
    {
        return $this->belongsTo(User::class , 'applicant_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mali()
    {
        return $this->belongsTo(User::class , 'mali_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function superadmin()
    {
        return $this->belongsTo(User::class , 'superadmin_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deliverer()
    {
        return $this->belongsTo(User::class , 'deliverer_id');
    }
}
