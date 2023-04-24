<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageStoreRoom extends Model
{
    /**
     * @param $id
     * @param $field
     * @return string
     */
    public static function getStorageStoreRoom($id, $field)
    {
        if($id){ $cat=StorageStoreRoom::find($id); if($cat){ return $cat->$field;} }
        return '';
    }
}
