<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class TagGroup extends Model
{
    use Sluggable;

    /**
     * @var string
     */
    protected $table='tagging_tag_groups';

    /**
     * @var string[]
     */
    protected $fillable=['name'];

    /**
     * @var bool
     */
    public $timestamps=false;


    /**
     * @return \string[][]
     */
    public function sluggable(): array
    {
        return ['slug' => ['source' => 'name']];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * @param $id
     * @return string
     */
    public static function tagGroup($id)
    {
        if($id){ $TG= TagGroup::find($id); return $TG->name;}
        return '';
    }}
