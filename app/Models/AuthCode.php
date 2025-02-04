<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuthCode extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table='auth_code';
    /**
     * @var string[]
     */
    protected $fillable=['code','phone','expiration_time','time_code_again'];

}
