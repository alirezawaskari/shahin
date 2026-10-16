<?php

namespace App\Models;

use App\Helpers\FilterHelper;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Callcenter extends Model implements AuditableContract
{
    use Auditable;

    /**
     * @var array
     */
    protected $guarded = [];

    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at' , 'create'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sms()
    {
        return $this->belongsTo(sms::class, 'sms_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function callcenter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ghorfeOnlineLists()
    {
        return $this->belongsToMany(GhorfeOnlineList::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function register()
    {
        return $this->belongsTo(User::class, 'reg_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function suspensionStandard()
    {
        return $this->hasOne(SuspensionStandardCallcenter::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reason()
    {
        return $this->belongsTo(Reason::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function representation()
    {
        return $this->belongsTo(Representation::class);
    }

    /**
     * Access scoping (tenancy, permissions, representation) + the request's filter criteria.
     *
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        // Business scoping must apply to EVERY listing. It used to live in the `else` of the
        // description check, so searching by description silently dropped tenancy.
        $query = FilterHelper::getDataByCheckGhorfeExistOrNot($query , 'belongsToMany' , null);

        if (auth()->user()->admin_representation) {
            $query->where('representation_id', auth()->user()->admin_representation->id);
        }
        if (!auth()->user()->hasPermission('callcenter-detail')) {
            $query->whereHas('callcenter',function ($query){
                    $query->Where('user_id', auth()->user()->id)->orWhere('agent_id', auth()->user()->id);
                    $query->orWhereHas('employee',function ($query){
                       $query->where('parent_id',auth()->user()->employee->id);
                    });
            });
        }
        if(auth()->user()->center_id)
		{
            $query->where(function ($query){
			$query->whereHas('factors', function ($query)  {
                $query->whereHas('factorItems', function ($query)  {
                    $query->whereIn('factoritemable_id', auth()->user()->center->services->pluck('id')->toArray());
                    });
                })->orWhereHas('callcenter',function ($query){
                    $query->whereHas('employee',function ($query){
                       $query->where('parent_id',auth()->user()->employee->id);
                });
                })->orWhere('agent_id', auth()->user()->id)->orWhere('user_id', auth()->user()->id);
            });
		}

        return $query->applyCriteria(request()->all());
    }

    /**
     * Pure filter criteria, independent of the HTTP request so the same filter can be
     * stored and replayed (e.g. "send SMS to this filter result").
     *
     * Every optional criterion is skipped when empty / "all", every multi-value input is
     * accepted as scalar or array, and every OR is grouped so it can never escape the
     * other criteria.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array<string, mixed> $c
     */
    public function scopeApplyCriteria($query, array $c)
    {
        $text = fn ($key) => (isset($c[$key]) && is_scalar($c[$key]) && trim((string) $c[$key]) !== '') ? trim((string) $c[$key]) : null;

        $name = $text('name');
        $family = $text('family');
        $description = $text('description');
        $phone = $text('user_phone');
        $requestType = $text('request_type');
        $hasFactor = $text('has_factor');
        $sex = $text('sex');
        $representation = $text('representation');
        $startDate = $text('startDate');
        $endDate = $text('endDate');

        $status = self::idList($c['status'] ?? null);
        $type = self::idList($c['type'] ?? null);
        $users = self::idList($c['users'] ?? null);
        $agents = self::idList($c['agents'] ?? null);
        $province = self::decodeIds($c['province'] ?? null);
        $city = self::decodeIds($c['city'] ?? null);
        $area = self::decodeIds($c['area'] ?? null);
        $productCategory = self::idList($c['product_category'] ?? null);
        $serviceCategory = self::idList($c['service_category'] ?? null);
        $subServiceCategory = self::idList($c['sub_service_category'] ?? null);
        $serviceItems = self::idList($c['service_items'] ?? null);
        $service = self::idList($c['service'] ?? null);

        if ($description !== null) {
            $query->where(function ($q) use ($description) {
                $q->where('description', 'LIKE', '%' . $description . '%')
                    ->orWhereHas('register', function ($q) use ($description) {
                        $q->whereHas('callcenters', function ($q) use ($description) {
                            $q->whereHas('histories', function ($q) use ($description) {
                                $q->where('description', 'LIKE', '%' . $description . '%');
                            });
                        });
                    })
                    ->orWhereHas('sms', function ($q) use ($description) {
                        $q->where('body', 'LIKE', '%' . $description . '%');
                    });
            });
        }

        if ($status) {
            $query->whereIn('status', $status);
        }

        if ($type) {
            $query->whereIn('type', $type);
        }

        if ($requestType !== null && $requestType !== 'all') {
            $query->where('request_type', $requestType);
        }

        if ($province) {
            $query->whereHas('register', fn ($q) => $q->whereIn('province_id', $province));
        }
        if ($city) {
            $query->whereHas('register', fn ($q) => $q->whereIn('city_id', $city));
        }
        if ($area) {
            $query->whereHas('register', fn ($q) => $q->whereIn('area_id', $area));
        }

        // The phone lives on the registered user (site / call / visit) or on the incoming SMS.
        // Whichever request types are selected, match either: the `type` filter above already
        // narrows the types, and ANDing "site" with "sms" conditions matched nothing.
        if ($phone !== null) {
            $query->where(function ($q) use ($phone) {
                $q->whereHas('register', fn ($q) => $q->where('phone', $phone))
                    ->orWhereHas('sms', fn ($q) => $q->where('sender_number', $phone));
            });
        }

        if ($name !== null) {
            $query->whereHas('register', fn ($q) => $q->where('name', 'LIKE', '%' . $name . '%'));
        }

        if ($family !== null) {
            $query->whereHas('register', fn ($q) => $q->where('family', 'LIKE', '%' . $family . '%'));
        }

        if ($hasFactor !== null) {
            if ($hasFactor == 1) {
                $query->whereHas('factors');
            } elseif ($hasFactor == 0) {
                $query->where('request_type', 'link')->doesntHave('factors');
            }
        }

        if ($sex !== null && $sex !== 'all') {
            $query->whereHas('register', fn ($q) => $q->where('sex', $sex));
        }

        if ($serviceItems) {
            $query->whereHas('factors', function ($q) use ($serviceItems) {
                $q->whereHas('factorItems', fn ($q) => $q->whereIn('service_item_id', $serviceItems));
            });
        }

        // Each bound applies on its own: picking only "from" or only "to" used to be ignored.
        if ($startDate !== null) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate !== null) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($service) {
            $serviceIds = ServiceItem::whereIn('id', $service)->pluck('service_id')->filter()->unique()->all();
            $query->whereHas('factors', function ($q) use ($serviceIds) {
                $q->whereHas('factorItems', function ($q) use ($serviceIds) {
                    $q->where('factoritemable_type', '=', Service::class)->whereIn('factoritemable_id', $serviceIds);
                });
            });
        }

        // Grouped: an ungrouped orWhereHas used to escape every other criterion (and tenancy).
        if ($productCategory) {
            $query->where(function ($q) use ($productCategory) {
                $q->whereHas('factors', function ($q) use ($productCategory) {
                    $q->whereHas('factorItems', function ($q) use ($productCategory) {
                        $q->where('factoritemable_type', '=', Category::class)->whereIn('factoritemable_id', $productCategory);
                    });
                })->orWhereHas('categories', fn ($q) => $q->whereIn('callcenterable_id', $productCategory));
            });
        }

        if ($subServiceCategory) {
            $query->where(function ($q) use ($subServiceCategory) {
                $q->whereHas('factors', function ($q) use ($subServiceCategory) {
                    $q->whereHas('factorItems', function ($q) use ($subServiceCategory) {
                        $q->where('factoritemable_type', '=', Service::class)->whereIn('factoritemable_id', $subServiceCategory);
                    });
                })->orWhereHas('services', fn ($q) => $q->whereIn('callcenterable_id', $subServiceCategory));
            });
        } elseif ($serviceCategory) {
            $serviceIds = Service::whereIn('category_id', $serviceCategory)->pluck('id')->all();
            $query->where(function ($q) use ($serviceIds) {
                $q->whereHas('factors', function ($q) use ($serviceIds) {
                    $q->whereHas('factorItems', function ($q) use ($serviceIds) {
                        $q->where('factoritemable_type', '=', Service::class)->whereIn('factoritemable_id', $serviceIds);
                    });
                })->orWhereHas('services', fn ($q) => $q->whereIn('callcenterable_id', $serviceIds));
            });
        }

        if ($representation !== null && $representation !== 'all') {
            if ($representation === 'center') {
                $query->whereNull('representation_id');
            } else {
                $query->where('representation_id', $representation);
            }
        }

        if ($users) {
            $query->whereIn('user_id', $users);
        }

        if ($agents) {
            $query->whereIn('agent_id', $agents);
        }

        return $query;
    }

    /**
     * Scalar-or-array input -> list of non-empty values, without the "all" placeholder
     * the selects use for "no filter".
     *
     * @return array<int, mixed>
     */
    private static function idList(mixed $value): array
    {
        return array_values(array_filter(
            \Illuminate\Support\Arr::wrap($value),
            fn ($v) => $v !== null && $v !== '' && $v !== 'all' && is_scalar($v)
        ));
    }

    /**
     * Like idList(), but also accepts a JSON-encoded value ("5", "[5,6]") as the area/city
     * pickers send it.
     *
     * @return array<int, mixed>
     */
    private static function decodeIds(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        return self::idList($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function factors()
    {
        return $this->hasMany(Factor::class, 'task_id');
    }

    /**
     * Summary of histories
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<History, Callcenter>
     */
    public function histories()
    {
        return $this->morphMany(History::class, 'historiable');
    }

    /**
     * @param $type
     * @return string
     */
    protected static function type($type)
    {
        //get type callcenter
        return [
            '0' => 'سایت',
            '1' => 'پیامک',
            '2' => 'تماس ورودی',
            '3' => 'دیجیتال',
            '4' => 'مراجعه حضوری',
        ][$type];
    }

    /**
     * @param $status
     * @return string
     */
    public static function status($status)
    {
        return [
            '0' => 'جدید',
            '1' => 'در انتظار پرداخت',
            '2' => 'معلق',
            '3' => 'پرداخت شده',
            '4' => 'نیاز به تماس',
            '5' => 'بسته شده',
            '6' => 'درحال انقضا',
            '7' => 'عودت پرداخت',
            '8' => 'بایگانی',
            '9' => 'منقضی شده',
            '10' => 'اقدام شده',
            '11' => 'انصراف از دریافت پیامک'
        ][$status];
    }
    /**
     * Get all of the categories that are assigned this callcenter.
     */
    public function categories()
    {
        return $this->morphedByMany(Category::class, 'callcenterable','callcenterables');
    }
    /**
     * Get all of the services that are assigned this callcenter.
     */
    public function services()
    {
        return $this->morphedByMany(Service::class, 'callcenterable','callcenterables');
    }
    public function services_item()
    {
        return $this->morphedByMany(ServiceItem::class, 'callcenterable','callcenterables');
    }
    /**
     * Summary of createTask
     * @param User $user
     * @param mixed $description
     * @param mixed $callcenter_roles
     * @return Callcenter
     */
    public static function createTask(User $user, $description , $callcenter_roles)
    {
        $callcenter_user = User::where('level', 'admin')->where('active', 1)->whereHas('roles', function ($query) use ($callcenter_roles) {
            $query->whereIn('id', $callcenter_roles);
        })->first();
        if(!isset($callcenter_user->id))
        {
            $callcenter_user = User::where('level', 'admin')->where('active', 1)->whereHas('roles', function ($query) {
                $query->where('name', 'superadmin');
            })->first();
        }

        $task = Callcenter::create([
            'reg_id' => $user->id,
            'user_id' => $callcenter_user->id,
            'agent_id' => null,
            'type' => 0,
            'request_type' => 'other',
            'status' => 0,
            'description' => $description
        ]);
        History::create($task , $task->user_id , null , $task->status , $description);
        return $task;
    }
    /**
     * @param mixed $phone
     * @param Callcenter $item
     * @return mixed
     */
    public static function setService($phone, Callcenter $item)
    {
        if ($item->request_type == "other") {
            //set last user histories
            $histories = Helper::userHistories($item->register);
            if($histories)
            {
                $last_history = $histories[0];
                return $last_history['description'];
            }
            return $item->description;
        }
        if ($item->categories()->exists() || $item->services()->exists() && !$item->factors()->exists()) {
          $servicesTitle= implode(" - ",  $item->services()->pluck('title')->toArray());
          $categoriesTitle=implode(" - ",  $item->categories()->pluck('title')->toArray());
          $servicesItemTitle=implode(" - ",  $item->services_item()->pluck('title')->toArray());
          return $servicesItemTitle .' - '.$servicesTitle.' - '.$categoriesTitle;
        }

        $user = User::wherePhone($phone)->first();
        if ($user) {
            if ($item->factors->first()) {
                $temp = [];
                foreach ($item->factors as $factor) {
                    foreach ($factor->factorItems as $factor_item) {
                        if (isset($factor_item->factoritemable)) {
                            array_push($temp, $factor_item->factoritemable->title . (isset($factor_item->financialPlansType) ? ' - ' .$factor_item->financialPlansType->title : ''). (isset($factor_item->serviceItem) ? ' - ' . $factor_item->serviceItem->title : ''));
                        } elseif (isset($factor_item->category)) {
                            array_push($temp, $factor_item->category->title);
                        }
                    }
                }
                return implode(',', $temp);
            }
            if ($item->type != 1 && $user->service) {
                 return $user->service->title;
            }
            else{
                return 'ندارد';
            }
        } else {
            return 'ندارد';
        }
    }

    /**
     * @param mixed $phone
     * @param Callcenter $item
     * @return mixed
     */
    public static function setServiceWithoutFinancialPlansType($phone, Callcenter $item)
    {
        if ($item->request_type != "other") {

        if ($item->categories()->exists() || $item->services()->exists() && !$item->factors()->exists()) {
          $servicesTitle= implode(" - ",  $item->services()->pluck('title')->toArray());
          $categoriesTitle=implode(" - ",  $item->categories()->pluck('title')->toArray());
          $servicesItemTitle=implode(" - ",  $item->services_item()->pluck('title')->toArray());
          return $servicesItemTitle .' - '.$servicesTitle.' - '.$categoriesTitle;
        }

        $user = User::wherePhone($phone)->first();
        if ($user) {
            if ($item->factors->first()) {
                $temp = [];
                foreach ($item->factors as $factor) {
                    foreach ($factor->factorItems as $factor_item) {
                        FactorItem::getFactorItemAble($factor_item , $temp);
                    }
                }
                return implode(',', $temp);
            }
            if ($item->type != 1 && $user->service) {
                 return $user->service->title;
            }
            else{
                return 'ندارد';
            }
        } else {
            return 'ندارد';
        }
        }
    }
}
