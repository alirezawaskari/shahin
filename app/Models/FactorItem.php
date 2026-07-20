<?php

namespace App\Models;

use App\Events\referralCenterNotification;
use App\Events\smsService;
use App\Helpers\CalculationHelper;
use App\Helpers\FilterHelper;
use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Illuminate\Support\Facades\View;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
class FactorItem extends Model implements AuditableContract, HasMedia
{
    use Auditable;
    use SoftDeletes;
    use InteractsWithMedia;
    use \App\Models\Concerns\AdoptsLegacyMedia;

    /**
     * Touch-to-migrate: legacy paths embedded in these JSON columns -> the documents
     * collection. factor_values is nested (image arrays sit next to plain text/ids); only
     * values resolving to a real file under public/ are adopted. The columns stay untouched.
     */
    protected function legacyArrayMediaMap(): array
    {
        return [
            'factor_values' => self::DOCUMENTS_COLLECTION,
            'factor_image'  => self::DOCUMENTS_COLLECTION,
        ];
    }

    /**
     * Spatie collection that permanently holds the ajax-uploaded document images for
     * this factor item (recommendations section, checkout file/multi_file uploads, …).
     * Additive (NOT singleFile) — every upload is kept. One bucket for all form fields:
     * each media carries a 'field' custom property naming the beforeform it belongs to,
     * so separation is by that property rather than by collection.
     */
    const DOCUMENTS_COLLECTION = 'factor_documents';

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::DOCUMENTS_COLLECTION)->useDisk('public');
    }

    /**
     * Urls of the file(s) uploaded for beforeform $field: Spatie media if any, else the
     * legacy path(s) still in factor_values. Absolute urls — render {{ $url }} as-is.
     *
     * Use for BOTH 'file' (take the first) and 'multi_file' (loop) form types.
     */
    public function documentUrls(string $field): array
    {
        return $this->mediaUrlsForField(self::DOCUMENTS_COLLECTION, 'factor_values', $field);
    }

    /**
     * Same as documentUrls(), but each entry carries ['url', 'legacy', 'media_id'] so a view
     * can flag a not-yet-adopted file ("با ذخیره به سیستم جدید منتقل می‌شود").
     */
    public function documentItems(string $field): array
    {
        return $this->mediaItemsForField(self::DOCUMENTS_COLLECTION, 'factor_values', $field);
    }

    /** First url for a single-'file' beforeform field, or '' when nothing is uploaded. */
    public function documentUrl(string $field): string
    {
        return $this->documentUrls($field)[0] ?? '';
    }

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
        'values' => 'array',
        'factor_values' => 'array',
        'factor_image' => 'array',
        'contract_values' => 'array',
        'prepayment' => 'array',
        'confirms' => 'array',
        'price_difference' => 'array',
    ];

    /**
     *types of guarantees for factorItem
     */
    const GuaranteeTYPE = [
        'check' => 'چک',
        'certificate' => 'گواهی کسراز حقوق',
        'promissorynote' => 'سفته',
    ];

    /**
     *purchase type of service
     */
    const PurchaseTYPE = [
        '0' => 'آنلاین',
        '1' => 'حضوری',
    ];

    /**
     *type of pay many by customer
     */
    const TYPE = [
        '0' => 'نقدی',
        '1' => 'اقساطی',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function factoritemable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function histories()
    {
        return $this->morphMany(History::class , 'historiable');
    }

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
        return $this->hasMany(Factor::class)->orderby('created_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sessions()
    {
        return $this->hasMany(Session::class)->orderBy('date');
    }
    /**
     * Summary of representation
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<RepresentationFactorItem, FactorItem>
     */
    public function representation()
    {
        return $this->hasOne(RepresentationFactorItem::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userPerformances()
    {
        return $this->hasMany(UserPerformance::class)->orderBy('date');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supporter()
    {
        return $this->belongsTo(User::class, 'supporter_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supervisor()
    {
        return $this->belongsTo(FactorItem::class, 'supervisor_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function factorItemInstallments()
    {
        return $this->hasMany(FactorItemInstallment::class)->orderBy('date', 'ASC');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(FactorItemProduct::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function serviceTariffs()
    {
        return $this->belongsToMany(ServiceTariff::class)->withTimestamps()->whereNull('factor_item_service_tariff.deleted_at')->withPivot('value', 'id', 'description' , 'session_number', 'treatment_duration', 'user_id', 'status', 'price', 'discount' , 'grouping');
    }

    /**
     *
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->resnumber = self::makeUniqueTracking();
        });
    }

    /**
     * @return string
     */
    private static function makeUniqueTracking()
    {
        do {
            $resNumber = rand(12, 99) . substr(Carbon::now()->timestamp, 6, 11) . substr(auth()->user()->phone, 7, 11);
            $found = self::where('resnumber', $resNumber)->first();
        } while (!is_null($found));
        return $resNumber;
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
     */
    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financialPlansType()
    {
        return $this->belongsTo(FinancialPlansType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function totalFactorItem()
    {
        return $this->belongsTo(TotalFactorItem::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function property()
    {
        return $this->morphOne(FactorItemAttributes::class, 'factor_item_attributable');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function contractView()
    {
        return $this->morphOne(ContractView::class, 'contract');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
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
        $service_tariff = request('service_tariff');
        $has_session = request('has_session');
        $service = request('service');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $date = request('date');
        $status = request('status');
        $status_mali = request('status_mali');
        $type = request('type');
        $purchase_type = request('purchase_type');
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
            $query = FilterHelper::getDataByCheckGhorfeExistOrNot($query , 'belongsToMany' , null);
        });

        $query->whereHas('factor', function ($query) {
            $query->where('status', 1);
        });

        if (auth()->user()->admin_representation) {
            $query->whereHas('representation', function ($query){
                $query->where('representation_id', auth()->user()->admin_representation_id);
            });
        }
        if(isset($has_payment) && trim($has_payment) != '' && $has_payment != 'all')
        {
            if($has_payment == 'true')
            {
                $query->whereRaw('id = supervisor_id')->where(function ($query){
                    $query->whereHas('totalFactorItem', function ($query){
                        $query->whereHas('factors', function ($query){
                            $query->where('status', 1);
                        });
                    })->orWhereHas('factors', function ($query){
                        $query->where('status', 1);
                    });
                });
            }
            elseif($has_payment == 'false')
            {
                $query->whereRaw('id = supervisor_id')->where(function ($query){
                    $query->whereHas('totalFactorItem', function ($query){
                        $query->whereDoesntHave('factors', function ($query){
                            $query->where('status', 1);
                        });
                    })->orWhereDoesntHave('totalFactorItem')->whereDoesntHave('factors', function ($query){
                        $query->where('status', 1);
                    });
                });
            }
        }

        if (auth()->user()->hasPermission('file-detail|file-desktop')) {
            if (auth()->user()->center_id) {
                $center = auth()->user()->center_id;
                $query->where('center_id', $center);
            }

            if (isset($representation) && trim($representation) != 'all') {
                if (trim($representation) == 'center_representation') {
                    $query->whereDoesntHave('representation');
                } else {
                    $query->whereHas('representation', function ($query) use ($representation) {
                        $query->where('representation_id', $representation);
                    });
                }
            }

            if (isset($date) && trim($date) != '') {
                $date = verta($date)->format('Y/m/d');
                $query->where('date', 'LIKE', '%' . $date . '%');
            }

            if (isset($center) && trim($center) != 'all') {
                $query->where('center_id', $center);
            }

            if (isset($organization) && trim($organization) != 'all') {
                $query->whereHas('factor', function ($query) use ($organization) {
                    $query->where('organization_id', $organization);
                });
            }

            if (auth()->user()->hasRole('organ-manager')) {
                $query->whereHas('factor', function ($query) use ($organization) {
                    $query->where('organization_id', auth()->user()->admin_organization_id);
                });
            }
        } else {
            $query->whereHas('serviceTariffs', function ($query) use ($center) {
                $query->where('user_id', auth()->user()->id);
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

        if (isset($service_tariff) && trim($service_tariff) != '') {
            $query->whereHas('serviceTariffs', function ($query) use ($service_tariff) {
                $query->where('name', 'LIKE', '%' . $service_tariff . '%');
            });
        }

        if (isset($has_session) && trim($has_session) != 'all') {
            if($has_session)
			{
				$query->has('sessions');
			}
			else
			{
			    $query->whereDoesntHave('sessions');
			}
            if (isset($service_tariff) && trim($service_tariff) != '') {
                $query->whereHas('serviceTariffs', function ($query) use ($has_session) {
                    if($has_session)
                    {
                        $query->pivot->has('sessions');
                    }
                    else
                    {
                        $query->whereDoesntHave('pivot.sessions');
                    }
                });
            }
        }

        if (isset($phone) && trim($phone) != '') {
            $query->whereHas('factor', function ($query) use ($phone) {
                $query->whereHas('user', function ($query) use ($phone) {
                    $query->where('phone', $phone);
                });
            });
        }

        if (isset($status) && !is_null($status) && is_array($status)) {
            $query->whereIn('status_factor',$status);
        }

        if (isset($status_mali) && !is_null($status_mali) && is_array($status_mali)) {
            $query->whereIn('status_mali', $status_mali);
        }

        if (isset($type) && trim($type) != '' && $type != 'all') {
            $query->where('type', $type);
        }

        if (isset($purchase_type) && trim($purchase_type) != '' && $purchase_type != 'all') {
            $query->where('purchase_type', $purchase_type);
        }

        if (isset($service) && trim($service) != '' && $service != 'all') {
            $service = ServiceItem::find($service)->service->id;
            $query->where('service_id', $service);
        }
        if (isset($resnumber) && trim($resnumber) != '') {
            $query->where('resnumber', $resnumber);
        }
        if (isset($supporter) && trim($supporter) != '' && $supporter != 'all') {
            $query->where('supporter_id', $supporter);
        }
        if (isset($startDate) && trim($startDate) != '' && isset($endDate) && trim($endDate) != '') {
            $query->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
        }

        if ( (is_null($subServiceCategory) || $subServiceCategory == 'all' ) && isset($serviceCategory) && $serviceCategory!='all' ){
            $serviceCategory=Category::find($serviceCategory);
                    $query->where('factoritemable_type', '=', Service::class)->whereIn('factoritemable_id',$serviceCategory->services->pluck('id')->toArray());
        }
        if (isset($subServiceCategory) && $subServiceCategory != 'all'){
                    $query->where('factoritemable_type', '=', Service::class)->where('factoritemable_id',$subServiceCategory);
        }
        if (isset($productCategory) && trim($productCategory) != 'all') {
                    $query->where('factoritemable_type', '=', Category::class)->where('factoritemable_id',$productCategory);
        }
        if (isset($serviceItems) && is_array($serviceItems) ) {
                    $query->whereIn('service_item_id',$serviceItems);
        }

        return $query;
    }
    /**
     * @param mixed $status_factor
     * @return string
     */
    public static function statusFactor($status_factor)
    {
        return [
            '0' => 'جدید',
            '17' => 'منتظر مشاوره شعبه',
            '20' => 'منتظر تایید فاکتور توسط کاربر',
            '21' => 'درانتظار اصلاح فاکتور به درخواست کاربر',
            '2' => 'مشاوره شد',
            '10' => 'آماده اجرا',
            '6' => 'شروع کار',
            '7' => 'اتمام کار',
            '15' => 'پرونده مختومه',
            '19' => 'تعلیق اجرا',
            '1' => 'تاییدیه پشتیبان',
            '3' =>  'تعلیق پشتیبان',
            '4' => 'منتظر تکمیل مدارک',
            '5' => 'منتظر تایید مالی',
            '8' => 'منتظر تایید مدیر',
            '9' => 'منتظر تایید امور تسهیلات',
            '11' => 'منتظر تایید مدیر مالی نمایندگی',
            '12' => 'منتظر تایید مدیر نمایندگی',
            '13' => 'منتظر تایید امورقراردادها',
            '14' => 'منتظر تایید پشتیبان',
            '16' => 'ارجاع به کارشناسی',
            '18' => 'منتظر تایید قرارداد توسط کاربر',
        ][$status_factor];
    }
    /**
     * @param mixed $status_factor
     * @return string
     */
    public static function statusMali($status_mali)
    {
        return [
            '0' => 'جدید',
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
     * @param mixed $mobile_supervisor
     * @param FactorItem $factor_item
     * @return array
     * get factorItem supervisor by factoritemable && type filter
     */
	private static function getSupervisor($mobile_supervisor , FactorItem $factor_item)
    {
        $user_supervisor = User::where( 'phone', $mobile_supervisor )->first();
        if($user_supervisor)
        {
            $factor = Factor::where('user_id' , $user_supervisor->id)->whereIn('title' , ['register','factor'])
            ->where('status' , true)
            ->whereHas('factorItems', function($q) use($factor_item) {
               $q->where('type', '=', $factor_item->type)
                 ->where('factoritemable_id', $factor_item->factoritemable_id)
                 ->where('factoritemable_type', $factor_item->factoritemable_type);
            })->latest()->first();

            if($factor)
            {
                return ['supervisor_factor_item' => $factor->factorItems[0] , 'status' => 'success' , 'message' => 'اطلاعات سرپرست با موفقیت دریافت شد.'];
            }
            return ['supervisor_factor_item' => null , 'status' => 'error' , 'message' => 'این شماره سرپرست ، پرونده ای با این مشخصات ندارد.'];
        }
        return ['supervisor_factor_item' => null , 'status' => 'error' , 'message' => 'کاربری با این شماره تماس در سامانه وجود ندارد.'];
    }
    /**
     * @param mixed $mobile_supervisor
     * @param FactorItem $factor_item
     * @return array
     * set factorItem supervisor
     */
    public static function setSupervisor($mobile_supervisor , FactorItem $factor_item)
    {
        $mobile_supervisor = Helper::numberConverter( $mobile_supervisor );
        $result = FactorItem::getSupervisor( $mobile_supervisor , $factor_item );
        if($result['status'] == 'success')
        {
            if($factor_item->id != $result['supervisor_factor_item']->id)
            {
                $factor_item->totalFactorItem()->delete();
                $factor_item->update( [ 'total_price' => 0, 'supervisor_id' => $result['supervisor_factor_item']->id , 'total_factor_item_id' => null ] );
                $factor_item->property()->update([
                    'prepayment_price'=>0,
                    'prepayment_debt'=>0,
                    'prepayment_count'=>0,
                    'prepayment_num'=>0
                ]);
            }
            return ['supervisor_factor_item' => $result['supervisor_factor_item'], 'factor_item' => $factor_item , 'message' => $result['message']];
        }

        return ['message' => $result['message']];
    }
    /**
     * @param FactorItem $factor_item
     * get factorItem of a collection
     */
    public static function getFatorItemCollection(FactorItem $factor_item)
    {
        $factorItems = FactorItem::where('supervisor_id', $factor_item->supervisor_id);
        return $factorItems;
    }
    /**
     * @param FactorItem $factor_item
     * @return mixed
     * get expire installments
     */
    public static function getExpireInstallments(FactorItem $item)
    {
        $expire_installments = $item->factorItemInstallments->where('status', 0)->where('date', '<', Carbon::now());
        return $expire_installments;
    }
    /**
     * @param FactorItem $item
     * @return void
     * create factorItem installments if not exist else update installment
     */
    public static function createOrUpdateInstallments(FactorItem $item)
    {
        if (count($item->factorItemInstallments) == 0 && $item->installments_price != 0) {
            for ($i = 1 ; $i <= $item->installments_num ; $i++) {
                $date = verta($item->approval_date)->addMonths($i);
                $factorItemInstallment = new FactorItemInstallment();
                $factorItemInstallment->price = $item->installments_price;
                $factorItemInstallment->level = $i;
                $factorItemInstallment->date = $date->formatGregorian('Y-m-d H:i:s');
                $factorItemInstallment->factor_item_id = $item->id;
                $factorItemInstallment->save();
            }
        } elseif($item->installments_price != 0) {
            self::updateInstallments($item);
        }
    }
    /**
     * @param FactorItem $item
     * @return void
     * delete factorItem installments if not confirmed
     */
    public static function updateInstallments(FactorItem $item)
    {
        $factor_item_installments_paid = $item->factorItemInstallments->where('status' , 1)->count();
        if(!$item->confirm && !$factor_item_installments_paid)
        {
            $item->factorItemInstallments()->delete();
            $factor_item = FactorItem::find($item->id);
            self::createOrUpdateInstallments($factor_item);
        }
    }
    /**
     * @param FactorItem $item
     * @return int //price
     */
    public static function getPrepaymentPaid(FactorItem $item)
    {
        //get prepayment price paid in cash or credit factorItem
        $prepayment_paid = $item->factors()
            ->where(function($query) {
                $query->where( 'title' , 'prepayment' );
                $query->orWhere( 'title', 'factor' );
            })->where(function($query) {
                $query->where('status' , 1);
            })->sum( 'price' );

        //get return payment price in cash or credit factorItem
        $return_payment =  $item->factors()
            ->where('status', 2)
            ->where(function($query) {
                $query->where( 'title' , 'prepayment' );
                $query->orWhere( 'title', 'factor' );
            })->sum('price');

        $prepayment_paid = $prepayment_paid - $return_payment;

        if(!$item->type)
        {
            $prepayment_paid = $prepayment_paid + $item->factor->price;
        }

        return $prepayment_paid;
    }
    /**
     * @param FactorItem $item
     * @return int //price
     */
    public static function getPriceDifferencePaid(FactorItem $item)
    {
        //get total price_difference price paid
        $price_differnce_paid = $item->factors()
        ->where('title', 'price_difference')
        ->where(function($query) {
            $query->where('status', 1);
        })->sum('price');

        //get total return payment price
        $return_payment = $item->factors->where('status', 2)->where('title', 'price_difference')->sum('price') + $item->factors->where('status', 1)->where('type', 'deduction_price_difference')->sum('price');

        $price_differnce_paid = $price_differnce_paid - $return_payment;

        return $price_differnce_paid;
    }
    /**
     * @param FactorItem $item
     * @param mixed $price
     * @return bool
     * check the credit factorItem
     */
    public static function checkCredit(FactorItem $item , $price)
    {
        $sessions_sum_price = self::getSessionsSumPrice($item , 'all');
        $financial_plans_type_discount_price = $item->financialPlansType ? ($item->financialPlansType->discount_percent/100) * $sessions_sum_price : 0;
        $prepayment_paid = ($item->type ? TotalFactorItem::getPrepaymentPaid($item->totalFactorItem) : self::getPrepaymentPaid($item)) - $item->discount + $financial_plans_type_discount_price;
        $sessions_sum_price = self::getSessionsSumPrice($item , 1);
        if($item->type && $item->totalFactorItem->confirm)
        {
            $temp = round($price) <= round($item->credit) ? true : false;
            //$temp = TotalFactorItem::getExpireInstallments($item->totalFactorItem)->isEmpty() ? true : false;
        }
        else
        {
            //reduce the register price in cash files
            $temp = round( $price) <= round($prepayment_paid) ? true : false;
        }
        return $temp;
    }
    /**
     * @param FactorItem $item
     * @return int //price
     */
    public static function calculateCredit(FactorItem $item)
    {
        $sessions_sum_price = self::getSessionsSumPrice($item , 1);
        $prepayment_paid = self::getPrepaymentPaid($item) - $item->discount;

        $user = $item->factor->user;
        //credit factorItem
        if($item->type && $item->confirm)
        {
            $credit = $item->credit > 0 ? $item->credit : 0;
        }
        //cash factorItem
        else
        {
            $credit = ($prepayment_paid - $sessions_sum_price) > 0 ? $prepayment_paid - $sessions_sum_price : 0;
        }

        $credit = $credit + $user->wallet;
        $credit = $credit + $user->credit;

        return $credit;
    }
    /**
     * @param FactorItem $item
     * @return int //price
     */
    public static function calculateTotalDebt(FactorItem $item)
    {
        $sessions_sum_price = self::getSessionsSumPrice($item , 'all');
        $prepayment_debt = isset($item->property->prepayment_debt) ? $item->property->prepayment_debt : 0;
        $financial_plans_type_discount_price = $item->financialPlansType ? ($item->financialPlansType->discount_percent/100) * $sessions_sum_price : 0;
        $prepayment_paid = self::getPrepaymentPaid($item) - $item->discount + $financial_plans_type_discount_price;
        $debt = 0;
        $total_debt_items = [];
        if($item->type && $item->confirm)
        {
            $price_difference_paid =isset($item->property->price_difference_total) ? ($item->property->price_difference_total - $item->property->price_difference_debt ) : 0;
            $debt = $debt + TotalFactorItem::getExpireInstallments($item->totalFactorItem)->sum('price');
            $debt = $debt + (isset($item->property->price_difference_debt) ? $item->property->price_difference_debt : 0);
            $debt = $debt + $prepayment_debt;
            $total_debt_items['expire_installments_price'] = TotalFactorItem::getExpireInstallments($item->totalFactorItem)->sum('price');
            $total_debt_items['price_difference_debt'] = isset($item->property->price_difference_debt) ? $item->property->price_difference_debt : 0;
            $total_debt_items['prepayment_debt'] = $prepayment_debt;
            $total_debt_items['extra_work'] = ($sessions_sum_price - ($price_difference_paid + $item->total_price + $item->discount)) > 0 ? $sessions_sum_price - ($price_difference_paid + $item->total_price + $item->discount) : 0;
            $total_debt_items['debt'] = $debt;
        }
        else
        {
            $debt = $debt + $prepayment_debt;
            $total_debt_items['expire_installments_price'] = 0;
            $total_debt_items['price_difference_debt']=0;
            //$debt = $debt + (($sessions_sum_price - $prepayment_paid) > 0 ? $sessions_sum_price - $prepayment_paid : 0);
            $total_debt_items['prepayment_debt'] = $prepayment_debt;
            $total_debt_items['extra_work'] = ($sessions_sum_price - $prepayment_paid) > 0 ? $sessions_sum_price - $prepayment_paid : 0;
            $total_debt_items['debt'] = $debt;
        }

        return $total_debt_items;
    }
    /**
     * @param FactorItem $item
     * @param mixed $status
     * @return int //price
     * get the sessions done sum price
     */
    public static function getSessionsSumPrice(FactorItem $item , $status)
    {
        $sessions_sum_price = 0;
        $factor_items = FactorItem::getFatorItemCollection($item)->get();
        foreach ( $factor_items as $factor_item ) {
            if($status == 'all')
            {
                $sessions_sum_price =  $sessions_sum_price + $factor_item->sessions->sum('price');
            }
            else
            {
                $sessions_sum_price =  $sessions_sum_price + $factor_item->sessions->where('status' , 1)->sum('price');
            }
        }
        return $sessions_sum_price;
    }
    /**
     * @param FactorItem $factor_item
     * @param $total_price
     * @param $other_price
     * @return int //price
     */
    public static function getTotalPrice(FactorItem $factor_item , $total_price , $other_price)
    {
        $factor_items = FactorItem::getFatorItemCollection( $factor_item )->get();

        $total_price = ( float ) str_replace( ',', '', $total_price);
        $sum_service_tariff_total_price = 0;
        foreach ($factor_items as $item) {
            $total_price_service_tariffs = $item->serviceTariffs()->sum('factor_item_service_tariff.price');
            $total_discount_price = $item->serviceTariffs()->sum('factor_item_service_tariff.discount');
            $sum_service_tariff_total_price = $sum_service_tariff_total_price + $other_price + $total_price_service_tariffs - $total_discount_price;
        }
        $total_price == 0 ? $total_price = $sum_service_tariff_total_price : '';
        // $sum_service_tariff_total_price != 0 ? $total_price = $sum_service_tariff_total_price : '';
        //apply financialPlansType discount percent if exist
        $financial_plans_type_discount_price = $factor_item->financialPlansType ? ($factor_item->financialPlansType->discount_percent/100) * $total_price : 0;
		$total_price = $total_price + ($factor_item->supervisor ? $factor_item->supervisor->discount : 0) - $financial_plans_type_discount_price;

		return $total_price;
    }
    /**
     * @param mixed $supervisor_factor_item
     * @param mixed $total_price
     * @param mixed $type
     * @param mixed $user
     * @return void
     */
    public static function calculateFinancialInformation($supervisor_factor_item , $total_price, $type, $user)
    {
        if ( $supervisor_factor_item->totalFactorItem && $supervisor_factor_item->totalFactorItem->confirm ) {
            //get difference price
            FactorItem::calculateDiffrencePrice($supervisor_factor_item , $total_price);
        } else {
            //get calculate financial information for cash factorItems
            FactorItem::calculate( $supervisor_factor_item, $total_price, $type, $user );
        }
        if ($supervisor_factor_item->type)
        {
            //get calculate financial information for installment factorItems
            TotalFactorItem::updateCalculate( $supervisor_factor_item->totalFactorItem );
        }
    }
    /**
     * @param FactorItem $supervisor_factor_item
     * @param mixed $total_price
     * @return void
     */
    public static function calculateDiffrencePrice( FactorItem $supervisor_factor_item , $total_price ) {
        $total = $total_price - ($supervisor_factor_item->total_price + $supervisor_factor_item->discount);
        $price_difference_pay = self::getPriceDifferencePaid($supervisor_factor_item);

        if ($supervisor_factor_item->property()->exists()){
            $supervisor_factor_item->property()->update([
                    'price_difference_total'=>$total,
                    'price_difference_debt'=>$total - $price_difference_pay,
            ]);
        }else{
            $supervisor_factor_item->property()->create([
                'price_difference_total'=>$total,
                'price_difference_debt'=>$total - $price_difference_pay,
            ]);
        }
    }
    /**
     * @param FactorItem $supervisor_factor_item
     * @param $total_price
     * @param $type
     * @param $user
     * @return void
     */
    public static function calculate(FactorItem $supervisor_factor_item, $total_price, $type, $user)
    {
        $prepayment_price = CalculationHelper::calculatePrepayment($supervisor_factor_item, $total_price, $type, $user);
        $installments_num = $type ? CalculationHelper::calculateInstallmentsNum($supervisor_factor_item) : 0;
        $wage = CalculationHelper::calculateWage($supervisor_factor_item , $total_price , $prepayment_price , $installments_num);
        $tax = CalculationHelper::calculateTax($wage);
        $loan = CalculationHelper::calculateLoan($total_price , $prepayment_price , $wage , $tax);
        $installment_price = CalculationHelper::calculateInstallmentsPrice($type , $loan , $installments_num);
        $prepayment_num = CalculationHelper::calculatePrepaymentNum($supervisor_factor_item , $type);

        //get payment invoices and refunds
        $prepayment_paid = FactorItem::getPrepaymentPaid($supervisor_factor_item);

        $prepayment = [];
        $prepayment = Arr::add($prepayment, 'prepayment_price', $prepayment_price);
        $prepayment = Arr::add($prepayment, 'prepayment_debt', $prepayment_price - $prepayment_paid);
        $prepayment = Arr::add($prepayment, 'prepayment_num', $prepayment_num);
        $prepayment = Arr::add($prepayment, 'prepayment_count', isset($supervisor_factor_item->property->prepayment_num) ? $prepayment_num - ($supervisor_factor_item->property->prepayment_num - $supervisor_factor_item->property->prepayment_count) : $prepayment_num);

        //update supervisor factorItem
        $supervisor_factor_item->update([
            'total_price' => $total_price - $supervisor_factor_item->discount,
            'installments_price' => $installment_price,
            'installments_num' => $installments_num,
        ]);

        //update property of factorItem
        if ($supervisor_factor_item->property()->exists()){
            $supervisor_factor_item->property->update($prepayment);
        }else{
            $supervisor_factor_item->property()->create($prepayment);
        }
    }
    /**
     * @param FactorItem $item
     * @param mixed $price
     * @return void
     * create factor by inFactorItem type when change factorItem type of credit to the cash
     */
    public static function createPaymentInFactorItem(FactorItem $item , $price)
    {
        if(!$item->factors()->where('status' , 1)->exists())
        {
            $factor = new Factor();
            $factor->title = 'factor';
            $factor->user_id = $item->factor->user_id;
            $factor->price = $price;
            $factor->type = 'in_factor_item';
            $factor->factor_item_id = $item->id;
            $factor->callcenter_id = auth()->user()->id;
            $factor->representation_id = auth()->user()->admin_representation_id;
            $factor->center_id = $item->center_id;
            $factor->status = 1;
            $factor->save();
        }
    }
    /**
     * change type of factorItem
     * @param FactorItem $factor_item
     * @param mixed $type
     */
    public static function changeType(FactorItem $factor_item, $type ) {
        $factor_items = FactorItem::getFatorItemCollection( $factor_item );
        $supervisor_factor_item = $factor_item->supervisor;
        if ( $supervisor_factor_item->totalFactorItem && $supervisor_factor_item->totalFactorItem->confirm ) {
            return false;
        }
        if($type != $factor_item->type)
        {
            //convert cash to credit
            if ( $type && $factor_item->type == 0 ) {

                $factor_items->update( [
                    'status_factor' => 9,
                    'type' => $type,
                ] );

                $total_factor_item = TotalFactorItem::create( [
                    'factor_id' => isset( $supervisor_factor_item->factor ) ? $supervisor_factor_item->factor->id : 1,
                    'contract_values' => $supervisor_factor_item->contract_values,
                    'credit' => $supervisor_factor_item->credit,
                    'guarantee_type' => $supervisor_factor_item->guarantee_type,
                    'total_price' => $supervisor_factor_item->total_price,
                    'installments_num' => $supervisor_factor_item->installments_num,
                    'total_factor_item_status_id' => 9,
                    'status_mali' => 0,
                    'percent_fines' => $supervisor_factor_item->percent_fines,
                ] );
                $total_factor_item->property()->create([
                    'prepayment_price'=>$supervisor_factor_item->property ? $supervisor_factor_item->property->prepayment_price : 0,
                    'prepayment_debt'=>$supervisor_factor_item->property ? $supervisor_factor_item->property->prepayment_debt : 0,
                    'prepayment_count'=>$supervisor_factor_item->property ? $supervisor_factor_item->property->prepayment_count : 0,
                    'prepayment_num'=>$supervisor_factor_item->property ? $supervisor_factor_item->property->prepayment_num : 0,
                ]);

                $supervisor_factor_item->update( [ 'total_factor_item_id' => $total_factor_item->id ] );

                $supervisor_factor_item->factors()->where( 'title', 'factor' )->update( [ 'title' => 'prepayment' , 'total_factor_item_id' => $total_factor_item->id ] );
            }

            //convert credit to the cash
            if ( !$type && $supervisor_factor_item->type ) {
                //if the factorItem is not approved
                $total_factor_item = $supervisor_factor_item->totalFactorItem;
                if ( !$supervisor_factor_item->totalFactorItem->confirm ) {
                    TotalFactorItem::setFactorsPaid($total_factor_item , $supervisor_factor_item);
                    $status_factor = count($factor_item->sessions) > 0  ? 6 : $factor_item->status_factor;
                    $factor_items->update( [
                        'type' => $type,
                        'status_factor' => $status_factor
                    ] );
                    $supervisor_factor_item->update( [ 'total_factor_item_id' => null ] );
                    TotalFactorItem::checkHasFactorItem($total_factor_item);
                }
            }
            $factor_item = FactorItem::find($factor_item->id);
            //get total price factor
            $total_price = $factor_item->serviceTariffs()->exists() ? 0 : $factor_item->total_price;
            $other_price = isset( $factor_item->factor_value[ 'otherPrice' ] ) ? $factor_item->factor_value[ 'otherPrice' ] : 0;
            $total_price = FactorItem::getTotalPrice($factor_item , $total_price , $other_price);
            //calculate
            FactorItem::calculate( $supervisor_factor_item, $total_price, $type, $supervisor_factor_item->factor->user );

            if($supervisor_factor_item->totalFactorItem)
            {
                TotalFactorItem::updateCalculate($total_factor_item);
            }
        }
        return true;
    }

    /**
     * @param FactorItem $item
     * @return bool
     */
    public static function checkPayment(FactorItem $item)
    {
        if($item->supervisor && $item->supervisor->totalFactorItem)
        {
            $return_payment_price = $item->supervisor->totalFactorItem->factors()->where('status' , 2)->sum('price');
            $paid_price = $item->supervisor->totalFactorItem->factors()->where('status' , 1)->sum('price');
        }
        else
        {
            $return_payment_price = $item->supervisor ? $item->supervisor->factors()->where('status' , 2)->sum('price') : 0;
            $paid_price = $item->supervisor ? $item->supervisor->factors()->where('status' , 1)->sum('price') : 0;
        }

        $result = $paid_price - $return_payment_price > 0 ? true : false;
        return $result;
    }

    /**
     * @param FactorItem $factor_item
     * @return bool
     */
    public static function haveInCompleteServiceTariff(FactorItem $factor_item){
        if (FactorItemServiceTariff::where('factor_item_id',$factor_item->id)->where('status',0)->count()>0){
            return true;
        }
        return false;
    }

    /**
     * @param FactorItem $factorItem
     * @return void
     * get financialPlansType if exist else get factorItem serviceItem
     */
    public static function getFinancialPlansType(FactorItem $factorItem): string
    {
        return $factorItem->financialPlansType?->title
            ?? $factorItem->serviceItem?->title
            ?? 'ثبت نشده';
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function checks()
    {
        return $this->morphMany(Check::class, 'checkable');
    }
    /**
     * @param \App\Models\FactorItem $item
     * @return mixed //array or null
     */
    public static function getSelectableCenters(FactorItem $item): \Illuminate\Database\Eloquent\Collection
    {
        return Center::query()
            ->where('status', true)
            ->whereHas('services', fn($q) => $q->where([
                'centerable_id'   => $item->factoritemable_id,
                'centerable_type' => $item->factoritemable_type,
            ]))
            ->when(
                auth()->user()?->admin_representation_id,
                fn($q) => $q->whereHas('representations', fn($q) => $q->where('id', auth()->user()->admin_representation_id))
            )
            ->get();
    }
    /**
     *@param \App\Models\FactorItem $item
     *@param int $representation
     *@return void
     */
    public static function createRepresentation(FactorItem $item, $representation)
    {
        $user = auth()->user();

        $item->histories()->create([
            'user_id' => $user->id,
            'callcenter_id' => $user->id,
            'status' => 1,
            'description' => "نمایندگی این پرونده توسط {$user->family} تغییر کرده است.",
        ]);
        $item->representation()->delete();
        if ($representation != 'center_representation') {
            $item->representation()->updateOrCreate(
               ['representation_id' => $representation],
               ['agent_id' => $user->id]
            );
        }
    }
    /**
     * Summary of setCenterForSingleCenter
     * @param FactorItem $factorItem
     * @return void
     */
    public static function setCenterForSingleCenter(FactorItem $factorItem): void
    {
        $selectableCenters = FactorItem::getSelectableCenters($factorItem);
        if ($selectableCenters->count() === 1) {
            $factorItem->update(['center_id' => $selectableCenters->first()->id]);
        }
     }
    /**
     * Summary of referralCenter
     * @param FactorItem $factorItem
     * @param mixed $request
     * @return void
     */
    public static function referralCenter(FactorItem $factorItem , $request)
    {
        //close older sessions
        Session::whereIn('status',[3,5,8])->where('factor_item_id',$factorItem->id)->update([
            'status'=>7,
            'description'=>'لغو این جلسه به دلیل ارجاع به شعبه جدید'
        ]);

        //update center_id
        $factorItem->update( [
            'center_id' =>  $request['center_id'],
            'status_factor' => 17,
            'date' => now(),
        ] );
        //create session
        Session::create( [
            'status' => 8 ,
            'user_id' => auth()->user()->id,
            'factor_item_id' => $factorItem->id,
            'description_before' => $request['description'] ,
        ]);

        //send sms to the user
        event( new smsService( $factorItem , 'setCenter') );
        if($factorItem->center->creatorRepresentation)
        {
            $factorItem->factor->update( [ 'representation_id' => $factorItem->center->creator_representation_id ] );
        }
        //send sms to the center manager
        event(new referralCenterNotification($factorItem->center));
    }
    /**
     * Summary of getFactorItemAble
     * @param FactorItem $factorItem
     * @param mixed $temp
     */
    public static function getFactorItemAble(FactorItem $factorItem , $temp)
    {
        if ( $factorItem->factoritemable_type == Service::class ) {
            array_push($temp, $factorItem->factoritemable->title . (isset($factor_item->serviceItem) ? ' - ' . $factorItem->serviceItem->title : ''));
        } else {
            array_push($temp, $factorItem->factoritemable->title);
        }
        return $temp;
    }
       /**
     * @param FactorItem $factor_item
     * @return array
     * set factorItem items for use in show && edit
     */
    public static function setItem( FactorItem $factor_item ) {
        $supervisor_factor_item = $factor_item->supervisor;
        $factor_item[ 'id' ] = $factor_item->id;
        $factor_item[ 'factor' ] = $factor_item->factor;
        $factor_item[ 'factoritemable' ] = $factor_item->factoritemable;
        $factor_item[ 'service_item' ] = !is_null( $factor_item->service_item_id ) ? $factor_item->service_item : null;
        $factor_item[ 'centers' ] = FactorItem::getSelectableCenters($factor_item);
        $factor_item[ 'forms' ] = isset( $factor_item->factoritemable->beforeforms ) ? $factor_item->factoritemable->beforeforms : null;
        $factor_item[ 'site_forms' ] = isset( $factor_item->factoritemable->beforeforms ) ? $factor_item->factoritemable->beforeforms->where( 'level', 'site' ) : null;
        $factor_item[ 'complete' ] = $factor_item->complete;
        $factor_item[ 'discount' ] = $supervisor_factor_item->discount ;
        $factor_item[ 'total_price' ] = $supervisor_factor_item->total_price + $supervisor_factor_item[ 'discount' ];
        $factor_item[ 'installments_num' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->installments_num : '';
        $factor_item[ 'installments_price' ] = number_format($supervisor_factor_item->installments_price);
        $factor_item[ 'values' ] = $factor_item->values;
        $factor_item[ 'factor_values' ] = $factor_item->factor_values;
        $factor_item[ 'supervisor_id' ] = $factor_item->supervisor_id;
        $factor_item[ 'factor_image' ] = $factor_item->factor_image;
        $factor_item[ 'status_factor' ] = $factor_item->status_factor;
        $factor_item[ 'status_factor_text' ] = FactorItem::statusFactor( $factor_item->status_factor );
        $factor_item[ 'confirms' ] = $factor_item->confirms;
        $factor_item[ 'center' ] = $factor_item->center;
        $factor_item[ 'date' ] = $factor_item->date ? $factor_item->date : now();
        $factor_item[ 'type' ] = $factor_item->type;
        $factor_item[ 'file_number' ] = $factor_item->file_number ? $factor_item->file_number : 'ندارد';
        $factor_item[ 'approval_date' ] = verta( $supervisor_factor_item->approval_date )->format( 'Y/m/d' );
        $factor_item[ 'status_mali' ] = FactorItem::statusMali( $supervisor_factor_item->status_mali );
        $factor_item[ 'status' ] = $factor_item->status;
        $factor_item[ 'products' ] = $factor_item->products;
        $factor_item[ 'service_tariffs' ] = $factor_item->serviceTariffs;
        $factor_item[ 'date_first_pay' ] = isset( $factor_item->factors->where( 'title', '!=', 'register' )->where( 'status', true )->first()->created_at ) ? verta( $factor_item->factors->where( 'title', '!=', 'register' )->first()->created_at )->format( 'Y/m/d' ) : '';
        $factor_item[ 'factorItems' ] = FactorItem::getFatorItemCollection( $factor_item )->get();
        $factor_item[ 'sessions' ] = $factor_item->sessions;
        $factor_item[ 'histories' ] = $factor_item->histories;
        $factor_item[ 'address' ] = $factor_item->factor->address;
        $factor_item[ 'user_histories'] = Helper::userHistories( $factor_item->factor->user );
        $factor_item[ 'financial_plans_type' ] = FactorItem::getFinancialPlansType($factor_item);
        $factor_item[ 'financial_plans_types_list' ] = FinancialPlansType::getFinancialPlansTypeByItemAndPaymentType($factor_item->factoritemable , $factor_item->type);
        $factor_item['factor_histories'] = $factor_item->histories->where('reason_id' , 2);
        //fill prepayment array
        $factor_item['prepayment'] = self::getPrepayment($supervisor_factor_item);
        //fill price_difference array
        $factor_item['price_difference'] = self::getPriceDifference($supervisor_factor_item);
        //information factorItem mali
        $item = [];
        $item[ 'id' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->id : $factor_item->id;
        $item[ 'total_factor_item' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem : [];
        $item[ 'total_debt' ] = $supervisor_factor_item->type ? TotalFactorItem::calculateTotalDebt($supervisor_factor_item->totalFactorItem) : FactorItem::calculateTotalDebt($supervisor_factor_item);
        $item[ 'confirm' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->confirm : false;
        $item[ 'credit' ] = $supervisor_factor_item->type ? TotalFactorItem::calculateCredit($supervisor_factor_item->totalFactorItem) : FactorItem::calculateCredit($supervisor_factor_item);
        $item[ 'discount' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->factorItems->sum('discount') : $supervisor_factor_item->discount ;
        $item[ 'total_price' ] = ($supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->total_price : $supervisor_factor_item->total_price) + $item[ 'discount' ];
        $item[ 'installments_num' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->installments_num : '';
        $item[ 'guarantee_type' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->guarantee_type : '';
        $item[ 'guarantee_types_list' ] = $supervisor_factor_item->type ? TotalFactorItem::getGuaranteeTypeByFinancialPlansType($supervisor_factor_item->totalFactorItem) : [];
        $item[ 'percent_fines' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->percent_fines : '';
        $item[ 'payment' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->factor : $supervisor_factor_item->factor;
        $item[ 'payments' ] = $supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem->factors : $supervisor_factor_item->factors;
        $item[ 'has_payment' ] = $supervisor_factor_item->purchase_type ? $supervisor_factor_item->factors()->where( 'status', 1 )->exists() : $supervisor_factor_item->factor()->where( 'status', 1 )->exists();
        $item['start_date'] = verta($supervisor_factor_item->approval_date)->format('Y/m/d');
        $item['contract_histories'] = $factor_item->histories->where('reason_id' , 1);

        //fill prepayment array
        $item['prepayment'] = self::getPrepayment($supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem:$supervisor_factor_item);
        //fill price_difference array
        $item['price_difference'] = self::getPriceDifference($supervisor_factor_item->type ? $supervisor_factor_item->totalFactorItem:$supervisor_factor_item);

        return ['factorItem' => $factor_item , 'item' => $item];
    }

    /**
     * @param mixed $item //factorItem or totalFactorItem
     * @return array
     */
    private static function getPrepayment($item)
    {
        $prepayment_price = $item->property ? $item->property->prepayment_price : 0;
        $prepayment_debt = $item->property ? $item->property->prepayment_debt : 0;
        $prepayment_count = $item->property ? $item->property->prepayment_count : 0;
        $prepayment_num = $item->property ? $item->property->prepayment_num : 0;
        $prepayment = array('price' => $prepayment_price , 'debt' => $prepayment_debt , 'count' => $prepayment_count , 'num' => $prepayment_num);

        return $prepayment;
    }
    /**
     * @param mixed $item // factorItem or totalFactoItem
     * @retrun int
     */
    private static function getPriceDifference($item)
    {
        $price_difference_installment = $item->property ? $item->property->price_difference_installment : 0;
        $price_difference_total = $item->property ? $item->property->price_difference_total : 0;
        $price_difference_debt = $item->property ? $item->property->price_difference_debt : 0;
        $price_difference = array('installment' => $price_difference_installment , 'total' => $price_difference_total , 'debt' => $price_difference_debt);

        return $price_difference;
    }
    /**
     * @param FactorItem $item
     * @return string
     * set agents contract number for set contract factorItem
     */
    public static function setAgentContractNumber(FactorItem $item)
    {
        $contract_number = [];
		$contract_start_date = [];

        $fator_item_collections = FactorItem::getFatorItemCollection($item)->get();
        foreach($fator_item_collections as $fator_item_collection)
        {
            if(isset($fator_item_collection->center))
            {
                isset($fator_item_collection->center->contract_number) ? array_push($contract_number, $fator_item_collection->center->contract_number) : array_push($contract_number, '......');
                isset($fator_item_collection->center->contract_start_date) ? array_push($contract_start_date, verta($fator_item_collection->center->contract_start_date)->format('Y/m/d')) : '.......';
            }
        }

        $contract_number = implode(' , ', $contract_number);
        $contract_start_date = implode(' , ', $contract_start_date);

        return $contract_number.' مورخ '.$contract_start_date;
    }
    /**
     * Summary of saveContractView
     * @param FactorItem $factorItem
     * @return void
     */
    public static function saveContractView(FactorItem $factorItem)
    {
        $user = $factorItem->factor->user;
        $item = self::setItem( $factorItem );
        $resnumber =  $factorItem->resnumber;
        $contract_number = FactorItem::setAgentContractNumber( $factorItem );

        if ( $factorItem->where( 'factoritemable_id', 124 )->count() ) {
            $contract_view = 'Admin.totalfactoritem.layouts.contractLoanCash';
        } elseif ( $factorItem->whereIn( 'factoritemable_id', [17,198] )->count() ) {
            $contract_view = 'Admin.totalfactoritem.layouts.contractGhorfeOnline';
        } else {
            $contract_view = 'Admin.totalfactoritem.layouts.contract';
        }
        $content = View::make( $contract_view, [
            'resnumber' => $resnumber,
            'contract_number' => $contract_number,
            'factorItem' => $factorItem,
            'user' => $user,
            'item' => $item,
        ] )->render();
        ContractView::store($factorItem , $content);
    }
        /**
     * Summary of saveContractFactorView
     * @param FactorItem $factorItem
     * @return void
     */
    public static function saveContractFactorView(FactorItem $factorItem)
    {
        $factor_rules = $factorItem->factoritemable->beforeforms->where('type', 'rules')->first();
        if($factor_rules)
        {
            ContractView::store($factorItem , $factor_rules->help);
        }
    }
}

