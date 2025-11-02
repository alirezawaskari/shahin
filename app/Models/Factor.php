<?php

namespace App\Models;

use App\Events\referralCenterNotification;
use OwenIt\Auditing\Auditable;
use App\Events\smsSuccessRegisterPayment;
use App\Helpers\CalculationHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Symfony\Component\CssSelector\Node\ElementNode;

class Factor extends Model implements AuditableContract
{
    use Auditable;
    use SoftDeletes;
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    protected $casts = [
        'values' => 'array',
        'after_values' => 'array'
    ];

    /**
     *types of factor
     */
    const TYPE = [
        'online' => 'آنلاین',
        'card' => 'کارت به کارت',
        'wallet' => 'کسر از کیف پول',
        'card_reader' => 'کارتخوان',
        'cash' => 'پول نقد',
        'credit' => 'کسر از حواله',
        'organ' => 'سازمان',
        'overpayment' => 'کسر از اضافه پرداخت',
        'confirm_factor' => 'دارای تاییدیه',
        'account' => 'واریز به حساب',
        'increase_credit' => 'افزایش حواله',
        'deduction_price_difference' => 'کسر از بستانکاری',
        'supervisor' => 'دارای سرپرست',
        'in_factor_item' => 'بین پرونده ای',
        'check' => 'چک',
        'send_card_number' => 'کارت به کارت(حساب مالیاتی)',
        'send_shaba_number' => 'واریز به حساب(حساب مالیاتی)',
        'send_tax_free_card_number' => 'واریز به حساب(حساب معمولی)'
    ];

    /**
     *title of factors
     */
    const TITLE = [
        'register' => 'هزینه ثبت نام',
        'installment' => 'قسط',
        'prepayment' => 'پیش دریافت اقساط',
        'extra_pay' => 'جریمه',
        'factor' => 'فروش نقد',
        'price_difference' => 'اختلاف حساب',
        'employee_payment' => 'پرداخت به همکار',
        'wallet' => 'شارژ کیف پول',
        'purchase_invoice_payment' => 'هزینه فاکتور خرید',
        'registeration_debt' => 'اختلاف هزینه ثبت نام', //diff registeration price by change financialPlansType
    ];

    /**
     *
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->resnumber = self::makeUniqueTracking($model);
        });
    }

    //set unique resnumber for each factor when createing.

    /**
     * @param $model
     * @return string
     */
    private static function makeUniqueTracking($model)
    {
        do {
            $resNumber = rand(12, 99) . substr(Carbon::now()->timestamp, 6, 11) . substr($model->user->phone, 7, 11);
            $found = self::where('resnumber', $resNumber)->first();
        } while (!is_null($found));
        return $resNumber;
    }

    /**
     * @param Factor $factor
     * @return mixed
     */
    private static function setSupporter(Factor $factor)
    {
        $supporter_id=null;

        if($factor->callcenter && $factor->callcenter->hasPermission('supporter_user_file'))
        {
            $supporter_id = $factor->callcenter_id;
            $supporter_user_selected = $factor->callcenter;
        }
        else
        {
            $supporter_users = User::getSupporterUserAdmin($factor->representation_id);
            if(is_null($supporter_users)){
                $supporter_user_selected = User::find(16983);
            }
            else
            {
                $supporter_user_selected = $supporter_users[array_rand( $supporter_users )];
            }
            $supporter_id = $supporter_user_selected->id;
        }
        $factor->factorItems()->update([
            'supporter_id'=>$supporter_id
        ]);
        if ($factor->totalFactorItemReg()->exists()){
            foreach ( $factor->totalFactorItemReg->factorItems() as $factorItem){
                $factorItem->update([
                    'supporter_id'=>$supporter_id
                ]);
            }
        }
        return $supporter_user_selected;
    }

