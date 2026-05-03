<?php

namespace App\Models;

use App\Helpers\FilterHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];
    /**
     * @var string[]
     */
    protected $fillable=['title','subTitle','en_title','slug','isActive','displayOrder','min_credit'
        ,'max_credit','percent','price','tax','minimum_discount'];

    /**
     * @var array
     */
    protected $guarded = [];

    //start relationShip

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function financialPlansTypeItems()
    {
        return $this->morphMany(FinancialPlansTypeItem::class , 'financialplanstypeItemable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function beforeforms()
    {
        return $this->morphMany(BeforeForm::class, 'beforeformable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function factoritems()
    {
        return $this->morphMany(FactorItem::class, 'factoritemable');
    }
//
//    /**
//     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
//     */
//    public function centers()
//    {
//        return $this->morphMany(Center::class, 'centerable');
//    }
    /**
     * Get all of the centers for the post.
     */
    public function centers()
    {
        return $this->morphToMany(Center::class, 'centerable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function installments()
    {
        return $this->morphMany(Installment::class , 'installmentable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('id')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function related()
    {
        return $this->belongsToMany(Category::class,'related_category','category_id','related_id');
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
    public function brands()
    {
        return $this->belongsToMany(Brand::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attributeGroups()
    {
        return $this->hasMany(AttributeGroup::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sliders()
    {
        return $this->hasMany(Slider::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function menu()
    {
        return $this->morphMany(Menu::class, 'menuable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function couponItems()
    {
        return $this->morphMany(CouponItem::class, 'couponitemable');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function childs()
    {
        return $this->hasMany(Category::class,'parent_id','id') ;
    }

    //category by category many to many

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function similarGroups()
    {
        return $this->belongsToMany(Category::class,'similar_groups', 'similarable_id',
            'relation_id')
            ->withPivotValue(['similar_model' =>Category::class, 'relation_model' => Category::class]);
    }

    //category by service many to many

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function similarGroupsServices()
    {
        return $this->belongsToMany(Service::class,'similar_groups','similarable_id',
            'relation_id')
            ->withPivotValue(['similar_model' =>Category::class, 'relation_model' => Service::class]);
    }


    //category_article by category many to many

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function similarGroupsCategoriesArticle()
    {
        return $this->belongsToMany(Category::class,'similar_groups','similarable_id',
            'relation_id')
            ->withPivotValue(['similar_model' =>CategoryArticle::class, 'relation_model' => Category::class]);
    }


    //Get information from a column of a category - in many files

    /**
     * @param $id
     * @param $field
     * @return string
     */
    public static function findCategory($id, $field){
        $cat=Category::find($id);
        if($cat){ return $cat->$field ;}
        return '';
    }

    //Display the names of a child's parent - use in subCategory.blade

    /**
     * @param $cat_id
     * @return string
     */
    public static function findPath($cat_id)
    {
        $res='';
        $cat = Category::where('id',$cat_id)->select('id','title','path')->first();
        if(isset($cat) && isset($cat->path)){
            $explode=explode('/',$cat->path);
            foreach ($explode as $item) {
                $cat_by_item=Category::where('id',$item)->select('id','title')->first();
                if(isset($cat_by_item))$res=$res . ' / ' .$cat_by_item->title;
            }
        }
        return $res;
    }

    /**
     * @param $cat_id
     * @return mixed
     */
    public static function countChild($cat_id)
    {
        $count = Category::where('parent_id',$cat_id)->count();
        return $count;
    }

    //This category has children or not - in many files

    /**
     * @param $id
     * @return int|null
     */
    public static function hasChild($id){
        if($id != null){
            $cat=Category::find($id);
            if($cat){
                $childes=Category::where('parent_id',$id)->count();
                if($childes && $childes > 0){
                    return 1;
                }
                return 0;
            }
        }
        return null;
    }


    //The last category of a product category - use in ProductController

    /**
     * @param $product
     * @return mixed|null
     */
    public static function latestCategory($product)
    {
        $category_ids = $product->categories()->pluck('category_id');
        foreach ($category_ids as $category_id) {
            if(Category::hasChild($category_id) == 0){
                return $category_id;
            }
        }
        return null;
    }

    //Get all childless categories - use AttributeGroupController and brandController

    /**
     * @return array
     */
    public static function lastCategory()
    {
        $res=[];
        //all category except services
        $service_cat = Category::where('id',2)->select('id','title')->first();
        $service_childes = Category::where('parent_id',$service_cat->id)->select('id','title')->pluck('id');
        foreach(Category::whereNotIn('id',$service_childes)->get() as $cat){
            if(Category::hasChild($cat->id) == 0){
                $res[]=$cat;
            }
        }
        return $res;
    }

    //To show the parents of the category in the category search - use in CategoryController

    /**
     * @param $id
     * @param $category_center_id
     */
    public static function findFather($id, $category_center_id)
    {
        $c = Category::find($id);
        $path=explode('/',$c->path);
        array_shift($path);
        if($category_center_id != null){
            if($path[0] == $category_center_id){
                return Category::select('id','title')->whereIn('id',$path)->orWhere('id',$id)->orderBy('depth','ASC')->get();
            }
        }
        else{
            return Category::select('id','title')->whereIn('id',$path)->orWhere('id',$id)->orderBy('depth','ASC')->get();
        }
    }

    /**
     * @param $id
     * @param $field
     * @return null
     */
    public static function category($id, $field)
    {
        $category=Category::select('id',$field)->where('id',$id)->first();
        if(isset($category->$field)) return $category->$field;
        return null;
    }

    //To get the parents of a category - use in ProductController

    /**
     * @param $id
     * @return string[]
     */
    public static function findAllFather($id)
    {
        $c = Category::find($id);
        $path=explode('/',$c->path);
        return $path;
    }

    //Get all categories by depth = 2 - use ProductController

    /**
     * @param $center_id
     * @return array
     */
    public static function categoryDepth($center_id)
    {
        $category_lists = [] ;
        //all category except services
        $service_cat = Category::where('id',2)->select('id','title')->first();
        $service_childes = Category::where('parent_id',$service_cat->id)->select('id','title')->pluck('id');
        if($center_id != null){
            $category_id = Center::find($center_id)->centerable->id;
            $categories=Category::whereNotIn('id',$service_childes)->where('parent_id',$category_id)->where('depth',2)->get();
        }else{
            $categories=Category::whereNotIn('id',$service_childes)->where('depth',2)->get();
        }

        foreach($categories as $cat){
            $category_lists[]=$cat;
        }
        return $category_lists;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(Service::class,'category_id','id');
    }
  /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ghorfeOnlineLists()
    {
        return $this->belongsToMany(GhorfeOnlineList::class);
    }
    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $query = FilterHelper::getDataByCheckGhorfeExistOrNot($query , 'belongsToMany' , null);
        return $query;
    }
}
