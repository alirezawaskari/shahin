<?php

namespace App;

use App\Models\GhorfeOnlineList;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $guarded = [];

    public function getChild()
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }

    public function getParent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function ghorfeOnlineLists()
    {
        return $this->hasMany(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function scopeMainMenu($query)
    {
        return $query->whereNull('parent_id');
    }
}
