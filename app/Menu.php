<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $guarded = [];

    public function getChild()
    {
        return $this->hasMany(Menu::class, 'parent_id', 'id');
    }

    public function getParent()
    {
        return $this->hasMany(Menu::class, 'id', 'parent_id');
    }
    public function ghorfeOnlineList()
    {
        return $this->hasMany(GhorfeOnlineList::class, 'ghorfe_online_list_id', 'id');
    }
}
