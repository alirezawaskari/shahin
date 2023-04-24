<?php

namespace App\Http\Controllers;

use App\Models\Help;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class HelpController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $help = Cache::remember('help', Carbon::now()->addMinute(60), function () {
            return Help::first();
        });

        return view('site.help' , compact('help'));
    }
}
