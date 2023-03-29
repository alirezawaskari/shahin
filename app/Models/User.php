<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laratrust\Traits\LaratrustUserTrait;

class User extends Authenticatable
{
    use LaratrustUserTrait;
    use Notifiable;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $casts = [
        'sms_code' => 'array',
    ];

    protected $fillable = [
        'name',
        'family',
        'phone',
        'password',
        'melicode',
        'birth_year',
        'birth_month',
        'birth_day',
        'province_id',
        'city_id',
        'area_id',
        'call',
        'father',
        'shenasnamecode',
        'serialcharacter',
        'serialtop',
        'serialbottom',
        'issuing',
        'postalcode',
        'address',
        'imgmeli',
        'imgshenasname',
        'imgsignature',
        'level',
        'confirm',
        'active',
        'lalecard_code',
        'token',
        'api_token',
        'email_verified_at',
        'activation_code',
        'avatar',
        'email',
        'sex',
        'wallet',
		'credit',
        'limit_count',
        'service_id',
        'center_id',
        'representation_id',
        'admin_representation_id',
		'organization_id',
        'admin_organization_id',
		'part',
        'user_code',
        'type_employ',
        'callcenter_id',
        'work_address',
        'home_phone',
        'work_phone',
        'work',
    ];

    protected $hidden = [
        'password', 'remember_token', 'melicode', 'shenasnamecode', 'serialcharacter', 'serialtop', 'serialbottom', 'imgmeli', 'imgshenasname', 'avatar', 'imgsignature', 'token', 'api_token',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->token = self::makeUniqueToken();
            $model->api_token = self::makeUniqueApiToken();
            $model->avatar = '/img/user.jpg';
        });
    }

    private static function makeUniqueToken()
    {
        do {
            $token = rand(12345, 99999) . rand(12345, 99999) . rand(1, 9) . Carbon::now()->dayOfWeekIso;
            $found = self::where('token', $token)->first();
        } while (!is_null($found));
        return $token;
    }

    private static function makeUniqueApiToken()
    {
       return Str::random(60);
    }

    public function isAdmin()
    {
        return $this->level == 'admin' ? true : false;
    }

    public function isActive()
    {
        return $this->active == '1' ? true : false;
    }

    public function factors()
    {
        return $this->hasMany(Factor::class)->orderBy('created_at' , 'Desc');
    }

    public function creditDetails()
    {
        return $this->hasMany(UserCreditDetail::class)->orderBy('created_at' , 'Desc');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class , 'agent_id')->orderBy('date', 'ASC');
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function factoritemproducts()
    {
        return $this->hasMany(FactorItemProduct::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function tickets()
    {
        return $this->belongsToMany(Ticket::class);
    }

    public function tasks()
    {
        return $this->hasMany(Callcenter::class, 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function vip()
    {
        return $this->hasOne(VipUser::class);
    }

    public function store_room()
    {
        return $this->hasOne(StoreRoom::class , 'warehouse_keeper_id');
    }

    public function supplier()
    {
        return $this->hasOne(Supplier::class);
    }

    public function center()
    {
        return $this->belongsTo(Center::class, 'center_id');
    }
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function callcenters()
    {
        return $this->hasMany(Callcenter::class , 'reg_id');
    }

    public function callcenter()
    {
        return $this->belongsTo(Callcenter::class, 'reg_id');
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function wallets()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function works()
    {
        return $this->hasMany(Work::class);
    }

    public function representation()
    {
        return $this->belongsTo(Representation::class);
    }

    public function admin_representation()
    {
        return $this->belongsTo(Representation::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    public function histories()
    {
        return $this->hasMany(History::class , 'user_id');
    }
    public function admin_organization()
    {
        return $this->belongsTo(Organization::class , 'admin_organization_id');
    }

    public function scopeSpanningUser($query, $month)
    {
        $query->selectRaw('monthname(created_at) month , count(*) published')
            ->where('created_at', '>', Carbon::now()->subMonth($month))
            ->groupBy('month')
            ->latest();
    }

    public function scopeSpanningWeekUser($query, $dates)
    {
        $query->selectRaw('DATE(created_at) as date , count(*) published')
            ->where('created_at', '>=', $dates->keys()->first())
            ->groupBy('date')
            ->latest();
    }

    public function scopeFilter($query)
    {
        $name = request('name');
        $family = request('family');
        $phone = request('phone');
        $melicode = request('melicode');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $level = request('level');
        $province = request('province');
        $city = request('city');
        $role = request('role');
        $representation = request('representation');
        $organization = request('organization');
         if (auth()->user()->hasPermission('user-detail')) {
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
            if (isset($organization) && trim($organization) != 'all') {
                $query->where('organization_id', $organization);
            }
            if (isset($role) && trim($role) != 'all') {
                $query->whereHas('roles',function ($query)use($role){
                    $query->where('id',$role)->where('level','admin');
                });
            }
            if (isset($name) && trim($name) != '') {
                $query->where('name', 'LIKE', '%' . $name . '%');
            }
            if (isset($family) && trim($family) != '') {
                $query->where('family', 'LIKE', '%' . $family . '%');
            }
            if (isset($phone) && trim($phone) != '') {
                $query->where('phone', 'LIKE', '%' . $phone . '%');
            }
            if (isset($melicode) && trim($melicode) != '') {
                $query->where('melicode', 'LIKE', '%' . $melicode . '%');
            }
            if (isset($level) && trim($level) != '' && $level != 'all') {
                if ($level == 'user') {
                    $query->whereLevel('user');
                } elseif ($level == 'admin') {
                    $query->whereLevel('admin');
                }
            }
            if ($province != 'all') {
                $query->where('province_id', $province);
            }
            if ($city != 'all') {
                $query->where('city_id', $city);
            }
            if (isset($startDate) && trim($startDate) != '' && isset($endDate) && trim($endDate) != '') {
                $query->whereDate('created_at', '>=' ,$startDate)->whereDate('created_at', '<=' ,$endDate);
            }
            if (auth()->user()->admin_organization) {
                $query->whereIn('organization_id', auth()->user()->admin_organization->organ_code);
            }
            elseif (auth()->user()->center_id) {
                $center = auth()->user()->center_id;
                $roleName = "doctor";
                $query->whereHas('roles', function ($q) use ($roleName) {
                    $q->where('name', $roleName);
                })->where('center_id', $center)->get();

            }
        }
        else{
			if (isset($organization) && trim($organization) != 'all') {
                $query->where('organization_id', $organization);
            }
            if (isset($name) && trim($name) != '') {
                $query->where('name', 'LIKE', '%' . $name . '%');
            }
            if (isset($family) && trim($family) != '') {
                $query->where('family', 'LIKE', '%' . $family . '%');
            }
            if (isset($phone) && trim($phone) != '') {
                $query->where('phone', 'LIKE', '%' . $phone . '%');
            }
            if (isset($melicode) && trim($melicode) != '') {
                $query->where('melicode', 'LIKE', '%' . $melicode . '%');
            }
            if (isset($startDate) && trim($startDate) != '' && isset($endDate) && trim($endDate) != '') {
                $query->whereDate('created_at', '>=' ,$startDate)->whereDate('created_at', '<=' ,$endDate);
            }
        }

        return $query;
    }

    public function getIsAdminAttribute()
    {
        return $this->level == 'admin' ? true : false;
    }

    protected static function getUser($id)
    {
        $user = User::find($id);
        return $user;
    }

    protected static function checkWallet(User $user , $price , $password)
    {
        if (Hash::check($password , $user->password))
        {
            if ($user->wallet >= $price)
            {
                $user->update(['wallet' => $user->wallet - $price]);
                return ['success' => true , 'message' => 'با موفقیت انجام شد'];
            }
            return ['success' => false , 'message' => 'اعتبار کاربر کافی نمی باشد(اعتبار کاربر:'.number_format($user->wallet).'ریال)'];
        }
        return ['success' => false , 'message' => 'رمز عبور وارد شده صحیح نمی باشد'];
    }

    protected static function checkCredit(User $user , $price , $password , $title)
    {
        if (Hash::check($password , $user->password))
        {
            if ($user->credit >= $price)
            {
                $user->update(['credit' => $user->credit - $price]);
                $user->creditDetails()->create([
                    'price' => $price,
                    'type' => 'decrease',
                    'description' => 'پرداخت'.' '.Factor::TITLE[$title],
                    'registrar_id' => auth()->user()->id,
                ]);
                return ['success' => true , 'message' => 'با موفقیت انجام شد'];
            }
            return ['success' => false , 'message' => 'اعتبار کاربر کافی نمی باشد(اعتبار کاربر:'.number_format($user->credit).'ریال)'];
        }
        return ['success' => false , 'message' => 'رمز عبور وارد شده صحیح نمی باشد'];
    }

    public static function checkManager()
    {
        $user = \Auth::user();
        if($user->center_id != '')
            return $user->center_id ;
        else
            return null;
    }

    public static function hasDoctor($center_id)
    {
        $users_count=User::with('roles')->whereHas('roles', function ($query)  {
            $query->where('roles.name','doctor');
        })->where('level','admin')->where('center_id',$center_id)->count();
        return $users_count;
    }

    public static function fetchDebtItemCount(User $user)
    {
        $debt=array();

        $debt['factorItems_prepayment_debt_count'] = FactorItem::whereHas('factor',function ($query)use($user){
            $query->where('user_id',$user->id)->where('status', 1);
        })->whereHas('property',function ($query){
            $query->where( 'prepayment_debt', '>', 0);
        })->count();

        $debt['factorItems_price_difference_debt_count'] = FactorItem::whereHas('factor',function ($query)use($user){
            $query->where('user_id',$user->id)->where('status', 1);
        })->whereHas('property',function ($query){
            $query->where( 'price_difference_debt', '>', 0);
        })->count();

        $debt['factorItems_installments_debt_count']=FactorItem::whereHas('factors',function ($query)use($user){
            $query->where('user_id',$user->id);
        })->WhereHas('factorItemInstallments',function ($query){
            $query->where('status', 0)->where('date', '<', Carbon::now())->whereHas('factoritem',function ($query){
                $query->where('confirm',1);
            });
        })->count();

        return $debt;
    }

    /**
     * return total debts data of user
     * @param User $user
     * @return array
     */
    public static function fetchTotalDebts(User $user)
    {

        $factorItems= FactorItem::whereHas('factor',function ($query)use($user){
            $query->whereHas('user', function ($query) use ($user) {
                $query->where('status', 1)->where('id', $user->id);
            });
        })->get();

        $totalDebts=array();
        foreach ($factorItems as $factorItem){
            $totalDebts[] =FactorItem::calculateTotalDebt($factorItem);
        }
        return $totalDebts;
    }

    public static function getGenderTitle($gender)
    {
        return [
            '0' => 'آقای',
            '1' => 'خانم',
            'null' => 'خانم/آقا'
        ][$gender];
    }
}
