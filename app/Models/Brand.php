<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Brand extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

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
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'logo');
    }

    public function getRegistrationFormAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'registration_form');
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

}
