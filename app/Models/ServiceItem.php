<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ServiceItem extends Model implements AuditableContract
{
    use Auditable;
    use Sluggable,SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable=['service_id','title','en_title','description','slug','status','isShow',
        'content','image','icon','thumb','image_app','img_box_1','txt_box_1','left_content'];
    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];
    /**
     * @var array
     */
    protected $guarded = [];
   /**
     * @return \string[][]
     */
    public function sluggable():array
    {
        return [
            'slug' => [
                'source' => 'en_title',
                'onUpdate' => true,
            ]
        ];
    }
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
    /**
     * @return string
     */
    public function path()
    {
        return "/serviceItem/$this->slug";
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function askedQuestions()
    {
        return $this->morphMany(AskedQuestion::class, 'askedquestionable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function workSamples()
    {
        return $this->morphMany(WorkSample::class, 'worksampleable');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function galleries()
    {
        return $this->morphMany(Gallery::class, 'galleryable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function menu()
    {
        return $this->morphMany(Menu::class, 'menuable');
    }


}
