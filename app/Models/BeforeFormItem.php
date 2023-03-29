<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeforeFormItem extends Model
{
   protected $guarded = [];

   public $timestamps = false;

   public function beforeformitemable()
   {
      return $this->morphTo();
   }
}
