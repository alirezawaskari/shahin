<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Impunity extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $guarded=['id'];

    /**
     * Get the parent impunitiable
     */
    public function impunitiable()
    {
        return $this->morphTo();
    }

}
