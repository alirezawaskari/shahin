<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GhorfeOnlineListUser extends Model
{
    public $timestamps = false;
    protected $table = 'ghorfe_online_list_user';

    protected $fillable = ['user_id','ghorfe_online_list_id'];
}
