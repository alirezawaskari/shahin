<?php

namespace App\Http\Controllers;

use App\Models\About;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AboutController extends Controller
{
    public function index()
    {
        $about = Cache::remember('about', Carbon::now()->addMinute(60), function () {
            return About::first();
        });
        return view('site.about' , compact('about'));
    }
}
