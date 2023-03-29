<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketUser extends Model
{
    use SoftDeletes;

    protected $guarded = [];


    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
