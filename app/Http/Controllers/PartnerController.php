<?php

namespace App\Http\Controllers;

class PartnerController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function partner()
    {
        return view('site.contract');
    }
}
