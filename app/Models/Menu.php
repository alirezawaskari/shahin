<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table='menu';

    protected $fillable=['menuable_id','menuable_type','parent_id','position','title','depth','duplicate','duplicate_subset'];

    public function menuable()
    {
        return $this->morphTo();
    }

    public static function hasCatInMenu($cat_id,$parent_id)
    {
        return Menu::where('menuable_id',$cat_id)->where('menuable_type',Category::class)
            ->where('parent_id',$parent_id)->count();
    }

    public static function findMenu($cat_id,$parent_id,$type,$field)
    {
        $menu=Menu::where('menuable_id',$cat_id)->where('menuable_type',$type)->where('parent_id',$parent_id)->first();
        if($menu){ return $menu->$field ; }
        return null;
    }

    //relationShip
    public function childs()
    {
        return $this->hasMany(Menu::class,'parent_id','menuable_id')
            ->orderBy('position','ASC') ;
    }

    public function childByRelation($relation)
    {
        return $this->hasMany(Menu::class,'parent_id','menuable_id')
            ->where('menuable_type',$relation)
            ->orderBy('position','ASC') ;
    }

}
