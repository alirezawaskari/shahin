<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
class GalleryForm extends Component
{
    public $galleries;

    public function __construct($galleries = [])
    {
        $this->galleries = collect($galleries);
    }

    public function render()
    {
        return view('components.gallery-form');
    }
}
