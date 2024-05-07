<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PercentageAllocationEmployee extends Model
{
    use HasFactory;

    /**
     *types of PercentageAllocationEmployee
     */
    const TYPE = [
        'register' => 'هزینه ثبت نام',
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
}
