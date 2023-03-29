<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryArticle extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable=['title'];

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    //category_article by service many to many
    public function similarGroupsServices()
    {
        return $this->belongsToMany(Service::class,'similar_groups','similarable_id',
            'relation_id')
            ->withPivotValue(['similar_model' =>CategoryArticle::class, 'relation_model' => Service::class]);
    }

    //category_article by category many to many
    public function similarGroupsCategories()
    {
        return $this->belongsToMany(Category::class,'similar_groups','similarable_id',
            'relation_id')
            ->withPivotValue(['similar_model' =>CategoryArticle::class, 'relation_model' => Category::class]);
    }

}
