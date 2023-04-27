<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductValidation extends Model
{
    use SoftDeletes ;

    /**
     * @var string[]
     */
    protected $fillable=['product_id','validation_id','field','productvalidationable_id','productvalidationable_type'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function productvalidationable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function validation()
    {
        return $this->belongsTo(Validation::class);
    }


}
