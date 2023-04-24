<?php

namespace App\Http\Controllers;

class ErrorController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function notfound()
    {
        return view('front.notfound');
    }
}
