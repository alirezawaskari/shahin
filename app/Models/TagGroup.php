<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class TagGroup extends Model
{
    use Sluggable;

    protected $table='tagging_tag_groups';

    protected $fillable=['name'];

    public $timestamps=false;


    public function sluggable(): array
    {
        return ['slug' => ['source' => 'name']];
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public static function tagGroup($id)
    {
        if($id){ $TG= TagGroup::find($id); return $TG->name;}
        return '';
    }}
