<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgencyType extends Model
{
    use SoftDeletes;
    protected $fillable=['user_id','title','en_title','slug','image','total_price','prepayment','content','is_show'];
    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */

    public function agencyes()
    {
        return $this->hasMany(agency::class);
    }

    public function sluggable(): array
    {
        return ['slug' => ['source' => 'en_title']];
    }

    public function galleries()
    {
        return $this->morphMany(Gallery::class, 'galleryable');
    }
}
