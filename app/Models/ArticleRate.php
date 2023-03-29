<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleRate extends Model
{
    protected $guarded = [];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
