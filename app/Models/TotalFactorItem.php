<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
class TotalFactorItem extends Model implements AuditableContract
{
    use Auditable;
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string[]
     */
    protected $casts = [
        'contract_values' => 'array',
        'factor_values' => 'array',
        'prepayment' => 'array',
        'price_difference' => 'array',
    ];

    /**
     *
     */
    const GuaranteeTYPE = [
        'check' => 'چک',
        'certificate' => 'گواهی کسراز حقوق',
        'promissorynote' => 'سفته',
    ];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function factor()
    {
        return $this->belongsTo(Factor::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function factors()
    {
        return $this->hasMany(Factor::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function totalFactorItemContractSettlement()
    {
        return $this->hasOne(TotalFactorItemContractSettlement::class , 'item_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function factorItems()
    {
        return $this->hasMany(FactorItem::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * get totalFactorItem facotrItems groupBy factoritemable //service or category
     */
    public function factorItemsGroup()
    {
        return $this->hasMany(FactorItem::class)->groupBy('factoritemable_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function totalFactorItemInstallments()
    {
        return $this->hasMany(TotalFactorItemInstallment::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function property()
    {
        return $this->morphOne(FactorItemAttributes::class, 'factor_item_attributable');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function histories()
    {
        return $this->morphMany(History::class , 'historiable');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $phone = request('phone');
        $family = request('family');
        $name = request('name');
        $service = request('service');
        $service_item = request('service_item');
        $category = request('category');
        $purchase_type = request('purchase_type');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $status = request('status');
        $status_mali = request('status_mali');
        $supporter = request('user');
        $representation = request('representation');
        $organization = request('organization');
        $center = request('center');
        $productCategory = request('product_category');
        $serviceCategory = request('service_category');
        $subServiceCategory = request('sub_service_category');
        $serviceItems = request('service_items');
        $has_payment = request('has_payment');

        $query->whereHas('factor', function ($query) {
            $query->where('status', 1);
        });
        if (auth()->user()->admin_representation) {
            $query->whereHas('factorItems', function ($query){
                $query->whereHas('representation', function ($query){
                    $query->where('representation_id', auth()->user()->admin_representation_id);
                });
            });
        }
        if (isset($name) && trim($name) != '') {
            $query->whereHas('factor', function ($query) use ($name) {
                $query->whereHas('user', function ($query) use ($name) {
                    $query->where('name', 'LIKE', '%' . $name . '%');
                });
            });
        }

        if (isset($family) && trim($family) != '') {
            $query->whereHas('factor', function ($query) use ($family) {
                $query->whereHas('user', function ($query) use ($family) {
                    $query->where('family', 'LIKE', '%' . $family . '%');
                });
            });
        }

        if (isset($phone) && trim($phone) != '') {
            $query->whereHas('factor', function ($query) use ($phone) {
                $query->whereHas('user', function ($query) use ($phone) {
                    $query->where('phone', '=', $phone);
                });
            });
        }

        if (isset($status) && !is_null($status) && is_array($status)) {
            $query->whereIn('total_factor_item_status_id', $status);
        }

        if(isset($has_payment) && trim($has_payment) != '' && $has_payment != 'all')
        {
            if($has_payment == 'true')
            {
                $query->whereHas('factors', function ($query){
                    $query->where('status', 1);
                });
            }
            elseif($has_payment == 'false')
            {
                $query->whereDoesntHave('factors', function ($query){
                    $query->where('status', 1);
                });
            }
        }

        if (isset($status_mali) && !is_null($status_mali) && is_array($status_mali)) {
            $query->whereIn('status_mali', $status_mali);
        }

        if (isset($service_item) && trim($service_item) != '' && $service_item != 'all') {
            $query->whereHas('factorItems', function ($query) use ($service_item) {
                $query->where('service_item_id', $service_item);
            });
        }

        if (isset($purchase_type) && trim($purchase_type) != '' && $purchase_type != 'all') {
            $query->whereHas('factorItems', function ($query) use ($purchase_type) {
                $query->where('purchase_type', $purchase_type);
            });
        }

        if (isset($startDate) && trim($startDate) != '' && isset($endDate) && trim($endDate) != '') {
            $query->whereHas('factorItems', function ($query) use ($startDate , $endDate) {
                $query->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
            });
        }

        if (auth()->user()->hasPermission('file-detail')) {
            if (auth()->user()->hasRole('organ-manager')) {
                $query->whereHas('factor', function ($query) {
                    $query->where('organization_id', auth()->user()->admin_organization_id);
                });
            }

            if (auth()->user()->center_id) {
                $center = auth()->user()->center_id;
                $query->whereHas('factorItems', function ($query) use ($center) {
                    $query->where('center_id', $center);
                });
            }

            if (isset($representation) && trim($representation) != 'all') {
                if (trim($representation) == 'center') {
                    $query->whereHas('factor', function ($query) {
                        $query->where('representation_id', null);
                    });
                } else {
                    $query->whereHas('factor', function ($query) use ($representation) {
                        $query->where('representation_id', $representation);
                    });
                }
            }

            if (isset($organization) && trim($organization) != 'all') {
                $query->whereHas('factor', function ($query) use ($organization) {
                    $query->where('organization_id', $organization);
                });
            }

            if (isset($center) && trim($center) != 'all') {
                $query->whereHas('factorItems', function ($query) use ($center) {
                    $query->where('center_id', $center);
                });
            }


            if (isset($service) && trim($service) != '' && $service != 'all') {
                $query->whereHas('factorItems', function ($query) use ($service) {
                    $query->where('factoritemable_id', $service);
                });
            }

            if (isset($category) && trim($category) != '' && $category != 'all') {
                $query->whereHas('factorItems', function ($query) use ($category) {
                    $query->where('factoritemable_id', $category);
                });
            }

            if (isset($supporter) && trim($supporter) != '' && $supporter != 'all') {
                $query->whereHas('factorItems', function ($query) use ($supporter) {
                    $query->where('supporter_id', $supporter);
                });
            }
        } else {
            $query->whereHas('products', function ($query) use ($center) {
                $query->where('user_id', auth()->user()->id);
            });
        }

        if ( (is_null($subServiceCategory) || $subServiceCategory == 'all' ) && isset($serviceCategory) && $serviceCategory!='all' ){
            $serviceCategory=Category::find($serviceCategory);
            $query->whereHas('factorItems', function ($query) use ($serviceCategory) {
                $query->where('factoritemable_type', '=', Service::class)->whereIn('factoritemable_id', $serviceCategory->services->pluck('id')->toArray());
            });
        }
        if (isset($subServiceCategory) && $subServiceCategory != 'all'){
            $query->whereHas('factorItems', function ($query) use ($subServiceCategory) {
                $query->where('factoritemable_type', '=', Service::class)->where('factoritemable_id', $subServiceCategory);
            });
        }
        if (isset($productCategory) && trim($productCategory) != 'all') {
            $query->whereHas('factorItems', function ($query) use ($productCategory) {
                $query->where('factoritemable_type', '=', Category::class)->where('factoritemable_id', $productCategory);
            });
        }
        if (isset($serviceItems) && is_array($serviceItems) ) {
            $query->whereHas('factorItems', function ($query) use ($serviceItems) {
                $query->whereIn('service_item_id',$serviceItems);
            });
        }
        return $query;
    }
    /**
     * @param TotalFactorItem $item
     * @return int
     * get the remaining extraPay amount
     */
    public static function getDebtExtraPay(TotalFactorItem $item)
    {
        $factors_paid_extra_pay = self::getExtraPayPaid($item);
        $extra_pay = self::getAllExtraPay($item);
        $impunity_penalty = $item->impunityPenalty ? $item->impunityPenalty->amount : 0;
        $extra_pay_price = $extra_pay['extra_pay_price'] - $factors_paid_extra_pay - $impunity_penalty;

        return $extra_pay_price;
    }

    /**
     * @param TotalFactorItem $item
     * @return int
     */
    private static function getExtraPayPaid(TotalFactorItem $item)
    {
        return $item->factors()
            ->where('title' , 'extra_pay')
            ->where(function($query) {
                $query->where('status', 1);
            })->sum('price');
    }
    /**
     * @param TotalFactorItem $item
     * @return array
     * get the total extraPay
     */
    public static function getAllExtraPay(TotalFactorItem $item)
    {
        $extra_pay_price = 0;
        $extra_pay_day = 0;
        $installments = $item->totalFactorItemInstallments->where('status' , '!=' , 2)->where('date', '<=' , Carbon::now());
        foreach ($installments as $installment) {
            $installment_extra_pay = TotalFactorItemInstallment::getExtraPay($installment);
            $extra_pay_price = $extra_pay_price + $installment_extra_pay['extra_pay_price'];
            $extra_pay_day = $extra_pay_day + $installment_extra_pay['extra_pay_day'];
        }

        //get the total number of extraPay days
        $extra_pay = [
            'extra_pay_day' => $extra_pay_day,
            //get the total extraPay price
            'extra_pay_price' => $extra_pay_price,
            //get the total extraPay in the appropriate format
            'appropriate_format' => $extra_pay_day ? $extra_pay_day . 'روز - ' . number_format($extra_pay_price) . 'ریال'  : 'ندارد',
        ];

        return $extra_pay;
    }
    /**
     * @param TotalFactorItem $item
     * @return mixed
     * get appropriate_format
     */
    public static function getExtraPay(TotalFactorItem $item)
    {
        $extra_pay = self::getAllExtraPay($item);
        return $extra_pay['appropriate_format'];
    }
    /**
     * @param mixed $status_factor
     * @return string
     * get status_factor string
     */
    public static function statusFactor($status_factor)
    {
        return [
            '0' => 'جدید',
            '1' => 'تاییدیه پشتیبان',
            '2' => 'مشاوره',
            '3' =>  'تعلیق پشتیبان',
            '4' => 'منتظر تکمیل مدارک',
            '5' => 'منتظر تایید مالی',
            '6' => 'شروع کار',
            '7' => 'اتمام کار',
            '8' => 'منتظر تایید مدیر',
            '9' => 'منتظر تایید امور تسهیلات',
            '10' => 'آماده اجرا',
            '11' => 'منتظر تایید مدیر مالی نمایندگی',
            '12' => 'منتظر تایید مدیر نمایندگی',
            '13' => 'منتظر تایید امور قراردادها',
            '14' => 'منتظر تایید پشتیبان',
            '15' =>'پرونده مختومه',
            'null' => 'تعیین نشده',
        ][$status_factor];
    }
    /**
     * @param mixed $status_mali
     * @return string
     * get status_mali string
     */
    public static function statusMali($status_mali)
    {
        return [
            '0' => 'تایید نشده',
            '1' => 'جاری',
            '2' => 'اخطار اول',
            '3' => 'اخطار دوم',
            '4' => 'ارجاع به امور قراردادها',
            '5' => 'اتمام اقساط',
            '6' => 'تسویه حساب',
            '7' => 'منتظر تایید تسویه حساب مالی',
            '8' => 'منتظر تایید تسویه حساب مدیر',
            '9' => 'آماده تسویه حساب',
            '10' => 'ارجاع به حقوقی',
            '11' => 'منتظر تایید تسویه حساب امورقراردادها',
        ][$status_mali];
    }
    /**
     * @param TotalFactorItem $item
     * @return string
     * Get the totalFactorItem items.
     * items include services or categories
     */
    public static function setFactorItemable(TotalFactorItem $item)
    {
        $temp = [];
        foreach ($item->factorItemsGroup as $factor_item) {
            array_push($temp, $factor_item->factoritemable->title);
        }
        isset($item->factorItem) ? array_push($temp, $item->factorItem->factoritemable->title) : '';
        return implode(' - ', $temp);
    }
    /**
     * @param TotalFactorItem $item
     * @return void
     * update totalFactorItem financial information
     */
    public static function updateCalculate(TotalFactorItem $item)
    {
        $price = 0;
        $total_price = 0;
        $installments_num = 0;
        $total = 0;
        $debt = 0;

        $prepayment_pay = self::getPrepaymentPaid($item);

        $factor_items = $item->factorItemsGroup;
        foreach ($factor_items as $factor_item) {
            $price = $price + $factor_item->property->prepayment_price;
            $total_price = $total_price + $factor_item->total_price;
            $installments_num = $installments_num < $factor_item->installments_num ? $factor_item->installments_num : $installments_num;
            $total = $total + $factor_item->property->price_difference_total;
            $debt = $debt + $factor_item->property->price_difference_debt;
        }

        $item->property()->update([
            'prepayment_price'=>$price,
            'prepayment_debt'=>$price - $prepayment_pay,
            'price_difference_total'=>$total,
            'price_difference_debt'=>$debt,
        ]);

        $item->update([
           'total_price' => $total_price,
           'installments_num' => $installments_num,
        ]);

        TotalFactorItem::createOrUpdateInstallments($item->approval_date , $item);
    }
    /**
     * @param mixed $approval_date
     * @param TotalFactorItem $total_factor_item
     * @return void
     */
    public static function createOrUpdateInstallments($approval_date , TotalFactorItem $total_factor_item)
    {
        //change approval date for create installments
        $total_factor_item->update([
            'approval_date' => $approval_date,
        ]);

        $factor_items = $total_factor_item->factorItems;
        $price = 0;
        $date = Carbon::now();
        foreach($factor_items as $factor_item)
        {
            $factor_item->update(['approval_date' => $approval_date]);
            FactorItem::createOrUpdateInstallments($factor_item);
        }
        $factor_items = $total_factor_item->factorItemsGroup;
        if (count($total_factor_item->totalFactorItemInstallments) == 0) {
            for ($i = 1 ; $i <= $total_factor_item->installments_num ; $i++) {
                foreach($factor_items as $factor_item)
                {
                    $price = $price + ($factor_item->factorItemInstallments->where('level' , $i)->last() ? $factor_item->factorItemInstallments->where('level' , $i)->last()->price : 0);
                    $date = $factor_item->factorItemInstallments->where('level' , $i)->last() ? $factor_item->factorItemInstallments->where('level' , $i)->last()->date : $date;
                }
                if($price != 0)
                {
                    $totalFactorItemInstallment = new TotalFactorItemInstallment();
                    $totalFactorItemInstallment->price = $price;
                    $totalFactorItemInstallment->level = $i;
                    $totalFactorItemInstallment->date = $date;
                    $totalFactorItemInstallment->total_factor_item_id = $total_factor_item->id;
                    $totalFactorItemInstallment->save();
                    $price = 0;
                }
            }
        } else {
            self::updateInstallments($total_factor_item);
        }
    }
    /**
     * @param TotalFactorItem $total_factor_item
     * @return void
     * deleting created installments and re-creating them
     */
    private static function updateInstallments(TotalFactorItem $total_factor_item)
    {
        $total_factor_item_installments_paid = $total_factor_item->totalFactorItemInstallments->where('status' , 1)->count();
        if (!$total_factor_item->confirm && !$total_factor_item_installments_paid) {
            $total_factor_item->totalFactorItemInstallments()->delete();
            $total_factor_item = TotalFactorItem::find($total_factor_item->id);
            self::createOrUpdateInstallments($total_factor_item->approval_date , $total_factor_item);
        }
    }
    /**
     * @param TotalFactorItem $item
     * @return mixed
     * get expire installments
     */
    public static function getExpireInstallments(TotalFactorItem $item)
    {
        $expire_installments = $item->totalFactorItemInstallments->where('status', 0)->where('date', '<', Carbon::now());
        return $expire_installments;
    }
    /**
     * @param TotalFactorItem $item
     * @return string
     */
    public static function setFatorItemCollectionResnumber(TotalFactorItem $item)
    {
        $resnumber = [];
        $factor_items = $item->factorItems;

        foreach($factor_items as $factor_item)
        {
            $fator_item_collections = FactorItem::getFatorItemCollection($factor_item)->get();
            foreach($fator_item_collections as $fator_item_collection)
            {
                array_push($resnumber, $fator_item_collection->resnumber);
            }
        }

        return implode(' , ', $resnumber);
    }
    /**
     * @param TotalFactorItem $item
     * @return string
     * set agents contract number for set contract factorItem
     */
    public static function setAgentContractNumber(TotalFactorItem $item)
    {
        $contract_number = [];
		$contract_start_date = [];

        $factor_items = $item->factorItems;

        foreach($factor_items as $factor_item)
        {
            $fator_item_collections = FactorItem::getFatorItemCollection($factor_item)->get();
            foreach($fator_item_collections as $fator_item_collection)
            {
                if(isset($fator_item_collection->center))
                {
                    isset($fator_item_collection->center->contract_number) ? array_push($contract_number, $fator_item_collection->center->contract_number) : array_push($contract_number, '......');
                    isset($fator_item_collection->center->contract_start_date) ? array_push($contract_start_date, verta($fator_item_collection->center->contract_start_date)->format('Y/m/d')) : '.......';
                    // $center_agents = $fator_item_collection->factor_values['center-agent'];
                    // foreach($center_agents as $center_agent)
                    // {
                    //     $employee = Employee::where('user_id' , $center_agent)->first();
                    //     isset($employee->contract_number) ? array_push($contract_number, $employee->contract_number) : array_push($contract_number, '......');
                    //     isset($employee->contract_start_date) ? array_push($contract_start_date, verta($employee->contract_start_date)->format('Y/m/d')) : '';
                    // }
                }
            }
        }

        $contract_number = implode(' , ', $contract_number);
        $contract_start_date = implode(' , ', $contract_start_date);

        return $contract_number.' مورخ '.$contract_start_date;
    }
    /**
     * @param TotalFactorItem $item
     * @return mixed
     * check expire installments && unpaid installments for change status_mali after installment paid
     */
    public static function checkInstallments(TotalFactorItem $item)
    {
        $expire_installments = count(self::getExpireInstallments($item));
        if ($expire_installments == 0) {
            $status_mali = $item->status_mali == 0 ? 0 : 1;
            $item->update(['status_mali' => $status_mali, 'remember' => 0]);
        }

        $unpaid_installments = count($item->totalFactorItemInstallments->where('status', 0));
        if ($unpaid_installments == 0) {
            $item->update(['status_mali' => 5, 'remember' => 0]);
        }
        return $item->totalFactorItemInstallments;
    }
    /**
     * @param TotalFactorItem $item
     * @return int
     * get totalFactorItem credit
     */
    public static function calculateCredit(TotalFactorItem $item)
    {
        $sessions_sum_price = 0;
        foreach($item->factorItems as $factor_item)
        {
            $sessions_sum_price = $sessions_sum_price + FactorItem::getSessionsSumPrice($factor_item , 'all');
        }
        $prepayment_paid = self::getPrepaymentPaid($item) - $item->discount;

        $user = $item->factor->user;
        if($item->confirm)
        {
            $credit = $item->credit > 0 ? $item->credit : 0;
        }
        else
        {
            $credit = ($prepayment_paid - $sessions_sum_price) > 0 ? $prepayment_paid - $sessions_sum_price : 0;
        }

        $credit = $credit + $user->wallet;
        $credit = $credit + $user->credit;

        return $credit;
    }
    /**
     * @param TotalFactorItem $item
     * @return array
     * get total debt
     */
    public static function calculateTotalDebt(TotalFactorItem $item)
    {
        $sessions_sum_price = 0;
        foreach($item->factorItems as $factor_item)
        {
            $sessions_sum_price = $sessions_sum_price + FactorItem::getSessionsSumPrice($factor_item , 'all');
        }
        $prepayment_debt=isset($item->property->prepayment_debt)?$item->property->prepayment_debt:0;
        $prepayment_paid = self::getPrepaymentPaid($item) - $item->discount;

        $debt = 0;
        $total_debt_items = [];
        if($item->confirm)
        {
            $price_difference_paid = isset($item->property->price_difference_total)?($item->property->price_difference_total - $item->property->price_difference_debt) : 0;
            $price_difference_installment = isset($item->property->price_difference_installment)?$item->property->price_difference_installment : 0;
            $debt = $debt + self::getExpireInstallments($item)->sum('price') - $price_difference_installment;
            $debt = $debt + (isset($item->property->price_difference_debt) ? $item->property->price_difference_debt : 0);
            $debt = $debt + $prepayment_debt;
            $total_debt_items['expire_installments_price'] = self::getExpireInstallments($item)->sum('price') - $price_difference_installment;
            $total_debt_items['price_difference_debt'] = isset($item->property->price_difference_debt) ? $item->property->price_difference_debt : 0;
            $total_debt_items['extra_work'] = ($sessions_sum_price - ($price_difference_paid + $item->total_price + $item->discount)) > 0 ? $sessions_sum_price - ($price_difference_paid + $item->total_price + $item->discount) : 0;
        }
        else
        {
            $debt = $debt + $prepayment_debt;
            $total_debt_items['expire_installments_price'] = 0;
            $total_debt_items['price_difference_debt']=0;
            $total_debt_items['extra_work'] = ($sessions_sum_price - $prepayment_paid) > 0 ? $sessions_sum_price - $prepayment_paid : 0;
        }

        $total_debt_items['prepayment_debt'] = $prepayment_debt;
        $total_debt_items['registeration_debt'] = isset($item->property->registeration_debt) ? $item->property->registeration_debt : 0;
        $debt = $debt + $total_debt_items['registeration_debt'];
        $total_debt_items['debt'] = $debt;

        return $total_debt_items;
    }
    /**
     * @param TotalFactorItem $item
     * @return int
     */
    public static function getPrepaymentPaid(TotalFactorItem $item)
    {
        //get prepayment price paid in credit factorItem
        $prepayment_paid = $item->factors()
            ->where(function($query) {
                $query->where( 'title' , 'prepayment' );
            })->where(function($query) {
                $query->where('status' , 1);
            })->sum( 'price' );

        //get return payment price in credit factorItem
        $return_payment =  $item->factors()
            ->where('status', 2)
            ->where(function($query) {
                $query->where( 'title' , 'prepayment' );
            })->sum('price');

        $prepayment_paid = $prepayment_paid - $return_payment;

        return $prepayment_paid;
    }
    /**
     * @param TotalFactorItem $item
     * @return array
     * calculation of the financial balance of the totalFactorItem for settlement
     */
    public static function financialBalance(TotalFactorItem $item)
    {
        $financial_balance_items = [];
        $total_debt = 0;
        $total_credit = 0;
        $property_is_exists=$item->property()->exists();

        $financial_balance_items['prepayment_debt'] = $property_is_exists?$item->property->prepayment_debt:0;
        $total_debt = $total_debt + ($financial_balance_items['prepayment_debt'] > 0 ? $financial_balance_items['prepayment_debt'] : 0);
        $total_credit = $total_credit + ($financial_balance_items['prepayment_debt'] < 0 ? $financial_balance_items['prepayment_debt'] : 0);

        $installment = $property_is_exists && !is_null($item->property->price_difference_installment) ? $item->property->price_difference_installment : 0;
        $financial_balance_items['unpaid_installments'] = $item->totalFactorItemInstallments->where('status' , '!=' , 1)->sum('price') - $installment;
        $total_debt = $total_debt + ($financial_balance_items['unpaid_installments'] > 0 ? $financial_balance_items['unpaid_installments'] : 0);
        $total_credit = $total_credit + ($financial_balance_items['unpaid_installments'] < 0 ? $financial_balance_items['unpaid_installments'] : 0);

        $financial_balance_items['price_difference_debt'] = $property_is_exists && !is_null($item->property->price_difference_debt) ? $item->property->price_difference_debt : 0;
        $total_debt = $total_debt + ($financial_balance_items['price_difference_debt'] > 0 ? $financial_balance_items['price_difference_debt'] : 0);
        $total_credit = $total_credit + ($financial_balance_items['price_difference_debt'] < 0 ? $financial_balance_items['price_difference_debt'] : 0);
        $financial_balance_items['impunity_installment'] = $item->impunityInstallment;
        $financial_balance_items['impunity_penalty'] = $item->impunityPenalty;
        $financial_balance_items['extra_pay'] = self::getDebtExtraPay($item);
        $total_debt = $total_debt + ($financial_balance_items['extra_pay'] > 0 ? $financial_balance_items['extra_pay'] : 0);
        $total_credit = $total_credit + ($financial_balance_items['extra_pay'] < 0 ? $financial_balance_items['extra_pay'] : 0);

        //find creator user and append in impunity object
        if (!is_null($financial_balance_items['impunity_installment'])){
            $financial_balance_items['impunity_installment']->creator=User::find($financial_balance_items['impunity_installment']->created_by);
        }
        if (!is_null($financial_balance_items['impunity_penalty'])){
            $financial_balance_items['impunity_penalty']->creator=User::find($financial_balance_items['impunity_penalty']->created_by);
        }

        $impunity_installment_amount = 0;
        $impunity_penalty_amount = 0;
        if ($item->impunityInstallment()->exists()){
            $impunity_installment_amount=$item->impunityInstallment->amount;
        }
        if ($item->impunityPenalty()->exists()){
            $impunity_penalty_amount=$item->impunityPenalty->amount;
        }

        $financial_balance_items['total_debt'] = $total_debt;
        $financial_balance_items['total_credit'] = $total_credit;

        $financial_balance_items['financial_balance'] = ($total_debt + $total_credit)-($impunity_installment_amount);
        $financial_balance_items['financial_balance_without_extra_pay'] = $financial_balance_items['financial_balance'] - $financial_balance_items['extra_pay'];

        return $financial_balance_items;
    }
    /**
     * @param TotalFactorItem $item
     * @param mixed $price
     * @return array
     */
    public static function checkPriceDifference(TotalFactorItem $item , $price)
    {
        if(isset($item->property) && ($item->property->price_difference_debt*-1) >= $price)
        {
            $total_price_difference_debt = $item->property->price_difference_debt + $price;
            $credit = $item->credit - $price;
            self::deductionPriceDifferenceFactorItems($item , $price);

            $item->property->update([
                'price_difference_debt' => $total_price_difference_debt,
            ]);
            $item->update([
                'credit' => $credit,
            ]);
            return ['success' => true , 'message' => 'با موفقیت انجام شد'];
        }

        return ['success' => false , 'message' => 'میزان اختلاف حساب پرونده کافی نمی باشد.' ];
    }
    /**
     * @param TotalFactorItem $item
     * @param $price
     * @return array
     */
    public static function checkOverpayment(TotalFactorItem $item , $price)
    {
        if (isset($item->property) && $item->property->price_difference_installment >= $price) {
            $price_difference_installment = $item->property->price_difference_installment - $price;
            $item->property->update([
                'price_difference_installment' => $price_difference_installment,
            ]);
            return ['success' => true , 'message' => 'با موفقیت انجام شد'];
        }
        return ['success' => false , 'message' => 'میزان اضافه پرداخت پرونده کافی نمی باشد.' ]        ;
    }
    /**
     * @param TotalFactorItem $item
     * @return void
     * delete totalFactorItem if did not have a factorItem
     */
    public static function checkHasFactorItem(TotalFactorItem $item)
    {
        if(!$item->factorItems()->count())
        {
            $item->property->delete();
            $item->delete();
        }
    }

    /**
    * @param TotalFactorItem $item
    * @param FactorItem $supervisor_factor_item
    * @return void
    */
    public static function setFactorsPaid(TotalFactorItem $item , FactorItem $supervisor_factor_item)
    {
        if($item->factors()->where('title' , 'prepayment'))
        {
            $item_prepayment_price = 0;
            $factor_items = $item->factorItems->where('id' , '!=' , $supervisor_factor_item->id);
            foreach($factor_items as $factor_item)
            {
                $item_prepayment_price = $item_prepayment_price + $factor_item->property->prepayment_price;
            }

            $item_prepayment_paid = self::getPrepaymentPaid($item);
            if($item_prepayment_paid > $item_prepayment_price)
            {
                $extra_prepayment_paid = $item_prepayment_paid - $item_prepayment_price;
                $extra_prepayment_paid =  (float) str_replace(',', '', Helper::numberConverter($extra_prepayment_paid));

                //create return payment factor in totalFactorItem
                self::returnPaymentInTotalFactorItem($item , $extra_prepayment_paid);
                //create prepayment factor paid in factoritem
                FactorItem::createPaymentInFactorItem($supervisor_factor_item , $extra_prepayment_paid);
            }
        }
    }
    /**
    * @param TotalFactorItem $item
    * @param mixed $price
    * @return void
    */
    private static function returnPaymentInTotalFactorItem(TotalFactorItem $item , $price)
    {
        $factor = new Factor();
        $factor->title = 'prepayment';
        $factor->user_id = $item->factor->user_id;
        $factor->price = $price;
        $factor->type = 'in_factor_item';
        $factor->total_factor_item_id = $item->id;
        $factor->callcenter_id = auth()->user()->id;
        $factor->representation_id = auth()->user()->admin_representation_id;
        $factor->status = 2;
        $factor->save();
    }

    /**
     * @param TotalFactorItem $item
     * @param mixed $price
     * @return bool
     */
    private static function deductionPriceDifferenceFactorItems(TotalFactorItem $item , $price)
    {
        $factor_items = $item->factorItems;
        foreach($factor_items as $factor_item)
        {
            if($factor_item->property->price_difference_debt + $price > 0)
            {
                $price = $factor_item->property->price_difference_debt + $price;
                $factor_item->update(['credit' => $factor_item->credit + $factor_item->property->price_difference_debt]);
                $factor_item->property->update(['price_difference_debt' => 0]);
            }
            else
            {
                $factor_item->update(['credit' => $factor_item->credit - $price]);
                $factor_item->property->update(['price_difference_debt' => $factor_item->property->price_difference_debt + $price]);

                return true;
            }
        }
        return false;
    }

    /**
     * @param TotalFactorItem $item
     * @return bool
     */
    public static function checkPayment(TotalFactorItem $item)
    {
        $return_payment_price = $item->factors()->where('status' , 2)->sum('price');
        $paid_price = $item->factors()->where('status' , 1)->sum('price');

        $result = $paid_price - $return_payment_price > 0 ? true : false;
        return $result;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function impunities()
    {
        return $this->morphMany(Impunity::class, 'impunitiable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function impunityInstallment()
    {
        return $this->morphOne(Impunity::class, 'impunitiable')->where('status',1)->where('type','installment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function impunityPenalty()
    {
        return $this->morphOne(Impunity::class, 'impunitiable')->where('status',1)->where('type','penalty');
    }

    /**
     * @return mixed
     */
    public function checks()
    {
        return $this->morphMany(Check::class , 'checkable');

    }

    /**
     * @return mixed
     */
    public function promissories()
    {
        return $this->morphMany(Promissory::class , 'promissoryable');

    }

    /**
     * @param TotalFactorItem $item
     * @return array
     * this function is used get guarantee types list by financialPlansType selected if exist financialPlansType else get all guarantee types list
     */
    public static function getGuaranteeTypeByFinancialPlansType(TotalFactorItem $item)
    {
       $guarantee_types_list = ['check' , 'certificate' , 'promissorynote'];
       $financial_plans_type = $item->factorItems->first()->financialPlansType;
       $guarantee_types_list = $financial_plans_type && $financial_plans_type->guarantee_type ? $financial_plans_type->guarantee_type : $guarantee_types_list;
       return $guarantee_types_list;
    }
    /**
     * @param TotalFactorItem $total_factor_item
     * @return int
     */
    public static function getFinancialPlansTypePriceByFactorItems(TotalFactorItem $total_factor_item)
    {
        $price = 0;
        foreach ($total_factor_item->factorItems as $item) {
            $price = $price + $item->financialPlansType->price + ($item->financialPlansType->price * ($item->factoritemable->tax/100));
        };
        return $price;
    }
}
