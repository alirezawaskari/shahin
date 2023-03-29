<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductValidation extends Model
{
    use SoftDeletes ;
    protected $fillable=['product_id','validation_id','field','productvalidationable_id','productvalidationable_type'];

    public function productvalidationable()
    {
        return $this->morphTo();
    }

    public function validation()
    {
        return $this->belongsTo(Validation::class);
    }


}
