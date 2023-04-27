<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * @var string[]
     */
    protected $fillable=['type','title','content','file','status'];

    /**
     * list of status of contract
     */
    const STATUS = [
        '1' => 'منتظر تایید وکیل',
        '2' => 'منتظر تایید مدیر مالی',
        '3' => 'منتظر تایید مدیر عامل'
    ];

    /**
     * list of types  of contract
     */
    const TYPE = [
        '1' => 'خرید',
        '2' => 'فروش',
        '3' => 'واگذاری'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contractHistories()
    {
        return $this->hasMany(ContractHistory::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function arrangeContracts()
    {
        return $this->hasMany(ArrangeContract::class);
    }

}
