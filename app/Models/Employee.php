<?php

namespace App\Models;

use App\Helpers\FilterHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Employee extends Model implements HasMedia
{
    /**
     * @var array
     */
    protected $guarded = ['id'];
    use SoftDeletes, InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    /** Touch-to-migrate: legacy single-image columns -> Spatie single-file collections. */
    protected function legacyMediaMap(): array
    {
        return ['imgaccess' => 'imgaccess', 'imglastcertificate' => 'imglastcertificate'];
    }

    /**
     * Spatie single-file collections for the employee document images. Legacy
     * path columns (imgaccess, imglastcertificate) are kept, but reads route
     * through the accessors below.
     *
     * profile_image is the PUBLIC portrait on the partner profile page — deliberately not
     * user.avatar, which is the admin-panel avatar and defaults to a shared placeholder.
     *
     * portfolio is "نمونه کارها": an unlimited multi-image collection, each image carrying
     * its caption in the `title` custom property. Same shape as Service::portfolio, so the
     * admin uses the identical multi-upload widget on both pages.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('imgaccess')->useDisk('public')->singleFile();
        $this->addMediaCollection('imglastcertificate')->useDisk('public')->singleFile();
        $this->addMediaCollection('profile_image')->useDisk('public')->singleFile();
        $this->addMediaCollection('portfolio')->useDisk('public');
    }

    /** Public portrait url, '' when none is set (the frontend then falls back to a default). */
    public function getProfileImageAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'profile_image');
    }

    /** Access image url: Spatie media if present, else the legacy column value (old rows). */
    public function getImgaccessAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'imgaccess')
            ?: ($this->getRawOriginal('imgaccess') ? asset($this->getRawOriginal('imgaccess')) : '');
    }

    /** Last certificate image url: Spatie media if present, else the legacy column value (old rows). */
    public function getImglastcertificateAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'imglastcertificate')
            ?: ($this->getRawOriginal('imglastcertificate') ? asset($this->getRawOriginal('imglastcertificate')) : '');
    }

    /**
     *
     */
    const TYPE = [
        'pay_end_work' => 'پرداخت بعد از اتمام کار',
        'pay_end_session' => 'پرداخت با تکمیل جلسه',
    ];

     /**
     * @param $status
     * @return string
     * get employee status text
     */
    public static function status($status)
    {
        return [
            '0' => 'غیرفعال',
            '1' => 'فعال',
        ][$status];
    }
    /**
     * Summary of contractStatus
     * @param mixed $status
     * @return string
     */
    public static function contractStatus($status)
    {
        return [
            '0' => 'تنظیم نشده',
            '3' => 'پیش نویس',
            '1' => 'تایید شده',
            '2' => 'منتظر تایید قرارداد توسط همکار',
            '4' => 'منتظر تایید قرارداد توسط مدیر',
        ][$status];
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function histories()
    {
        return $this->morphMany(History::class , 'historiable');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function parents()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card()
    {
        return $this->belongsTo(Card::class , 'card_id');
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
        $work_title = request('work_title');
        $status=request('status');
        $role=request('role');

        // Tenancy reads employees.ghorfe_online_list_id — the same column the edit form
        // assigns and GhorfeOnlineList::staff() lists from. It used to run through the USER's
        // ghorfe_online_list_user rows instead, but that pivot answers a different question
        // (which business this ACCOUNT belongs to, customers included), so a person assigned
        // to a business here would not have appeared in its own staff list.
        //
        // A business admin sees its staff; لاله staff see the employees belonging to no
        // business, which is what the column being null means.
        FilterHelper::getDataByCheckGhorfeExistOrNot($query, 'belongsTo', null);

        if ((!isset($status) || $status == 'all') && trim($name) == '' && trim($family) == '' && trim($phone) == '') {
            $query->whereHas('user', function ($query) use ($status) {
                $query->where('status',true);
            });
        }
        if (auth()->user()->admin_representation) {
            $representation = auth()->user()->admin_representation->id;
            $query->whereHas('user', function ($query) use ($representation) {
                $query->where('admin_representation_id', $representation);
            });
        }
        if (isset(request()->parents)){
            $query->whereIn('parent_id',request()->parents);
        }
        if (auth()->user()->center_id) {
            $center = auth()->user()->center_id;
            $query->whereHas('user', function ($query) use ($center) {
                $query->where('center_id', $center);
            });
        }

        if (isset($name) && trim($name) != '') {
            $query->whereHas('user', function ($query) use ($name) {
                $query->where('name', 'LIKE', '%' . $name . '%');
            });
        }

        if (isset($family) && trim($family) != '') {
            $query->whereHas('user', function ($query) use ($family) {
                $query->where('family', 'LIKE', '%' . $family . '%');
            });
        }
        if (isset($role) && trim($role) != 'all') {
            $query->whereHas('user', function ($query) use ($role) {
                $query->whereHas('roles',function ($query)use ($role){
                    $query->where('id',$role);
                });
            });
        }

        if (isset($phone) && trim($phone) != '') {
            $query->whereHas('user', function ($query) use ($phone) {
                $query->where('phone', 'LIKE', '%' . $phone . '%');
            });
        }

        if (isset($status) && $status != 'all') {
            $query->whereHas('user', function ($query) use ($status) {
                $query->where('status',$status);
            });
        }

        if (isset($work_title) && trim($work_title) != '') {
            $query->where('work_title', 'LIKE', '%' . $work_title . '%');
        }

        return $query;
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(Employee::class, 'parent_id');
    }

// بارگذاری بازگشتی
    public function childrenRecursive()
    {
        return $this->hasMany(Employee::class, 'parent_id')->with('childrenRecursive');
    }
    /**
     * @deprecated Dead relation: there is no `service_id` column on `employees`, so this
     * has always resolved to null. Use services() — a partner offers several.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * "خدمات" cards on the partner profile: the services this person offers, each with
     * their own subtitle and starting price (pivot columns).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany(Service::class)
            ->withPivot('subtitle', 'from_price', 'sort')
            ->orderByRaw('employee_service.sort is null, employee_service.sort');
    }

    /**
     * "نمونه کارها" images whose file actually exists on disk, for display surfaces.
     * Edit screens keep iterating getMedia('portfolio') so a broken row stays removable.
     */
    public function portfolioUrls()
    {
        return \App\Helpers\MediaLibraryHelper::existingMedia($this, 'portfolio')
            ->map(fn ($media) => ['url' => $media->getUrl(), 'title' => $media->getCustomProperty('title')]);
    }

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    /**
     * The business this person works for — what puts them on its "درباره ما" page.
     * Independent of center(): the branch is where they sit, this is who they work for.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ghorfeOnline()
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    /**
     * Visitor star ratings of this partner. Same table as Center::ratings(); includes
     * un-moderated rows, so a frontend must add ->approved(). Unordered for the same
     * reason as Center::ratings() — callers that display them add ->latest().
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /** "نام خانوادگی" as one string, '' when the employee has no user row. */
    public function getFullNameAttribute(): string
    {
        return $this->user ? trim($this->user->name . ' ' . $this->user->family) : '';
    }
    public function percentageAllocations()
    {
        return $this->hasManyThrough(
            PercentageAllocationEmployee::class,
            User::class,
            'id',           // foreign key in User table...
            'user_id',      // foreign key in PercentageAllocationEmployee table...
            'user_id',      // local key on Employee table...
            'id'            // local key on User table...
        );
    }

}
