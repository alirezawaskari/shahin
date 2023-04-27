<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Validation extends Model
{
    use SoftDeletes ;

    /**
     * @var string[]
     */
    protected $fillable=['title'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productValidations()
    {
        return $this->hasMany(ProductValidation::class);
    }
}
