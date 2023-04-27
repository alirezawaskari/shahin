<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialPlansTypeItem extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financialPlansType()
    {
        return $this->belongsTo(FinancialPlansType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function financialplanstypeItemable()
    {
        return $this->morphTo();
    }
}
