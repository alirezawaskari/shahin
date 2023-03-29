<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialPlansTypeItem extends Model
{
    public function financialPlansType()
    {
        return $this->belongsTo(FinancialPlansType::class);
    }

    public function financialplanstypeItemable()
    {
        return $this->morphTo();
    }
}
