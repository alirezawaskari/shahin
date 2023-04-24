<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountingUnit extends Model
{
    /**
     * @var string[]
     */
    protected $fillable=['title' , 'type'];

    /**
     *
     */
    const TYPE = [
        'packing' => 'واحد اصلی',
        'counting' => 'واحد فرعی',
        'measurement' => 'واحد اندازه گیری مصرفی',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function product_storeRoom()
    {
        return $this->hasMany(ProductStoreRoom::class);
    }

    /**
     * @param $id
     * @param $field
     * @return string
     */
    public static function getCountingUnit($id, $field)
    {
        if($id){ $counting_Unit=CountingUnit::find($id); if($counting_Unit){ return $counting_Unit->$field;} }
        return '';
    }
}
