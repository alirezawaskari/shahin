<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractView extends Model
{
    protected $fillable = [
        'contract',
        'contract_type',
        'contract_id'
    ];
}
