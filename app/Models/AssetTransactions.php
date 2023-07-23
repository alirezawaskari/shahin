<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetTransactions extends Model
{
    use SoftDeletes;
    use HasFactory;

    /**
     * @var string[]
     */
    protected $guarded = array('id');

    /**
     * Get receiver
     */
    public function receiverable()
    {
        return $this->morphTo();
    }

}
