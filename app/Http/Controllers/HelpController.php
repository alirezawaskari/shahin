<?php

namespace App\Http\Controllers;

use App\Models\Help;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HelpController extends Controller
{
    public function index()
    {
        $help = Cache::remember('help', Carbon::now()->addMinute(60), function () {
            return Help::first();
        });

        return view('site.help' , compact('help'));
    }
}
