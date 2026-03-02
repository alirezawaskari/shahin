<?php

namespace App\Http\Controllers;

use App\Models\About;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;


class AboutController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        if(session()->has('representation'))
        {
            $about = Cache::remember('about', Carbon::now()->addMinute(60), function () {
            return About::first();
            });
            return view('site.representation.about' , compact('about'));
        }
        $about = Cache::remember('about', Carbon::now()->addMinute(60), function () {
            return About::first();
        });
        return view('site.about' , compact('about'));
    }
}
