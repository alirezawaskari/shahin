<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountingUnit extends Model
{
    protected $fillable=['title' , 'type'];

    const TYPE = [
        'packing' => 'واحد اصلی',
        'counting' => 'واحد فرعی',
        'measurement' => 'واحد اندازه گیری مصرفی',
    ];

    public function product_storeRoom()
    {
        return $this->hasMany(ProductStoreRoom::class);
    }

    public static function getCountingUnit($id,$field)
    {
        if($id){ $counting_Unit=CountingUnit::find($id); if($counting_Unit){ return $counting_Unit->$field;} }
        return '';
    }
}
