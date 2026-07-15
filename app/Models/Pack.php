<?php

namespace App\Models;

//use Conner\Tagging\Taggable;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pack extends Model
{
    use SoftDeletes,Sluggable;
//        ,Taggable;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];
    /**
     * @var string[]
     */
    protected $fillable=['title','slug','is_show','description','image','review'];

    /**
     * @return \string[][]
     */
    public function sluggable(): array
    {return ['slug' => ['source' => 'title']];}


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class,'pack_products')
            ->withPivot('id')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function galleries()
    {
        return $this->morphMany(Gallery::class, 'galleryable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function storeRooms()
    {
        return $this->belongsToMany(StoreRoom::class,'pack_store_room')
            ->withPivot('id','category_store_room_id'
                ,'storage_store_room_id','row_num','col_num','discount','order_point','order_day'
                ,'expire_alert','expire_date','measurement_unit_id','storage_num',
                'purchase_type','pay_type', 'stock', 'sales_price', 'purchase_price',
                'barcode','main_unit_id', 'sub_unit_id','main_unit_value','sub_unit_value')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function centers()
    {
        return $this->belongsToMany(Center::class, 'center_pack')
            ->withPivot('id','price','stock','purchase_type','pay_type')->withTimestamps();
    }

}
