<?php

namespace App\Models;

use App\Helpers\FilterHelper;
use App\Helpers\Helper;
use App\Services\V1\Auth\AuthService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laratrust\Traits\HasRolesAndPermissions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laratrust\Contracts\LaratrustUser;
class User extends Authenticatable implements LaratrustUser
{
    use HasRolesAndPermissions;
    use Notifiable;
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * @var string[]
     */
    protected $casts = [
        'sms_code' => 'array',
    ];

    /**
     *types of level for super admin type
     */
    const LEVEL = [
        'user' => 'کاربر فعال',
        'blockUser' => 'کاربر غیرفعال(لیست سیاه)',
        'admin' => 'همکار لاله',
        'ghorfeAdmin' => 'همکار غرفه',
        'superAdmin' => 'همکار کل',
    ];
    /**
     * @var string[]
     */
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
        'imgturnover',
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
        'conduct',
        'gender_fix'
    ];

    /**
     * @var string[]
     */
    protected $hidden = [
        'password', 'remember_token', 'melicode', 'shenasnamecode', 'serialcharacter', 'serialtop', 'serialbottom', 'imgmeli', 'imgshenasname', 'avatar', 'imgsignature', 'token', 'api_token',
    ];

    /**
     *
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->token = self::makeUniqueToken();
            $model->api_token = self::makeUniqueApiToken();
            $model->avatar = '/img/user.png';
        });
    }

    /**
     * @return string
     */
    private static function makeUniqueToken()
    {
        do {
            $token = rand(12345, 99999) . rand(12345, 99999) . rand(1, 9) . Carbon::now()->dayOfWeekIso;
            $found = self::where('token', $token)->first();
        } while (!is_null($found));
        return $token;
    }

    /**
     * @return string
     */
    private static function makeUniqueApiToken()
    {
       return Str::random(60);
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        if($this->level == 'admin' || $this->level == 'superAdmin')
        {
            return true;
        }
        elseif($this->level == 'ghorfeAdmin')
        {
            return (new AuthService)->checkUserGhorfe();
        }
        return false;
    }
    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active == '1' ? true : false;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function factors()
    {
        return $this->hasMany(Factor::class)->orderBy('created_at' , 'Desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function creditDetails()
    {
        return $this->hasMany(UserCreditDetail::class)->orderBy('created_at' , 'Desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sessions()
    {
        return $this->hasMany(Session::class , 'agent_id')->orderBy('date', 'ASC');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function factoritemproducts()
    {
        return $this->hasMany(FactorItemProduct::class);
    }
    public function getMorphClass()
    {
        return 'App\Models\User';
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tickets()
    {
        return $this->belongsToMany(Ticket::class,'ticket_user','receiver_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Callcenter::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vip()
    {
        return $this->hasOne(VipUser::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function store_room()
    {
        return $this->hasOne(StoreRoom::class , 'warehouse_keeper_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function supplier()
    {
        return $this->hasOne(Supplier::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function center()
    {
        return $this->belongsTo(Center::class, 'center_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function callcenters()
    {
        return $this->hasMany(Callcenter::class , 'reg_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function callcenter()
    {
        return $this->belongsTo(Callcenter::class, 'reg_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wallets()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function works()
    {
        return $this->hasMany(Work::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function representation()
    {
        return $this->belongsTo(Representation::class);
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
    public function admin_representation()
    {
        return $this->belongsTo(Representation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function histories()
    {
        return $this->hasMany(History::class , 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin_organization()
    {
        return $this->belongsTo(Organization::class , 'admin_organization_id');
    }

    /**
     * @param $query
     * @param $month
     */
    public function scopeSpanningUser($query, $month)
    {
        $query->selectRaw('monthname(created_at) month , count(*) published')
            ->where('created_at', '>', Carbon::now()->subMonth($month))
            ->groupBy('month')
            ->latest();
    }

    /**
     * @param $query
     * @param $dates
     */
    public function scopeSpanningWeekUser($query, $dates)
    {
        $query->selectRaw('DATE(created_at) as date , count(*) published')
            ->where('created_at', '>=', $dates->keys()->first())
            ->groupBy('date')
            ->latest();
    }

    /**
     * @param $query
     * @return mixed
     */
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
        $sex = request('sex');
        $representation = request('representation');
        $organization = request('organization');

        $query = FilterHelper::getDataByCheckGhorfeExistOrNot($query , 'belongsToMany' , null);

         if (auth()->user() && auth()->user()->hasPermission('user-detail')) {
            if (auth()->user()->admin_representation) {
                $query->where('representation_id', auth()->user()->admin_representation->id);
            }
			if (auth()->user()->admin_organization) {
                $query->whereIn('organization_id', auth()->user()->admin_organization->organ_code);
            }
            if (auth()->user()->center_id) {
                $center = auth()->user()->center_id;
                $query->where('center_id', $center)->get();
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
            if (isset($sex) && $sex != 'all') {
                $query->where('sex', $sex);
            }
			if (isset($province) && $province != 'all') {
                $query->where('province_id', $province);
            }
            if (isset($city) && $city != 'all') {
                $query->where('city_id', $city);
            }
            if (isset($startDate) && trim($startDate) != '' && isset($endDate) && trim($endDate) != '') {
                $query->whereDate('created_at', '>=' ,$startDate)->whereDate('created_at', '<=' ,$endDate);
            }
        }
        else{
            $query->where('callcenter_id' , auth()->user()->id);
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

    /**
     * @return bool
     */
    public function getIsAdminAttribute()
    {
        return ($this->level == 'admin' || $this->level == 'ghorfeAdmin') ? true : false;
    }

    /**
     * @param $id
     * @return mixed
     */
    protected static function getUser($id)
    {
        $user = User::find($id);
        return $user;
    }

    /**
     * @param User $user
     * @param $price
     * @param $password
     * @return array
     */
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

    /**
     * @param User $user
     * @param $price
     * @param $password
     * @param $title
     * @return array
     */
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

    /**
     * @return null
     */
    public static function checkManager()
    {
        $user = \Auth::user();
        if($user->center_id != '')
            return $user->center_id ;
        else
            return null;
    }

    /**
     * @param $center_id
     * @return int
     */
    public static function hasDoctor($center_id)
    {
        $users_count=User::with('roles')->whereHas('roles', function ($query)  {
            $query->where('roles.name','doctor');
        })->where('level','admin')->where('center_id',$center_id)->count();
        return $users_count;
    }

    /**
     * @param User $user
     * @return array
     */
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
            if($factorItem->type)
            {
                $totalDebts[] =TotalFactorItem::calculateTotalDebt($factorItem->totalFactorItem);
            }
            else
            {
                $totalDebts[] =FactorItem::calculateTotalDebt($factorItem);
            }
        }
        return $totalDebts;
    }


    /**
     * return total debts data of user
     * @param User $user
     * @return int
     */
    public static function fetchTotalCredit(User $user)
    {
        $factor_items= FactorItem::whereHas('factor',function ($query)use($user){
            $query->whereHas('user', function ($query) use ($user) {
                $query->where('status', 1)->where('id', $user->id);
            });
        })->get();

        $factorItem_credit = 0;
        if($factor_items->count() > 0)
        {
            foreach($factor_items as $item)
            {
                $factorItem_credit = $factorItem_credit + ($item->supervisor->type && $item->supervisor->totalFactorItem ? TotalFactorItem::calculateCredit($item->supervisor->totalFactorItem) : FactorItem::calculateCredit($item->supervisor));
            }
        }
        else
        {
            $factorItem_credit = $factorItem_credit + $user->credit + $user->wallet;
        }


        return $factorItem_credit;
    }
    /**
     * @param $gender
     * @return string
     */
    public static function getGenderTitle($gender)
    {
        return [
            '0' => 'آقای',
            '1' => 'خانم',
            'null' => 'خانم/آقا'
        ][$gender];
    }

    public function diseases()
    {
        return $this->belongsToMany(Disease::class,'disease_user' );
    }

    /**
     * Summary of createUser
     * @param mixed $request
     * @return \Illuminate\Http\Response
     */
    public static function createUser($request)
    {
        $request['phone'] = Helper::numberConverter($request->phone);

        $validate= Validator::make($request->all(), [
            'phone' => 'required|regex:/(09)[0-9]{9}/|digits:11',
            'name' => 'required|string',
            'family' => 'required|string',
            'gender' => ['required',Rule::in(array(0,1))],
            'province' => 'required|numeric|exists:provinces,id',
            'city' => 'required|numeric|exists:cities,id',
        ]);

        if ($validate->fails()){
            return response([
                'status'=>'validation_error',
                'messages'=>$validate->errors()->all()
            ]);
        }
        $user = User::where('phone',$request->phone)->first();
        if (is_null($user)){
           $user = self::storeUser($request->phone,$request->name,$request->family,$request->province,$request->city,$request->gender);
           return response ([
                'status'=>'success',
                'message'=>' کاربر با موفقیت ثبت شد .'
            ]);
        }else{
            return response([
                'status'=>'error',
                'message'=>'این کاربر در سامانه موجود می باشد.'
            ]);
        }
    }
    /**
     * @param $request
     * @return mixed
     */
    private static function storeUser($phone, $name, $family, $province_id, $city_id,$gender)
    {
        $pass = rand(12345, 99999);
        return User::create([
            'name' => $name,
            'family' => $family,
            'phone' => $phone,
            'sex' => $gender,
            'password' => Hash::make($pass),
            'active' => true,
            'level' => 'user',
            'province_id' => $province_id,
            'city_id' => $city_id,
            'representation_id' => auth()->user()->admin_representation_id,
        ]);
    }
    /**
     * @return array
     */
    public static function getSupporterUserAdmin($representation)
    {
        $users = User::whereDoesntHave('ghorfeOnlineLists')->where('admin_representation_id' , $representation)->where( 'level', 'admin')->get();
        $supporter_users = [];
        foreach ( $users as $user ) {
            if ( $user->hasPermission('supporter_new_file') ) {
                array_push( $supporter_users, $user );
            }
        }
        return $supporter_users;
    }
    public function allocatedChildren()
    {
        return $this->hasMany(Employee::class, 'parent_id');
    }

    public function allocatedParent()
    {
        return $this->belongsTo(Employee::class, 'parent_id');
    }

}
