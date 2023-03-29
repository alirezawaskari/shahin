<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportPurchaseInvoice extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseinvoce()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
