<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Console\Migrations\StatusCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use Sluggable,SoftDeletes;

    protected $fillable=['title','category_id',
        'en_title','slug','description','content',
        'status', 'isShow','price','image','pay_type',
        'purchase_type','min_credit','max_credit',
        'ability_invite_friend', 'items',
        'center','multiple','percent',
        'demo','content','txt_box_1','left_content'
        ,'img_box_1','thumb','image','icon','image_app'
        ,'background_image','gift','wage_percent','session_attributes','tax'];

    /**
     * @var array
     */
    protected $guarded = [];
    /**
     * @var array
     */
    protected $casts = [
        'after_fields' => 'array',
        'required' => 'array',
        'gift' => 'array',
        'session_attributes' => 'array',
    ];

    public function sluggable():array
    {
        return [
            'slug' => [
                'source' => 'en_title'
            ]
        ];
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function path()
    {
        return "/services/$this->slug";
    }

    public function financialPlansTypes()
    {
        return $this->morphToMany(FinancialPlansType::class , 'financialplanstypeItemable' , 'financial_plans_type_items');
    }

    public function financialPlansTypeItems()
    {
        return $this->morphMany(FinancialPlansTypeItem::class , 'financialplanstypeItemable');
    }

    public function tariffs()
    {
        return $this->hasMany(Tariff::class)->orderBy('id' , 'desc');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function keywords()
    {
        return $this->belongsToMany(Keyword::class);
    }

    public function groupsms()
    {
        return $this->belongsToMany(Groupsms::class);
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class);
    }

    public function representations()
    {
        return $this->belongsToMany(Representation::class);
    }

    public function service_items()
    {
        return $this->hasMany(ServiceItem::class);
    }

    public function installments()
    {
        return $this->morphMany(Installment::class , 'installmentable');
    }

    public function scopeStatus($query)
    {
        return $query->whereStatus(false);
    }

    public function factoritems()
    {
        return $this->morphMany(FactorItem::class, 'factoritemable');
    }

    public function beforeforms()
    {
        return $this->morphMany(BeforeForm::class, 'beforeformable');
    }

    public function centers()
    {
        return $this->morphMany(Center::class, 'centerable');
    }

    public function galleries()
    {
        return $this->morphMany(Gallery::class , 'galleryable');
    }

    //service By service many to many
    public function similarGroups()
    {
        return $this->belongsToMany(Service::class,'similar_groups','similarable_id','relation_id')
            ->withPivotValue(['similar_model' =>Service::class, 'relation_model' => Service::class]);
    }

    //service By category many to many
    public function similarGroupCategories()
    {
        return $this->belongsToMany(Category::class,'similar_groups','similarable_id','relation_id')
            ->withPivotValue(['similar_model' => Service::class, 'relation_model' => Category::class]);
    }

    public function workSamples()
    {
        return $this->morphMany(WorkSample::class, 'worksampleable');
    }

    public function askedQuestions()
    {
        return $this->morphMany(AskedQuestion::class, 'askedquestionable');
    }

    public static function serviceById($id,$field)
    {
        if($id){
            $service = Service::find($id);
            if($service){return $service->$field;}
        }
        return '';
    }

    //get status
    public static function status($status)
    {
        if($status){
           return 'فعال';
        }
        else{
            return 'غیر فعال';
        }
    }

    //get is show site status
    public static function isShow($is_show)
    {
        if($is_show){
           return 'فعال';
        }
        else{
            return 'غیر فعال';
        }
    }

    public function menu()
    {
        return $this->morphOne(Menu::class, 'menuable');
    }
    /**
     * @param Service $item
     * @return mixed
     */
    public static function setRepresentation(Service $item)
    {
        $representation = $item->representations->first();
        if($item->isShow)
        {
            $representation = null;
        }

        return $representation ? $representation->id : null;
    }
}
