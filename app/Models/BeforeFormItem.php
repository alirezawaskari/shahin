<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeforeFormItem extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function beforeformitemable()
   {
      return $this->morphTo();
   }
}
