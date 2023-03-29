<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable=['type','title','content','file','status'];

    const STATUS = [
        '1' => 'منتظر تایید وکیل',
        '2' => 'منتظر تایید مدیر مالی',
        '3' => 'منتظر تایید مدیر عامل'
    ];

    const TYPE = [
        '1' => 'خرید',
        '2' => 'فروش',
        '3' => 'واگذاری'
    ];

    public function contractHistories()
    {
        return $this->hasMany(ContractHistory::class);
    }

    public function arrangeContracts()
    {
        return $this->hasMany(ArrangeContract::class);
    }

}