    /**
     * @param Factor $factor
     * @param User $supporter_user
     * @return void
     */
    private static function smsRegisterPaymentSuccess(Factor $factor, User $supporter_user)
    {
        event(new smsSuccessRegisterPayment($factor , $supporter_user));
    }


    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'resnumber';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Get the callcenter that creator the factor.
     */
    public function callcenter()
    {
        return $this->belongsTo(User::class, 'callcenter_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Get the user that payer the factor.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Get the factor coupon.
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Get the factor representation.
     */
    public function representation()
    {
        return $this->belongsTo(Representation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * Get the installments for the factor.
     */
    public function installments()
    {
        return $this->hasMany(FactorItemInstallment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Get the factor organization.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseInvoiceFactors()
    {
        return $this->hasMany(PurchaseInvoiceFactor::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * Get the comments for the factor.
     */
    public function comments()
    {
        return $this->hasMany(CommentFactor::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * Get the registeration factor factorItems.
     * this relationship is used cash factorItems
     * this relationship is used in factors with register title
     */
    public function factorItems()
    {
        return $this->hasMany(FactorItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Get the factorItem that owns the factor.
     * this relationship is used cash factorItems
     * this relationship is used in factors with prepayment or price_differnce or extra_pay or installment title
     */
    public function factorItem()
    {
        return $this->belongsTo(FactorItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Get the totalFactorItem that owns the factor.
     * this relationship is used credit factorItems
     * this relationship is used in factors with prepayment or price_differnce or extra_pay or installment title
     */
    public function totalFactorItem()
    {
        return $this->belongsTo(TotalFactorItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * Get the registeration factor totalFactorItem.
     * this relationship is used credit factorItems
     * this relationship is used in factors with register title
     */
    public function totalFactorItemReg()
    {
        return $this->hasOne(TotalFactorItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Get the factor callcenter.
     */
    public function task()
    {
        return $this->belongsTo(Callcenter::class, 'task_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Get the address that stored in factor for send order products.
     */
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ghorfeOnlineLists()
    {
        return $this->belongsToMany(GhorfeOnlineList::class);
    }
    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $resnumber = request('resnumber');
        $phone = request('phone');
        $family = request('family');
        $name = request('name');
        $service = request('service');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $status = request('status');
        $title = request('title');
        $title_pay = request('title_pay');
        $type = request('type');
        $type_pay = request('type_pay');
        $confirm = request('confirm');
        $callcenter = request('user');
        $old_callcenter = request('old_callcenter');
        $representation = request('representation');
        $organization = request('organization');
        $productCategory = request('product_category');
        $serviceCategory = request('service_category');
        $subServiceCategory = request('sub_service_category');
        $serviceItems = request('service_items');
        $province = request('province');
        $city = json_decode(request('city'));

        $query->whereDoesntHave('ghorfeOnlineLists');

        $query->where('price', '!=', 0);
        if (auth()->user()->hasPermission('factor-detail')) {
            if (auth()->user()->center_id) {
                $center = auth()->user()->center_id;
                $query->whereHas('factorItem', function ($query) use ($center) {
                    $query->where('center_id', $center);
                });
                $query->whereHas('factorItem', function ($query) use ($center) {
                    $query->where('status_factor', '!=', 0);
                });
            }
            if (auth()->user()->admin_representation) {
                $query->where('representation_id', auth()->user()->admin_representation->id);
            }
            if (isset($representation) && trim($representation) != 'all') {
                if (trim($representation) == 'center') {
                    $query->where('representation_id', null);
                } else {
                    $query->where('representation_id', $representation);
                }
            }
        } else {
            if (auth()->user()->hasRole('organ-manager')) {
                $query->whereStatus(true)->whereIn('organization_id', auth()->user()->admin_organization->organ_code);
            } else {
                $query->whereHas('callcenter',function ($query){
                    $query->whereHas('employee',function ($query){
                        $query->where('parent_id',auth()->user()->employee->id);
                    });
                })->orWhere('callcenter_id', auth()->user()->id);
            }
        }

        if (isset($family) && trim($family) != '') {
            $query->whereHas('user', function ($query) use ($family) {
                $query->where('family', 'LIKE', '%' . $family . '%');
            });
        }
        if (isset($name) && trim($name) != '') {
            $query->whereHas('user', function ($query) use ($name) {
                $query->where('name', 'LIKE', '%' . $name . '%');
            });
        }
        if (isset($phone) && trim($phone) != '') {
            $query->whereHas('user', function ($query) use ($phone) {
                $query->where('phone', $phone);
            });
        }
        if (isset($status) && trim($status) != '' && $status != 'all') {
            $query->whereStatus($status);
            if($status == 1)
            {
                $query->whereIn('type' , Factor::getCashPaymentType());
            }
        }
        if (isset($title) && trim($title) != '' && $title != 'all') {
            $query->where('title', 'LIKE', $title);
        }
        if (isset($title_pay) && trim($title_pay) != '' && $title_pay != 'all') {
            $query->where('title', 'LIKE', $title_pay);
        }
        if (isset($type) && !is_null($type)  && is_array($type) ) {
            $query->whereIn('type', $type);
        }
        if (isset($type_pay) && trim($type_pay) != '' && $type_pay != 'all') {
            $query->where('type', 'LIKE', $type_pay);
        }
        if (isset($confirm) && trim($confirm) != '' && $confirm != 'all') {
            $query->whereConfirm($confirm);
        }
        if (isset($service) && trim($service) != '' && $service != 'all') {
            $service = ServiceItem::find($service)->service->id;
            $query->whereHas('factorItem', function ($query) use ($service) {
                $query->where('service_id', $service);
            });
        }

        if (isset($organization) && trim($organization) != 'all') {
            $query->where('organization_id', $organization);
        }

        if (isset($resnumber) && trim($resnumber) != '') {
            $query->where('resnumber', $resnumber);
        }
        if (isset($callcenter) && trim($callcenter) != '' && $callcenter != 'all') {
            if ($callcenter == 'site') {
                $query->where('callcenter_id', null);
            } else {
                $query->where('callcenter_id', $callcenter);
            }
        }
        if (isset($old_callcenter) && trim($old_callcenter) != '' && $old_callcenter != 'all') {
            $query->where('callcenter_id', $old_callcenter);
        }
        if (isset($startDate) && trim($startDate) != '' && isset($endDate) && trim($endDate) != '') {
            $query->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
        }
        if (isset($subServiceCategory) && $subServiceCategory != 'all'){
            $query->where(function ($query) use ($subServiceCategory){
                $query->whereHas('factorItems', function ($query) use ($subServiceCategory) {
                    $query->where('factoritemable_type', '=', Service::class)->where('factoritemable_id',$subServiceCategory);
                });
        });
        }

        if ( (is_null($subServiceCategory) || $subServiceCategory == 'all' ) && isset($serviceCategory) && $serviceCategory!='all' ){
            $serviceCategory=Category::find($serviceCategory);
            $query->where(function ($query) use ($serviceCategory){
                $query->whereHas('factorItems', function ($query) use ($serviceCategory) {
                    $query->where('factoritemable_type', '=', Service::class)->whereIn('factoritemable_id',$serviceCategory->services->pluck('id')->toArray());
                });
        });
        }


        if (isset($serviceItems) && is_array($serviceItems)) {
            $query->whereHas('factorItems', function ($query) use ($serviceItems) {
                $query->whereIn('service_item_id', $serviceItems);
            });
        }
        if (isset($productCategory) && trim($productCategory) != 'all') {
            $query->whereHas('factorItems', function ($query) use ($productCategory) {
                $query->where('factoritemable_type', '=', Category::class)->where('factoritemable_id', $productCategory);
            });
        }

        if ($province!='all' && $province != null) {
            $query->whereHas('user', function ($query) use ($province) {
                $query->where('province_id', $province);
            });
        }
        if ($province!='all' && $city != null) {
            $query->whereHas('user', function ($query) use ($city) {
                $query->where('city_id', $city);
            });
        }

        return $query;
    }

    /**
     * @param Factor $item
     * @return string
     * Get the factor items and factor title.
     * items include services or categories
     */
    public static function setItems(Factor $item)
    {
        $temp = [];
        foreach ($item->factorItems as $factor_item) {
            array_push($temp, $factor_item->factoritemable->title);
        }
        isset($item->factorItem) ? array_push($temp, $item->factorItem->factoritemable->title) : '';
        array_push($temp, $item::TITLE[$item->title]);
        return implode(' - ', $temp);
    }
    /**
     * @param Factor $item
     * @return string
     * Get the factor items and factor title.
     * items include services or categories
     */
    public static function setServiceItems(Factor $item)
    {
        $temp = [];
        foreach ($item->factorItems as $factor_item) {
            array_push($temp, $factor_item->factoritemable->title);
        }
        return implode(' - ', $temp);
    }
    /**
     * @param Factor $item
     * @return void
     * save of representation income from payment of the registration factor
     */
    public static function invoiceRevenueSharing(Factor $item)
    {
        if ($item->represntation_id && $item->title == 'register') {
            //get superadmin representation
            $admin_users = User::where('level', 'admin')->where('admin_representation_id', $item->representation_id)->get();
            foreach ($admin_users as $u) {
                if ($u->hasRole('superadmin-representation')) {
                    $user = $u;
                }
            }
            $factor_item = $item->factorItem;
            $performance = new UserPerformance();
            $performance->price = $factor_item->total_price / 10;
            $performance->user_id = $user->id;
            $performance->callcenter_id = auth()->user()->id;
            $performance->description = 'پرداخت از هزینه تشکیل پرونده';
            $performance->date = Carbon::now();
            $performance->factor_item_id = $factor_item->id;
            $performance->save();

            $resNum = rand(12, 99) . substr(Carbon::now()->timestamp, 6, 11) . substr(auth()->user()->phone, 7, 11);
            WalletTransaction::create([
                'user_id' => $performance->user_id,
                'reason_id' => $performance->id,
                'resnumber' => $resNum,
                'price' => $performance->price * ($user->employee->credit / 100),
                'status' => '1',
                'type' => '1',
                'created_at' => $performance->date,
            ]);
        }
    }

    /**
     * @param $status
     * @return string
     * get factor status text
     */
    public static function status($status)
    {
        return [
            '0' => 'در انتظار پرداخت',
            '1' => 'پرداخت شده',
            '2' => 'عودت پرداخت',
            '3' => 'منقضی شده',
            '4' => 'عودت واریزی',
            '5' => 'واریز شده',
        ][$status];
    }
    /**
     * @return array
     * get the type of payments array that have actually been paid
     */
    public static function getCashPaymentType()
    {
        return ['online' , 'card' , 'card_reader' , 'cash' , 'wallet' , 'account' , 'check'];
    }
    /**
     * @return array
     * get the type of payments array that is actually not paid
     */
    public static function getCreditPaymentType()
    {
        return ['credit' , 'overpayment' , 'deduction_price_difference' , 'increase_credit' , 'in_factor_item'];
    }

    /**
     * @param Factor $item
     * @return void
     * save payment after paid the factor.
     * deduction from the factorItem's debt
     */
    public static function savePayment(Factor $item)
    {
        //if not paid factor
        if ($item->status != 1) {
            return response(['message' => 'این فاکتور پرداخت نشده است.']);
        }

        //get total factor item for installments factor
        if ($item->totalFactorItem) {
            $total_factor_item = $item->totalFactorItem;
            $total_factor_item_status = $total_factor_item->total_factor_item_status_id == 0 ? 4 : $total_factor_item->total_factor_item_status_id;
        }

        if ($item->callcenter_id) {
            self::setInviterPercentage($item);
        }
        //set price paid with title factor
        switch ($item->title) {
            case 'register':
                self::createAttributes($item);
                self::setPercentageAllocationEmployee($item);
                self::closeSimilars($item);
                self::factorTaskChenge($item);
                self::setFactorItemRepresentation($item);
                self::refferalFactorItemToCenter($item);
                $supporter_user = self::setSupporter($item);
                if ( $supporter_user){
                    self::smsRegisterPaymentSuccess($item,$supporter_user);
                }
                break;
            case 'factor':
                //cash purchase
                self::setPayPriceFactor($item);
                break;
            case 'prepayment':
                self::setPayPrepayment($item, $total_factor_item, $total_factor_item_status);
                break;
            case 'price_difference':
                self::setPayPriceDiff($item);
                break;
            case 'installment':
                self::setPayInstallment($item, $total_factor_item, $total_factor_item_status);
                break;
            case 'wallet':
                self::rechargeWallet($item);
                break;
            case 'registeration_debt':
                self::setPayRegisterationDebt($item , $total_factor_item,);
            default:
                '';
        }
    }
    /**
     * @param Factor $item
     * @return void
     * close similar factors && similar tasks
     */
    private static function closeSimilars(Factor $item)
    {
        $factor_item_ables = $item->factorItems->pluck('factoritemable_id');
        Factor::where('id', '!=', $item->id)->where('user_id', $item->user_id)->where('status', 0)
            ->whereIn('title', ['register','factor'] )->get()->each(function ($item) use ($factor_item_ables) {
                if ($item->factorItems->whereIn('factoritemable_id', $factor_item_ables)) {
                    $item->update(['status' => 3, 'expired_at' => now()]);
                    if (isset($item->task) && $item->task->status != 3) {
                        $item->task->update([
                            'status' => 8
                        ]);
                    }
                }
            });
    }
    /**
     * @param Factor $item
     * @return void
     * create factorItem attribute items or totalFactorItem attribute items after pay registeration factor
     */
    private static function createAttributes(Factor $item)
    {
        $item->factorItems->map(function ($factor_item) {
            if (!$factor_item->property()->exists()) {
                $factor_item->property()->create();
            }
        });

        if ($item->totalFactorItemReg) {
            if (!$item->totalFactorItemReg->property()->exists()) {
                $item->totalFactorItemReg->property()->create();
            }
        }
    }
     /**
     * @param Factor $item
     * @return void
     * set emplloyee performance from percentageAllocation employee
     */
    private static function setPercentageAllocationEmployee(Factor $item)
    {
        $item->factorItems->map(function ($factor_item) {
            //set emplloyee performance from percentageAllocation employee
            PercentageAllocationEmployee::setUserPerformance($factor_item , 'register_price' , $factor_item->factor->price , now() , null);
        });
    }
    /**
     * @param Factor $item
     * @return void
     * store change on factor task if exist
     */
    public static function factorTaskChenge(Factor $item)
    {
        if($item->task)
        {
            $item->task->update(['user_id' => $item->callcenter_id , 'status' => 3]);
        }
    }
    /**
     * @param Factor $item
     * @return void
     */
    public static function setFactorItemRepresentation(Factor $item)
    {
        if($item->representation && $item->factorItems)
        {
            $item->factorItems->map(function ($item) {
                FactorItem::createRepresentation($item , $item->factor->representation_id);
            });
        }
    }
    /**
     * @param \App\Models\Factor $factor
     * @param mixed $center
     * @return void
     */
    private static function refferalFactorItemToCenter(Factor $factor)
    {
        $factor->factorItems()->each(function ($factorItem) {
            if($factorItem->center_id)
            {
                $factorItem->update([
                'status_factor' => 17,
                'date' => now(),
            ]);
            if (optional($factorItem->factoritemable)->session == 1) {
                Session::create([
                    'status' => 3,
                    'user_id' => $factorItem->factor->callcenter_id,
                    'factor_item_id' => $factorItem->id,
                    'date' => now(),
                    'description_before' => 'درخواست مشاوره - ثبت شده توسط مرکز',
                ]);
            }
            //send sms to the center manager
            event(new referralCenterNotification($factorItem->center));
            }
        });
    }
    /**
     * @param Factor $item
     * @return void
     * this function is used in pay cash factoItems factor
     */
    private static function setPayPriceFactor(Factor $item)
    {
        if ($item->factorItems->count()) {
            foreach ($item->factorItems as $factor_item) {
                if ($factor_item->purchase_type == 0) {
                    //factor price for purchase type online
                    $factor_item->property->update([
                        'prepayment_debt' => 0,
                        'prepayment_count' => 0,
                    ]);
                } else {
                    //register price for purchase type person
                    self::closeSimilars($item);
                    self::factorTaskChenge($item);
                    if ($factor_item->property()->exists()) {
                        $factor_item->property->update([
                            'prepayment_debt' => $factor_item->property->prepayment_debt - $factor_item->price,
                            'prepayment_count' => $factor_item->property->prepayment_count - 1
                        ]);
                    } else {
                        $factor_item->property()->create([
                            'prepayment_debt' => 0,
                            'prepayment_price' => $factor_item->price,
                            'prepayment_count' => 1,
                            'prepayment_num' => 1,
                        ]);
                    }

                }
            }
        }
        //factor price for purchase type person
        elseif ($item->factorItem) {
            $item->factorItem->property->update([
                'prepayment_debt' => $item->factorItem->property->prepayment_debt - $item->price,
                'prepayment_count' => $item->factorItem->property->prepayment_count - 1
            ]);
        }
    }
    /**
    * @param Factor $item
    * @param TotalFactorItem $total_factor_item
    * @param $total_factor_item_status
    * @return void
    */
    private static function setPayPrepayment(Factor $item, TotalFactorItem $total_factor_item, $total_factor_item_status)
    {
        $total_factor_item->update([
            'total_factor_item_status_id' => $total_factor_item_status,
        ]);
        $total_factor_item->property->update([
            'prepayment_debt' => $total_factor_item->property->prepayment_debt - $item->price,
            'prepayment_count' => $total_factor_item->property->prepayment_count - 1
        ]);
    }
    /**
     * @param Factor $item
     * @return void
     */
    private static function setPayPriceDiff(Factor $item)
    {
        $factor_item = $item->factorItem;
        $credit = $factor_item->credit + $item->price;
        $factor_item->update([
            'credit' => $credit,
        ]);
        $factor_item->property->update([
            'price_difference_debt' => $factor_item->property->price_difference_debt - $item->price
        ]);

        $factor_item->totalFactorItem->update(['credit' => $factor_item->totalFactorItem->credit + $item->price]);
        $factor_item->totalFactorItem->property->update([
            'price_difference_debt' => $factor_item->totalFactorItem->property->price_difference_debt - $item->price
        ]);
    }
    /**
     * @param Factor $item
     * @param TotalFactorItem $total_factor_item
     * @param $total_factor_item_status
     * @return void
     */
    private static function setPayInstallment(Factor $item, TotalFactorItem $total_factor_item, $total_factor_item_status)
    {
        $wage = 0;
        $factor_items = $total_factor_item->factorItems;
        $debt_price = isset($total_factor_item->property->price_difference_installment) ? $total_factor_item->property->price_difference_installment : 0;
        $installment = TotalFactorItemInstallment::where('total_factor_item_id', $total_factor_item->id)->where('status', 0)->orderBy('id', 'ASC')->first();
        if ($installment && $item->price + $debt_price >= $installment->price) {
            (isset($installment->factor_id) && $installment->factor_id != $item->id) ? $installment->factor->update(['expired_at' => now(), 'status' => 3]) : '';

            $installment->update([
                'factor_id' => $item->id,
                'status' => 1,
                'pay_date' => $item->created_at,
            ]);

            foreach ($factor_items as $factor_item) {
                $factor_item->factorItemInstallments()->where('level', $installment->level)->update(['status' => true]);
                $total_price = $factor_item->total_price + $factor_item->discount;
                $wage = $wage + (CalculationHelper::calculateWage($factor_item , $total_price , $factor_item->property->prepayment_price , $factor_item->installments_num)/$factor_item->installments_num);
            }
            //set emplloyee performance from percentageAllocation employee
            PercentageAllocationEmployee::setUserPerformance($factor_item , 'company_profit' , $wage , now() , null);

            $debt_price = $debt_price + $item->price - $installment->price;
            $installment = TotalFactorItemInstallment::where('total_factor_item_id', $total_factor_item->id)->where('status', 0)->orderBy('id', 'ASC')->first();
            for ($j = 0; $installment && $debt_price >= $installment->price; $j++) {
                $installment->update([
                    'factor_id' => $item->id,
                    'status' => 1,
                    'pay_date' => $item->created_at,
                ]);
                //set emplloyee performance from percentageAllocation employee
                PercentageAllocationEmployee::setUserPerformance($factor_item , 'company_profit' , $wage , now() , null);

                foreach ($factor_items as $factor_item) {
                    $factor_item->factorItemInstallments()->where('level', $installment->level)->update(['status' => true]);
                }
                $debt_price = $debt_price - $installment->price;
                $installment = TotalFactorItemInstallment::where('total_factor_item_id', $total_factor_item->id)->where('status', 0)->orderBy('id', 'ASC')->first();
            }
        } else {
            $debt_price = $debt_price + $item->price;
        }

        //check installments
        TotalFactorItem::checkInstallments($total_factor_item);

        $total_factor_item->update([
            'total_factor_item_status_id' => $total_factor_item_status,
        ]);
        $total_factor_item->property->update([
            'price_difference_installment' => $debt_price
        ]);
    }
    /**
     * @param Factor $item
     * @return void
     */
    private static function rechargeWallet(Factor $item)
    {
        $user = $item->user;
        $wallet = $user->wallet + $item->price;
        $user->update([
            'wallet' => $wallet,
        ]);
    }
    /**
     * @param Factor $item
     * @param TotalFactoItem $total_factor_item
     * @return void
     */
    private static function setPayRegisterationDebt(Factor $item, TotalFactorItem $total_factor_item)
    {
        $registeration_debt = $total_factor_item->property->registeration_debt;
        $total_factor_item->property->update(['registeration_debt' => $registeration_debt - $item->price]);
    }
    /**
     * @param Factor $item
     * @return void
     * set innviter percentage after paid the factor if this factor has an inviter.
     */
    private static function setInviterPercentage(Factor $item)
    {
        $factor_items = $item->factorItems;

        foreach ($factor_items as $item) {
            $identifier_code = $item->callcenter_id;

            if (UserPerformance::hasUserInviteByType($item->factoritemable_id, $item->factoritemable_type, $identifier_code, $item->factor->user_id)) {
                $invite_category_percent = UserPerformance::hasUserInviteByType($item->factoritemable_id, $item->factoritemable_type, $identifier_code, $item->factor->user_id);
                UserPerformance::createUserPerformance($item->id, $identifier_code, $invite_category_percent, $item->factor->user_id);
            }
        }
    }

    /**
     * @param Factor $item
     * @return void
     * return payment after delete factor
     */
    public static function returnPaymentBydeleteFactor(Factor $item)
    {
        if($item->status == 1 || $item->status == 2)
        {
            if ($item->callcenter_id) {
                self::deducateInviterPercentage($item);
            }

            //return the factor price with check payment type factor
            self::returnPriceByPaymentType($item);

            //retrun the factor price with check title factor
            self::returnPriceByTitle($item);
        }
    }
    /**
     * @param Factor $item
     * @return
     */
    private static function returnPriceByPaymentType(Factor $item)
    {
        //get total factor item for installments factor
        if ($item->totalFactorItem) {
            $total_factor_item = $item->totalFactorItem;
        }
        switch($item->type)
        {
            case 'wallet' :
                self::increaseUserWallet($item);
                break;
            case 'credit' :
                self::increaseUserCredit($item);
                break;
            case 'overpayment' :
                self::increaseTotalFactorItemOverPayment($item , $total_factor_item);
                break;
            case 'increase_credit' :
                self::decreaseUserCredit($item);
                break;
            case 'deduction_price_difference' :
                self::increaseFactorItemPriceDiff($item);
                break;
            default:
                '';
        }
    }
    /**
     * @param Factor $item
     * @return void
     */
    private static function returnPriceByTitle(Factor $item)
    {
        //get total factor item for installments factor
        if ($item->totalFactorItem) {
            $total_factor_item = $item->totalFactorItem;
        }
        switch ($item->title) {
            case 'register':
                self::setUnpaidTask($item);
                break;
            case 'factor':
                //cash purchase
                $item->status == 2 ? self::setPayPriceFactor($item) : self::deducatePayPriceFactor($item);
                break;
            case 'prepayment':
                $item->status == 2 ? self::setPayPrepayment($item, $total_factor_item, $total_factor_item->total_factor_item_status_id) : self::deducatePayPrepayment($item, $total_factor_item);
                break;
            case 'price_difference':
                $item->status == 2 ? self::setPayPriceDiff($item) : self::deducatePayPriceDiff($item);
                break;
            case 'installment':
                self::deducatePayInstallment($item, $total_factor_item);
                break;
            case 'wallet':
                self::deducateChargeWallet($item);
                break;
            default:
                '';
        }
    }
    /**
     * @param Factor $item
     * @return void
     * deducate innviter percentage after delete the factor if this factor has an inviter.
     */
    private static function deducateInviterPercentage(Factor $item)
    {
        $factor_items = $item->factorItems;

        foreach ($factor_items as $item) {
            $identifier_code = $item->callcenter_id;

            if (UserPerformance::hasUserInviteByType($item->factoritemable_id, $item->factoritemable_type, $identifier_code, $item->factor->user_id)) {
                $invite_category_percent = UserPerformance::hasUserInviteByType($item->factoritemable_id, $item->factoritemable_type, $identifier_code, $item->factor->user_id);
                UserPerformance::createUserPerformance($item->id, $identifier_code, $invite_category_percent, $item->factor->user_id);
            }
        }
    }
    /**
     * @param Factor $item
     * @return void
     */
    private static function setUnpaidTask(Factor $item)
    {
        $item->task ? $item->task->update(['status' => 9]) : '';
    }
    /**
     * @param Factor $item
     * @return void
     * this function is used in delete cash factoItems factor
     */
    private static function deducatePayPriceFactor(Factor $item)
    {
        if ($item->factorItems->count()) {
            foreach ($item->factorItems as $factor_item) {
                if ($factor_item->purchase_type == 0) {
                    //factor price for purchase type online
                    if ($factor_item->property()->exists()) {
                        $$factor_item->property->update([
                            'prepayment_debt' => $factor_item->total_price,
                            'prepayment_price' => $factor_item->total_price,
                            'prepayment_count' => 1,
                        ]);
                    } else {
                        $factor_item->property()->create([
                            'prepayment_debt' => $factor_item->total_price,
                            'prepayment_price' => $factor_item->total_price,
                            'prepayment_count' => 1,
                            'prepayment_num' => 1,
                        ]);
                    }

                } else {
                    //register price for purchase type person
                    if ($factor_item->property()->exists()) {
                        $factor_item->property->update([
                            'prepayment_debt' => $factor_item->property->prepayment_debt + $factor_item->price,
                            'prepayment_count' => $factor_item->property->prepayment_count + 1
                        ]);
                    } else {
                        $factor_item->property()->create([
                            'prepayment_debt' => $factor_item->price,
                            'prepayment_price' => $factor_item->price,
                            'prepayment_count' => 1,
                            'prepayment_num' => 1,
                        ]);
                    }

                }
            }
        } //factor price for purchase type person
        elseif ($item->factorItem) {
            $item->factorItem->property->update([
                'prepayment_debt' => $item->factorItem->property->prepayment_debt + $item->price,
                'prepayment_count' => $item->factorItem->property->prepayment_count + 1
            ]);
        }
    }
    /**
     * @param Factor $item
     * @param TotalFactorItem $total_factor_item
     * @return void
     * this function is used in delete prepayment totalFactoItems factor
     */
    private static function deducatePayPrepayment(Factor $item, TotalFactorItem $total_factor_item)
    {
        $total_factor_item->property->update([
            'prepayment_debt' => $total_factor_item->property->prepayment_debt + $item->price,
            'prepayment_count' => $total_factor_item->property->prepayment_count + 1
        ]);
    }
    /**
     * @param Factor $item
     * @return void
     * this function is used in delete differencePrice factoItems factor
     */
    private static function deducatePayPriceDiff(Factor $item)
    {
        $factor_item = $item->factorItem;
        $credit = $factor_item->credit - $item->price;
        $factor_item->update([
            'credit' => $credit,
        ]);
        $factor_item->property->update([
            'price_difference_debt' => $factor_item->property->price_difference_debt + $item->price
        ]);

        $factor_item->totalFactorItem->update(['credit' => $factor_item->totalFactorItem->credit - $item->price]);
        $factor_item->totalFactorItem->property->update([
            'price_difference_debt' => $factor_item->totalFactorItem->property->price_difference_debt + $item->price
        ]);
    }
    /**
     * @param Factor $item
     * @param TotalFactorItem $total_factor_item
     * @return void
     * this function is used in delete installment totalFactorItems factor
     */
    private static function deducatePayInstallment(Factor $item, TotalFactorItem $total_factor_item)
    {
        $factor_items = $total_factor_item->factorItems;
        $extra_pay_price = isset($total_factor_item->property->price_difference_installment) ? $total_factor_item->property->price_difference_installment : 0;
        $paid_installment = TotalFactorItemInstallment::where('total_factor_item_id', $total_factor_item->id)->where( 'status', 1 )->orderBy( 'id', 'DESC' )->first();
        if ($paid_installment && $extra_pay_price - $item->price < 0) {
            $return_paid_price = $item->price - $extra_pay_price;
            $paid_installment->update( [
                'factor_id' => null,
                'status' => 0,
                'pay_date' => null,
            ] );
            foreach ($factor_items as $factor_item) {
                $factor_item->factorItemInstallments()->where('level', $paid_installment->level)->update(['status' => 0]);
            }
            $return_paid_price = $return_paid_price - $paid_installment->price;
            $extra_pay_price = $return_paid_price < 0 ? $paid_installment->price - $return_paid_price : 0;
            for ( $j = 0; $return_paid_price > 0 ; $j++ ) {
                $paid_installment = TotalFactorItemInstallment::where('total_factor_item_id', $total_factor_item->id)->where( 'status', 1 )->orderBy( 'id', 'DESC' )->first();
                $paid_installment->update( [
                    'factor_id' => null,
                    'status' => 0,
                    'pay_date' => null,
                ] );
                foreach ($factor_items as $factor_item) {
                    $factor_item->factorItemInstallments()->where('level', $paid_installment->level)->update(['status' => 0]);
                }
                $return_paid_price = $return_paid_price - $paid_installment->price;
                $extra_pay_price = $return_paid_price < 0 ? $paid_installment->price - $return_paid_price : 0;
            }
        }
        else{
            $extra_pay_price = $extra_pay_price - $item->price;
        }

        //check installments
        TotalFactorItem::checkInstallments($total_factor_item);
        $total_factor_item->property->update([
            'price_difference_installment' => $extra_pay_price
        ]);
    }
    /**
     * @param Factor $item
     * @return void
     * this function is used in delete charge wallet factor
     */
    private static function deducateChargeWallet(Factor $item)
    {
        $user = $item->user;
        $wallet = $user->wallet - $item->price;
        $user->update([
            'wallet' => $wallet,
        ]);
    }
    /**
     * @param Factor $item
     * @return void
     */
    private static function increaseUserWallet(Factor $item)
    {
        $user = $item->user;
        $user->update(['wallet' => $user->wallet + $item->price]);
    }
    /**
     * @param Factor $item
     * @return void
     */
    private static function increaseUserCredit(Factor $item)
    {
        $user = $item->user;
        $user->update(['credit' => $user->credit + $item->price]);
        $user->creditDetails()->create([
            'price' => $item->price,
            'registrar_id' => auth()->user()->id,
            'type' => 'increase',
            'description' => 'عودت'.' '.Factor::TITLE[$item->title],
        ]);
    }
    /**
     * @param Factor $item
     * @param TotalFactorItem $total_factor_item
     * @return void
     */
    private static function increaseTotalFactorItemOverPayment(Factor $item , TotalFactorItem $total_factor_item)
    {
        $total_factor_item_property = $total_factor_item->property;
        $total_factor_item_property->update([
            'price_difference_installment' => $total_factor_item_property->price_difference_installment + $item->price
        ]);
    }
    /**
     * @param Factor $item
     * @return void
     */
    private static function decreaseUserCredit(Factor $item)
    {
        $user = $item->user;
        $user->update(['credit' => $user->credit - $item->price]);
        $user->creditDetails()->create([
            'price' => $item->price,
            'registrar_id' => auth()->user()->id,
            'type' => 'decrease',
            'description' => Factor::TITLE[$item->title],
        ]);
    }
    /**
     * @param Factor $item
     * @return void
     */
    private static function increaseFactorItemPriceDiff(Factor $item)
    {
        $factor_item_property = $item->factorItem->property;
        $factor_item_property->update([
            'price_difference_debt' => $factor_item_property->price_difference_debt - $item->price,
        ]);
    }
    /**
     * check exist similar factors && return factor without create new factor
     * @param mixed $factor_items
     * @param User $user
     * @return mixed
     */
    public static function checkExistSimilarFactor($factor_items , User $user)
    {

        $result = Factor::where('user_id', $user->id)->where('status', 0)
        ->where('expired_at','>',now())->whereIn('title', ['register' , 'factor'])->get()->map(function ($item) use ($factor_items) {
            $result['status'] = false;
            foreach($factor_items as $factor_item)
            {
                if (!$item->factorItems()->where('financial_plans_type_id' , $factor_item['financial_plans_type'] )->where('factoritemable_id', $factor_item['service'])->where('factoritemable_type' , Service::class)->exists()) {
                    $result['status'] = false;
                    return $result;
                }
                else{
                    $result['status'] = true;
                    $result['factor'] = $item;
                }
            }
            // foreach($category_items as $category_item)
            // {
            //     if (!$item->factorItems()->where('factoritemable_id', $category_item)->where('factoritemable_type' , Category::class)->exists()) {
            //         $result['status'] = false;
            //         return $result;
            //     }
            //     else{
            //         $result['status'] = true;
            //         $result['factor'] = $item;
            //     }
            // }
            return $result;
        });
        foreach($result as $item)
        {
            if($item['status'] == true)
            {
                return $item['factor'];
            }
        }
        return '';
    }
    /**
     * @param mixed $items
     * @return array
     * set factor items in array
     * factor items include financialPlansTypes && services
     */
    public static function setFactorItemsInArray($items)
    {
        $factor_items = [];
        foreach($items as $item)
        {
            $factor_items[] = [
                'service' => $item[ 'service_id' ],
                'financial_plans_type' => $item[ 'financial_plans_type_id' ],
            ];
        }
        return $factor_items;
    }
}

