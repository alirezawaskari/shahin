<?php

namespace App\Models;

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
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $name = request('name');
        $family = request('family');
        $description = request('description');
        $phone = request('user_phone');
        $type = request('type');
        $request_type = request('request_type');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $status = request('status');
        $users = request('users');
        $agents = request('agents');
        $service = request('service');
        $representation = request('representation');
        $province = request('province');
        $city = json_decode(request('city'));
        $area = json_decode(request('area'));
        $productCategory = request('product_category');
        $serviceCategory = request('service_category');
        $subServiceCategory = request('sub_service_category');
        $serviceItems = request('service_items');
        $has_factor = request('has_factor');

        if (isset($status) && !is_null($status) && is_array($status)) {
            $query->whereIn('status',$status);
        }

        if (isset($type) && !is_null($type)  && is_array($type) ) {
            $query->whereIn('type', $type);
        }

        if (isset($request_type) && trim($request_type) != '' && $request_type != 'all') {
            $query->where('request_type', $request_type);
        }

        if ($province!='all' && $province != null) {
            $query->whereHas('register', function ($query) use ($province) {
                $query->where('province_id', $province);
            });
        }
        if ($province!='all' && $city != null) {
            $query->whereHas('register', function ($query) use ($city) {
                $query->where('city_id', $city);
            });
        }

        if (isset($phone) && trim($phone) != '') {
            if ( is_null($type) || count(array_intersect($type, [2,3,4])) > 0  ) {
                $query->where(function ($query) use ($phone , $name){
                    $query->whereHas('register', function ($query) use ($phone , $name) {
                        $query->where('phone', $phone);
                    })->orWhereHas('sms', function ($query) use ($phone) {
                        $query->where('sender_number', $phone);
                    });
                });
            }
            if (is_array($type) && in_array(0,$type)) {
                $query->whereHas('register', function ($query) use ($phone,$name,$family) {
                    $query->where('phone', $phone)->
                    where('name', 'LIKE', '%'.$name.'%')->
                    where('family', 'LIKE', '%'.$family.'%');
                });
            }
            if (is_array($type) && in_array(1,$type)) {
                $query->whereHas('sms', function ($query) use ($phone) {
                    $query->where('sender_number', $phone);
                });
            }
        }

        if (isset($name) && trim($name) != '') {
            $query->whereHas('register', function ($query) use ($name) {
                $query->where('name', 'LIKE', '%'.$name.'%');
            });
        }

        if (isset($has_factor) && trim($has_factor) != '') {
                if ($has_factor == 1 ){
                    $query->whereHas('factors');
                }elseif ($has_factor == 0){
                    $query->where('request_type', 'link')->doesntHave('factors');
                }
        }

        if (isset($family) && trim($family) != '') {
            $query->whereHas('register', function ($query) use ($family) {
                $query->where('family', 'LIKE', '%'.$family.'%');
            });
        }

        if (isset($description) && trim($description) != '') {
            $query->where('description', 'LIKE', '%'.$description.'%');
            $query->whereHas('histories', function ($query) use ($description) {
                $query->where('description', 'LIKE', '%'.$description.'%');
            });
        }

        if (isset($serviceItems) && is_array($serviceItems) ) {
            $query->whereHas('factors', function ($query) use ($serviceItems) {
                $query->whereHas('factorItems', function ($query) use ($serviceItems) {
                    $query->whereIn('service_item_id',$serviceItems);
                });
            });
        }

        if (isset($startDate) && trim($startDate) != '' && isset($endDate) && trim($endDate) != '') {
            $query->whereDate('created_at', '>=' ,$startDate)->whereDate('created_at', '<=' ,$endDate);
        }

        if ($area != null) {
            $query->whereHas('register', function ($query) use ($area) {
                $query->where('area_id', $area);
            });
        }

        if (isset($service) && trim($service) != 'all') {
            $service = ServiceItem::find($service)->service->id;
            $query->where(function ($query) use ($service){
            $query->whereHas('factors', function ($query) use ($service) {
                $query->whereHas('factor_items', function ($query) use ($service) {
                    $query->where('service_id', '=', $service);
                });
            })->orWhereHas('register' , function ($query) use ($service) {
                $query->where('service_id', '=', $service);
            });
            });
        }

        if (isset($productCategory) && trim($productCategory) != 'all') {
            $query->whereHas('factors', function ($query) use ($productCategory) {
                $query->whereHas('factorItems', function ($query) use ($productCategory) {
                    $query->where('factoritemable_type', '=', Category::class)->where('factoritemable_id',$productCategory);
                });
            })->orWhereHas('categories',function ($query)use($productCategory){
                $query->where('callcenterable_id',$productCategory);
            });
        }

        if (isset($subServiceCategory) && $subServiceCategory != 'all'){
            $query->where(function ($query) use ($subServiceCategory){
            $query->whereHas('factors', function ($query) use ($subServiceCategory) {
                $query->whereHas('factorItems', function ($query) use ($subServiceCategory) {
                    $query->where('factoritemable_type', '=', Service::class)->where('factoritemable_id',$subServiceCategory);
                });
            })->orWhereHas('register' , function ($query) use ($subServiceCategory) {
                $query->where('service_id', '=', $subServiceCategory);
            })->orWhereHas('services',function ($query)use($subServiceCategory){
                $query->where('callcenterable_id',$subServiceCategory);
            });
        });
        }

        if ( (is_null($subServiceCategory) || $subServiceCategory == 'all' ) && isset($serviceCategory) && $serviceCategory!='all' ){
            $serviceCategory=Category::find($serviceCategory);
            $query->where(function ($query) use ($serviceCategory){
            $query->whereHas('factors', function ($query) use ($serviceCategory) {
                $query->whereHas('factorItems', function ($query) use ($serviceCategory) {
                    $query->where('factoritemable_type', '=', Service::class)->whereIn('factoritemable_id',$serviceCategory->services->pluck('id')->toArray());
                });
            })->orWhereHas('services',function ($query)use($serviceCategory){
                $query->whereIn('callcenterable_id',$serviceCategory->services->pluck('id')->toArray());
            })->whereHas('register' , function ($query) use ($serviceCategory) {
                $query->whereIn('service_id', $serviceCategory->services->pluck('id')->toArray());
            });
        });
        }

        if(auth()->user()->center_id)
		{
            $query->where(function ($query){
			$query->whereHas('factors', function ($query)  {
                $query->whereHas('factorItems', function ($query)  {
                    $query->where('factoritemable_id', auth()->user()->center->services->pluck('id')->toArray());
                    });
                })->orWhereHas('register' , function ($query) {
                    $query->whereIn('service_id', auth()->user()->center->services->pluck('id')->toArray());
                })->orWhereHas('callcenter',function ($query){
                    $query->whereHas('employee',function ($query){
                       $query->where('parent_id',auth()->user()->employee->id);
                });
                })->orWhere('agent_id', auth()->user()->id)->orWhere('user_id', auth()->user()->id);
            });
		}

        if (auth()->user()->hasPermission('callcenter-detail')) {

            if (auth()->user()->admin_representation) {
                $query->where('representation_id', auth()->user()->admin_representation->id);
            }
            if (isset($representation) && trim($representation) != 'all') {
                if(trim($representation) == 'center')
                {
                    $query->where('representation_id', null);
                }
                else
                {
                    $query->where('representation_id', $representation);
                }
            }

            if (isset($users) && count($users) > 0) {
                $query->whereIn('user_id',$users);
            }

            if (isset($agents) && count($agents) > 0) {
                $query->whereIn('agent_id',$agents);
            }

        }
        elseif(auth()->user()->hasPermission('callcenter'))
        {
                $query->whereHas('callcenter',function ($query){
                    $query->Where('user_id', auth()->user()->id)->orWhere('agent_id', auth()->user()->id);
                    $query->orWhereHas('employee',function ($query){
                       $query->where('parent_id',auth()->user()->employee->id);
                    });
            });
        }

        return $query;
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function factors()
    {
        return $this->hasMany(Factor::class, 'task_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
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
    /**
    * @param \App\Models\User $user
    * @param mixed $description
    * @param array $callcenter_roles
    * @return viod
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

        $task->histories()->create([
            'user_id' => auth()->user()->id,
            'callcenter_id' => $callcenter_user->id,
            'status' => $task->status,
            'description' => $description,
        ]);
    }
}
