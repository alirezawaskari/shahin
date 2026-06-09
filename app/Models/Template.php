<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Template extends Model
{
    protected $guarded = [];

    /**
     * Get the phone associated with the user.
     */
    public function menus()
    {
        return $this->belongsToMany(
            \App\Menu::class,
            'menu_template',
            'template_id',
            'menu_id'
        );
    }
}
