<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Agency extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    /** Document images owned by the agency (national-id card, turnover statement). */
    const MEDIA_FIELDS = ['imgmeli', 'imgturnover'];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $casts = [
        'values' => 'array'
    ];

    public function registerMediaCollections(): void
    {
        foreach (self::MEDIA_FIELDS as $field) {
            $this->addMediaCollection($field)->useDisk('public')->singleFile();
        }
    }

    public function getImgmeliAttribute(): string
    {
        return $this->getFirstMediaUrl('imgmeli');
    }

    public function getImgturnoverAttribute(): string
    {
        return $this->getFirstMediaUrl('imgturnover');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agencyType()
    {
        return $this->belongsTo(AgencyType::class);
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
        $name = request('name');
        $phone = request('phone');
        $province = request('province');
        $city = request('city');
        $status = request('status');
        $agency_type = request('agency_type');

        if (isset($name) && trim($name) != '') {
            $query->where('name', 'LIKE' , '%' . $name . '%');
        }

        if (isset($phone) && trim($phone) != '') {
            $query->where('phone', $phone);
        }

        if (isset($province) && trim($province) != 'all') {
            $query->where('province', 'LIKE' , '%' . $province . '%');
        }

        if (isset($city) && trim($city) != '') {
            $query->where('city', 'LIKE' , '%' . $city . '%');
        }

        if (isset($status) && trim($status) != 'all')
        {
            $query->where('status' , $status);
        }

        if (isset($agency_type) && trim($agency_type) != 'all')
        {
            $query->where('agency_type_id' , $agency_type);
        }

        return $query;
    }

    /**
     * @param $user
     * @param $service
     * @return bool
     */
    protected static function createUpdate($user , $service)
    {
        if(Agency::where('phone' , $user->phone)->first())
        {
            Agency::where('phone' , $user->phone)->update(['updated_at' => now()]);
        }
        else
        {
            Agency::Create([
                'phone' => $user->phone,
                'agency_type_id' => self::getAgencyType($service),
                'name' => $user->name.' '.$user->family,
                'province' => $user->province->name,
                'city' => $user->city->name,
                'updated_at' => now(),
            ]);
        }

        return true;
    }

    /**
     * @param $service
     * @return int|void
     */
    protected static function getAgencyType($service)
    {
        if($service == 17)
        {
            return 5;
        }
        elseif($service == 102)
        {
            return 3;
        }
        elseif($service == 103)
        {
            return 2;
        }
        elseif($service == 104)
        {
            return 1;
        }
        elseif($service == 107)
        {
            return 4;
        }
    }

    //get status agency

    /**
     * @param $status
     * @return string
     */
    protected static function status($status)
    {
        //get status factor
        switch($status)
        {
            case 0:
                return 'جدید';
                break;
            case 1:
                return 'بررسی شده';
                break;
            case 2:
                return 'تایید شده';
                break;
            case 3:
                return 'تعلیق شده';
                break;
            case 4:
                return 'اعطا شده';
                break;
            case 5:
                return 'نیاز به تماس';
                break;
            default :
                return 'جدید';
        }
    }

}
