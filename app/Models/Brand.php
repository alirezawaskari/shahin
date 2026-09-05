<?php

namespace App\Models;

use App\Helpers\FilterHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Brand extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    /** Touch-to-migrate: legacy single-image columns -> Spatie single-file collections. */
    protected function legacyMediaMap(): array
    {
        return ['logo' => 'logo', 'registration_form' => 'registration_form'];
    }

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];
    /**
     * @var string[]
     */
    protected $fillable=['title','title_en','description','type','logo','link','registration_form','request_by','status'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->useDisk('public')->singleFile();
        $this->addMediaCollection('registration_form')->useDisk('public')->singleFile();
    }

    public function getLogoAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'logo')
            ?: ($this->getRawOriginal('logo') ? asset($this->getRawOriginal('logo')) : '');
    }

    public function getRegistrationFormAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'registration_form')
            ?: ($this->getRawOriginal('registration_form') ? asset($this->getRawOriginal('registration_form')) : '');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @param $id
     * @param $field
     * @return string
     */
    public static function getBrand($id, $field)
    {
        if($id != null){$brand=Brand::find($id);if($brand) return $brand->$field;}
        return '';
    }

    /**
     * change status of brand by current status
     * @return void
     */
    public function changeStatus()
    {
        if ($this->status==1){
            $this->update([
                'status'=>0
            ]);
        }else{
            $this->update([
                'status'=>1
            ]);
        }
    }
    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $category = request('category_id');
        $status = request('status');

        $query->whereHas('categories', function ($query) {
            $query = FilterHelper::getDataByCheckGhorfeExistOrNot($query , 'belongsToMany' , null);
        });
        if (isset($category) && $category != "all"){
            $query->whereHas('categories', function ($query) use ($category)  {
                $query->where('id',$category);
            });
        }
        if (isset($status) && $status != "all"){
            $query->where('status',$status);
        }
        return $query;
    }
}
