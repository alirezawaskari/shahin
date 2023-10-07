<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Center extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable=['centerable_type','centerable_id', 'center_request_id' ,'creator_representation_id','title','address',
        'pay_type','purchase_type','status','is_show','lat','lon','has_subset','type','fullName',
        'background_image','description','province_id','city_id','image', 'center_category_id','user_id'];

    const TYPE = [ 'seller' => 'فروشنده', 'producer' => 'تولید کننده', 'supplier' => 'تامین کننده' ,'خدمات دهنده'=>'service_provider'];
    /**
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

	public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function center_request()
    {
        return $this->belongsTo(CenterRequest::class);
    }
    public function creatorRpresentation()
    {
        return $this->belongsTo(Representation::class , 'creator_presentation_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function representations()
    {
        return $this->belongsToMany(Representation::class);
    }
    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function center_category()
    {
        return $this->belongsTo(CenterCategory::class, 'center_category_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class , 'ticketable_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function centerable()
    {
        return $this->morphTo();
    }

    public function purchaseInvoice()
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function scopeFilter($query)
    {
        $name = request('name');
        $phone = request('phone');
        $province = request('province');
        $type = request('type');
        $service = request('service');
        $category = request('category');
        $status = request('status');

        if (isset($name) && trim($name) != '') {
            $query->where('name', 'LIKE' , '%' . $name . '%');
        }

        if (isset($phone) && trim($phone) != '') {
            $query->where('phone', $phone);
        }

        if (isset($province) && trim($province) != 'all') {
            $query->where('province_id', $province);
        }

        if (isset($status) && trim($status) != 'all') {
            $query->where('status', $status);
        }

        if (isset($type) && trim($type) != 'all') {
            $query->where('type', $type);
        }

        if (isset($category) && trim($category) != 'all') {
            $query->where('center_category_id', $category);
        }

        if (isset($service) && trim($service) != 'all') {
            $query->where('center_category_id', $service);
        }

        return $query;
    }

    public static function status($status)
    {
        switch($status)
        {
            case 0:
                return 'غیر فعال';
                break;
            case 1:
                return 'فعال';
                break;
            default:
            return 'فعال';
        }
    }


    public function serviceTariffs()
    {
        return $this->belongsToMany(ServiceTariff::class)->orderBy('id' , 'desc');
    }

    public function galleries()
    {
        return $this->morphMany(Gallery::class, 'galleryable');
    }

    public static function centerById($id,$field)
    {
        $center = Center::find($id);
        if($center) return $center->$field;
        return null;
    }
    /**
     * Get all of the categories that are assigned this center.
     */
    public function categories()
    {
        return $this->morphedByMany(Category::class, 'centerable');
    }

    /**
     * Get all of the services that are assigned this center.
     */
    public function services()
    {
        return $this->morphedByMany(Service::class, 'centerable');
    }

}
