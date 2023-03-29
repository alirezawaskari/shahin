<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Validation extends Model
{
    use SoftDeletes ;
    protected $fillable=['title'];

    public function productValidations()
    {
        return $this->hasMany(ProductValidation::class);
    }
}
