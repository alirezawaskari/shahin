<?php

namespace App\Models;

use App\Helpers\Helper;
//use Conner\Tagging\Taggable;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;
use File;

class  Product extends Model
{
    use Sluggable,SoftDeletes;
//    use Taggable;
    /**
     *type of products
     */
    const TYPE = [
        '0' => 'مواد اولیه مصرفی',
        '1' => 'کالا',
    ];

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * @var string[]
     */
    protected $fillable=['name','en_name','slug','brand_id','isShow','is_sale','internal_use','review'
        ,'root_category_id','type','status', 'center_id','description','length','width','height','weight',
        'reference_price','message','status'];

    /**
     *status of products
     */
    const STATUS = [
        '1' => 'پیش نویس',
        '2' => 'بررسی مجدد',
        '3' => 'در انتظار تایید',
        '4' => 'ویرایش پس از تایید',
        '5' => 'تایید شده',
        '6' => 'بررسی مجدد بعد از تایید',
        '7' => 'تکراری',
        '8' => 'حذف',
    ];

    /**
     * @return \string[][]
     */
    public function sluggable(): array
    {return ['slug' => ['source' => 'name']];}

    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    //start relationShip

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class)->withPivot('id')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function factor_items()
    {
        return $this->belongsToMany('App\FactorItem')->withTimestamps()->withPivot('value' , 'id' ,'session_number' , 'treatment_duration' , 'user_id' , 'status' , 'price' , 'discount' , 'grouping');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function attributes()
    {
        return $this->belongsToMany(Attribute::class , 'product_attributes')
            ->withTimestamps()->withPivot('id','value' , 'attribute_item_id','price','stock','initial_stock'
                ,'productattributeable_id','productattributeable_type','guarantee','posted_by','is_show','submission_time'
                ,'seller_code');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function centers()
    {
        return $this->belongsToMany(Center::class, 'center_product')
            ->withPivot('id','user_id','purchase_type','pay_type','price','stock')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function storeRooms()
    {
        return $this->belongsToMany(StoreRoom::class,'product_store_room')
            ->withPivot('id','user_id','category_store_room_id'
                ,'storage_store_room_id','row_num','col_num','discount','order_point','order_day'
                ,'expire_alert','expire_date','measurement_unit_id','storage_num',
                'purchase_type','pay_type', 'stock', 'sales_price', 'purchase_price',
                'barcode','main_unit_id', 'sub_unit_id','main_unit_value','sub_unit_value','purchase_invoice_id' ,'initial_stock')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function galleries()
    {
        return $this->morphMany(Gallery::class, 'galleryable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function changeStoreRooms()
    {
        return $this->hasMany(ChangeStoreRoom::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ghorfeOnline()
    {
        return $this->belongsToMany(GhorfeOnlineList::class, 'ghorfe_online_list_product')
            ->withPivot('id','user_id','purchase_type','pay_type','price','stock')->withTimestamps();
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productValidations()
    {
        return $this->hasMany(ProductValidation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeStatus($query)
    {
        return $query->whereStatus(false);
    }

    /**
     * @param $item
     * @return string|void
     */
    protected static function productSize($item)
    {
        $height = $item->height;
        $lenght = $item->lenght;
        $width = $item->width;
        $weight = $item->weight;

        if($weight >= 15 || $lenght >= 65 || $width >= 35 || $height >= 45)
        {
            return "بزرگ";
        }

        if($weight >= 15 || $lenght >= 65 || $width >= 35 || $height >= 45)
        {
            return "متوسط";
        }

        if($weight < 15 || $lenght < 65 || $width < 35 || $height < 45)
        {
            return "کوچک";
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pack()
    {
        return $this->belongsTo(Pack::class);
    }



    /**
     * Remove deleted tags and add new tags - use in ProductController and CenterController
     * @param $list_tags_product
     * @param $request_tags
     * @param $product
     */
    public static function UpdateTag($list_tags_product, $request_tags, $product)
    {
        if ($list_tags_product != []) {
            if ($list_tags_product && $request_tags) {
                $removing_tag = array_diff($list_tags_product, $request_tags);
                $new_tags = array_diff($request_tags, $list_tags_product);
                $product->untag($removing_tag);
                $product->tag($new_tags);
            }
        } else {
            if (isset($request_tags) && $request_tags)
                $product->tag(['tag_name' => $request_tags]);
        }
    }



    /**
     * store new images - use in ProductController and CenterController
     * @param $image_path
     * @param $multiple_image
     * @param $product
     */
    public static function storeImagesProduct($image_path, $multiple_image, $product){
        if (isset($image_path)) {
            if (\File::exists(public_path($product->image))) {
                \File::delete(public_path($product->image));
            }
            $product->image = Helper::moveImage($image_path, 'product');
            $product->save();
        }

        $files = $multiple_image;
        if ($files != '') {
            foreach ($files as $extra_file) {
                if (isset($extra_file['file'])) {
                    $image = Helper::moveImage($extra_file['file'], 'product');
                    Gallery::create(['galleryable_id' => $product->id,
                        'galleryable_type' => Product::class, 'title' => $extra_file['title'], 'image' => $image]);
                }
            }
        }
    }

    //use in ProductController

    /**
     * @param $request
     * @param $product
     * @param $productattributeable_id
     * @param $productattributeable_type
     */
    public static function saveReviewsGood($request, $product, $productattributeable_id, $productattributeable_type)
    {
        if (is_array($productattributeable_id)) {
            //ids of centers or store rooms
            foreach ($productattributeable_id as $ids) {
                //reviewstrength
                if (isset($request->reviewstrength)) {
                    foreach ($request->reviewstrength as $value) {
                        if ($value['text'] != '') {
                            Review::create(['product_id' => $product->id, 'type' => 0, 'text' => $value['text']
                                , 'reviewable_id' => $ids, 'reviewable_type' => $productattributeable_type]);
                        }

                    }
                }

                //reviewWeakness
                if (isset($request->reviewWeakness)) {
                    foreach ($request->reviewWeakness as $value) {
                        if ($value) {
                            if ($value['text'] != '') {
                                Review::create(['product_id' => $product->id, 'type' => 1, 'text' => $value['text']
                                    , 'reviewable_id' => $ids, 'reviewable_type' => $productattributeable_type]);
                            }
                        }
                    }
                }
            }
        } else {
            //reviewstrength
            if (isset($request->reviewstrength)) {
                foreach ($request->reviewstrength as $value) {
                    if ($value['text'] != '') {
                        Review::create(['product_id' => $product->id, 'type' => 0, 'text' => $value['text']
                            , 'reviewable_id' => $productattributeable_id, 'reviewable_type' => $productattributeable_type]);
                    }
                }
            }

            //reviewWeakness
            if (isset($request->reviewWeakness)) {
                foreach ($request->reviewWeakness as $value) {
                    if ($value['text'] != '') {
                        Review::create(['product_id' => $product->id, 'type' => 1, 'text' => $value['text']
                            , 'reviewable_id' => $productattributeable_id, 'reviewable_type' => $productattributeable_type]);
                    }
                }
            }
        }
    }

    /**
     * @param $request
     * @param $product
     * @param $productattributeable_id
     * @param $productattributeable_type
     */
    public static function saveAttributeGood($request, $product, $productattributeable_id, $productattributeable_type)
    {
        //attribute
        if (isset($request['attribute_multi']) && $request['attribute_multi'] != null) {
            foreach ($request['attribute_multi'] as $index_attr => $attributes_multi) {
                foreach ($attributes_multi as $index => $value) {
                    if ($value != null) {
                        $item_title = AttributeItem::getAttributeItem($value, 'title');
                        //ids of centers or store rooms
                        if (is_array($productattributeable_id)) {
                            foreach ($productattributeable_id as $ids) {
                                ProductAttribute::create(['attribute_id' => $index_attr, 'attribute_item_id' => $value,
                                    'value' => $item_title, 'product_id' => $product->id
                                    , 'productattributeable_id' => $ids,
                                    'productattributeable_type' => $productattributeable_type]);
                            }
                        }
                        else {
                            ProductAttribute::create(['attribute_id' => $index_attr, 'attribute_item_id' => $value,
                                'value' => $item_title, 'product_id' => $product->id
                                , 'productattributeable_id' => $productattributeable_id, 'productattributeable_type' => $productattributeable_type]);
                        }
                    }
                }
            }
        }

        if (isset($request['attr_create'])) {
            foreach ($request['attr_create'] as $index => $item) {
                if ($item != null) {
                    if (is_array($productattributeable_id)) {
                        //ids of centers or store rooms
                        foreach ($productattributeable_id as $ids) {
                            ProductAttribute::create(['attribute_id' => $index, 'attribute_item_id' => 0,
                                'value' => $item, 'product_id' => $product->id
                                , 'productattributeable_id' => $ids, 'productattributeable_type' => $productattributeable_type]);
                        }
                    } else {
                        ProductAttribute::create(['attribute_id' => $index, 'attribute_item_id' => 0,
                            'value' => $item, 'product_id' => $product->id
                            , 'productattributeable_id' => $productattributeable_id, 'productattributeable_type' => $productattributeable_type]);
                    }

                }
            }
        }

    }

    /**
     * @param $request
     * @param $product
     * @param $productattributeable_id
     * @param $productattributeable_type
     */
    public static function updateAttributeGood($request, $product, $productattributeable_id, $productattributeable_type)
    {
        if (isset($request['attribute_multi']) && $request['attribute_multi'] != null) {
            foreach ($request['attribute_multi'] as $index_attr => $attributes_multi) {
                foreach ($attributes_multi as $index => $value) {
                    if ($value != null) {
                        $attribute_item_id = $product->attributes()->where('attribute_id',$index_attr)->pluck('attribute_item_id')->toArray();
                        $attribute_item_id_new = $attributes_multi;
                        $removing_attribute_item=array_diff($attribute_item_id,$attribute_item_id_new);
                        $new_attribute_item=array_diff($attribute_item_id_new,$attribute_item_id);

                        if (is_array($productattributeable_id)) {
                            foreach ($productattributeable_id as $ids) {
                                foreach ($new_attribute_item as $item) {
                                    $item_title = AttributeItem::getAttributeItem($item, 'title');

                                    $has_f=$product->attributes()->where('attribute_item_id',$value)
                                        ->where('productattributeable_id',$ids)
                                        ->where('productattributeable_type',$productattributeable_type)->count();
                                    if($has_f == 0){
                                        $product->attributes()->attach($index_attr, [
                                            'attribute_item_id' => $value, 'value' => $item_title
                                            ,'productattributeable_id' => $ids,
                                            'productattributeable_type' => $productattributeable_type
                                        ]);
                                    }
                                }
                            }
                        }

                        if($removing_attribute_item != null){
                            $list_remove=$product->attributes()->whereIn('attribute_item_id',$removing_attribute_item)
                                ->where('productattributeable_id',$ids)
                                ->where('productattributeable_type',$productattributeable_type)->get();

                            foreach ($list_remove as $item) {
                                $item->pivot->delete();
                            }
                        }

                    }
                }

            }
        }

        if (isset($request['attr_create'])) {
            foreach ($request['attr_create'] as $index => $item) {
                if ($item != null) {
                    if (is_array($productattributeable_id)) {
                        //ids of centers or store rooms
                        foreach ($productattributeable_id as $ids) {
                            ProductAttribute::updateOrCreate(['product_id' => $product->id, 'attribute_id' => $index,
                                'productattributeable_id' => $ids, 'attribute_item_id' => 0,
                                'productattributeable_type' => $productattributeable_type],
                                ['value' => $item]);
                        }
                    } else {

                        ProductAttribute::updateOrCreate(['product_id' => $product->id, 'attribute_id' => $index,
                            'productattributeable_id' => $productattributeable_id, 'attribute_item_id' => 0,
                            'productattributeable_type' => $productattributeable_type],
                            ['value' => $item]);
                    }

                }
            }
        }

    }

    /**
     * @param $request
     * @param $product
     */
    public static function saveImageGood($request, $product)
    {
        if (isset($request->image_name_good)) {
            foreach ($request->image_name_good as $image_name) {

                if (isset($image_name['name'])) {
                    $img_detail = explode('/', $image_name['name']);
                    $year = $img_detail[3];
                    $month = $img_detail[4];
                    $img_name = $img_detail[6];
//                    $path = 'upload/product/' . $year . '/' . $month;
                    $path = 'upload/' . $year . '/' . $month.'/product/';

                    if (!is_dir($path)) {
                        File::makeDirectory($path, 0777, true, true);
                        File::move(public_path($image_name['name']), public_path($path . '/' . $img_name));
                    } else {
                        File::move(public_path($image_name['name']), public_path($path . '/' . $img_name));
                    }

                }
                if (isset($image_name['index']) && isset($path) && isset($img_name)) {
                    $product->image = '/' . $path . '/' . $img_name;
                    $product->save();
                }
                if (!isset($image_name['index']) && isset($path) && isset($img_name)) {
                    Gallery::create(['galleryable_id' => $product->id,
                        'galleryable_type' => Product::class,
                        'title' => $request->good['name'], 'image' => '/' . $path . '/' . $img_name]);
                }

            }
        }
    }

    /**
     * @param $request
     */
    public static function validationStoreRoomGood($request)
    {
        $request->validate(
            [
                'good.name' => 'required', 'choose_category_id' => 'required', 'good.brand_id' => 'required',
                'good.length' => 'required',
                'good.width' => 'required',
                'good.height' => 'required',
                'good.weight' => 'required',
                'good.category_store_room_id' => 'required',
                'good.store_room_id' => 'required',
                'good.storage_store_room_id' => 'required',
                'good.row_num' => 'required', 'good.col_num' => 'required',
                'good.order_point' => 'required',
                'good.order_day' => 'required',
                'good.expire_alert' => 'required',
                'good.expire_date' => 'required',
                'good.purchase_type' => 'required', 'good.measurement_unit_id' => 'required',
                'good.pay_type' => 'required',
                'good.purchase_price' => 'required',
                'good.main_unit_id' => 'required',
                'good.sub_unit_id' => 'required',
                'good.main_unit_value' => 'required',
                'good.sub_unit_value' => 'required',
                'good.storage_num' => 'required',
            ],
            [
                'good.name.required' => 'لطفا عنوان را وارد کنید',
                'good.brand_id.required' => 'لطفا برند را وارد کنید',
                'choose_category_id.required' => 'لطفا گروه کالا را انتخاب کنید',
                'good.length.required' => 'لطفا طول را انتخاب کنید',
                'good.width.required' => 'لطفا عرض را انتخاب کنید',
                'good.height.required' => 'لطفا ارتفاع را انتخاب کنید',
                'good.weight.required' => 'لطفا وزن را انتخاب کنید',
                'good.category_store_room_id.required' => 'لطفا دسته بندی انبار را انتخاب کنید',
                'good.store_room_id.required' => 'لطفا انبار را انتخاب کنید',
                'good.storage_store_room_id.required' => 'لطفا محل نگهداری را انتخاب کنید',
                'good.row_num.required' => 'لطفا شماره سطر را وارد کنید',
                'good.col_num.required' => 'لطفا شماره ستون را وارد کنید',
                'good.order_point.required' => 'لطفا نقطه سفارش را وارد کنید',
                'good.order_day.required' => 'لطفا مدت زمان سفارش را وارد کنید',
                'good.expire_alert.required' => 'لطفا اعلام انقضا را وارد کنید',
                'good.expire_date.required' => 'لطفا تاریخ انقضا را وارد کنید',
                'good.measurement_unit_id.required' => 'لطفا واحد اندازه گیری مصرفی را انتخاب کنید',
                'good.purchase_type.required' => 'لطفا نوع خرید را انتخاب کنید',
                'good.pay_type.required' => 'لطفا نوع پرداخت را انتخاب کنید',
                'good.purchase_price.required' => 'لطفا قیمت خرید را وارد کنید',
                'good.main_unit_id.required' => 'لطفا واحد اصلی را انتخاب کنید',
                'good.main_unit_value.required' => 'لطفا  تعداد واحد اصلی را وارد کنید',
                'good.sub_unit_id.required' => 'لطفا واحد فرعی را انتخاب کنید',
                'good.sub_unit_value.required' => 'لطفا تعداد واحد فرعی را وارد کنید',
                'good.storage_num.required' => 'لطفا شماره محل نگهداری را وارد کنید',
            ]
        );
    }

    /**
     * @param $request
     */
    public static function validationCenterGoodAdmin($request)
    {
        $request->validate([
            'good.name' => 'required',
            'good.brand_id' => 'required',
            'choose_category_id' => 'required',
            'good.center_id' => 'required',
            'good.length' => 'required',
            'good.width' => 'required',
            'good.height' => 'required',
            'good.weight' => 'required',
        ],
            [
                'good.name.required' => 'لطفا عنوان را وارد کنید',
                'good.brand_id.required' => 'لطفا برند را انتخاب کنید',
                'choose_category_id.required' => 'لطفا گروه کالا را انتخاب کنید',
                'good.center_id.required' => 'لطفا مرکز را انتخاب کنید',
                'good.length.required' => 'لطفا طول را انتخاب کنید',
                'good.width.required' => 'لطفا عرض را انتخاب کنید',
                'good.height.required' => 'لطفا ارتفاع را انتخاب کنید',
                'good.weight.required' => 'لطفا وزن را انتخاب کنید',
            ]
        );
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function countPriceProduct($id)
    {
        $product=Product::find($id);
        $count=$product->attributes()->where('is_price',1)->wherePivot('is_show',1)->count();
        return $count;
    }

    /**
     * @param $id
     * @param $status_id
     * @return string|null
     */
    public static function messageStatus($id, $status_id)
    {
        if($status_id == 7){
            $msg = 'کالای شما با کالایی که قبلا در سایت وجود داشته با شماره';
            $msg = $msg . ' ' . '<a class="padRL10" href="https://lalecard.com/product/'.$id.'">'.$id.'</a>'   ;
            $msg = $msg . ' مشابه است . لطفا از این کالا برای قیمت گذاری استفاده کنید.';

            return $msg;
        }
        return null;
    }

    /**
     * @param $field
     * @param $product_validation
     * @return null
     */
    public static function showErrorProduct($field, $product_validation)
    {
        $product_validation = $product_validation->where('field',$field)->first();
        if(isset($product_validation) && $product_validation->validation)
            return $product_validation->validation->title;
        else
            return null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    //use in edit product
    /**
     * @param $date
     * @return string|null
     * @throws \Exception
     */
    public static function dateToMiladi($date)
    {
        if($date != null){
            $date = Helper::convert($date);
            $verta_date = Verta::parse( $date );
            $verta_date = $verta_date->formatGregorian('Y-m-d H:i:s');
            return $verta_date;
        }
      return null;
    }

}
