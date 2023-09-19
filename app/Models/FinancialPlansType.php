<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialPlansType extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];
    /**
     * @var array
     */
    protected $casts = [
        'guarantee_type' => 'array',
    ];
    /**
     *type of payment
     */
    const TYPE = [
        'installment' => 'پرداخت اقساطی',
        'check' => 'پرداخت از طریق چک',
        'cash' => 'پرداخت نقدی',
    ];

    /**
     * Get all of the services that are assigned this financialPlansTypeItems.
     */
    public function services()
    {
        return $this->morphedByMany(Service::class, 'financialplanstypeItemable','financial_plans_type_items');
    }
    /**
     * Get all of the categories that are assigned this financialPlansTypeItems.
     */
    public function categories()
    {
        return $this->morphedByMany(Category::class, 'financialplanstypeItemable','financial_plans_type_items');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function financialPlansTypeItems()
    {
        return $this->hasMany(FinancialPlansTypeItem::class);
    }

     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function applicant()
    {
        return $this->belongsTo(User::class , 'applicant_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function representations()
    {
        return $this->belongsToMany(Representation::class);
    }
    /**
     * @param mixed $payment_type
     * @return bool
     * get payment type // cash or credit
     */
    public static function getPaymentType($payment_type)
    {
        if($payment_type == 'installment' || $payment_type == 'check')
        {
            return 1;
        }
        elseif($payment_type == 'cash')
        {
            return 0;
        }
    }
    /**
     * @param mixed $item // category or service
     * @return object
     */
    public static function getFinancialPlansTypeByItem($item)
    {
        return $item->financialPlansTypes()->where('status' , 1)->whereRaw('capacity > reserved')->where('end_date' , '>=' , now())->get()->unique();
    }
    /**
     * @param mixed $item // category or service
     * @return object
     */
    public static function filterFinancialPlansTypeByPaymentType($financial_plans_types , $payment_type)
    {
        foreach($financial_plans_types as $key => $financial_plans_type)
        {
            if(self::getPaymentType($financial_plans_type->payment_type) != $payment_type)
            {
                $financial_plans_types->forget($key);
            }
        }
        return $financial_plans_types;
    }
    /**
     * @param FinancialPlansType $financial_plans_type
     * @param mixed $type
     * @return bool
     */
    public static function checkFinancialPlansTypeByPaymentType(FinancialPlansType $financial_plans_type , $type)
    {
        if(self::getPaymentType($financial_plans_type->payment_type) == $type)
        {
            return true;
        }
        return false;
    }
}
