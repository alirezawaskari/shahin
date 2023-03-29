<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes ;
    protected $dates = ['deleted_at'];

    protected $fillable=['user_id','province_id','lat','lon','city_id','area_id','address','plaque','unit','postal_code','recipient_name','recipient_family','recipient_mobile'];
	
	public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
	public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }
	public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
