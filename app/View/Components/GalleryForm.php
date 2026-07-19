<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
class GalleryForm extends Component
{
    public $galleries;
    public $ghorfeTemplet;

    public function __construct($galleries = [], $ghorfeTemplet = null)
    {
        $this->galleries = collect($galleries);
        $this->ghorfeTemplet = $ghorfeTemplet;
    }

    public function render()
    {
        return view('components.gallery-form');
    }
}
