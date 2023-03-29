<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractHistory extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable=['contract_id','user_id','file'];
}
