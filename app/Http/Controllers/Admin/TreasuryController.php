<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Check;
use Illuminate\Http\Request;

class TreasuryController extends Controller
{
    public function calendar()
    {
        if (!auth()->user()->isAbleTo('treasury-calendar')) {
            return abort(403);
        }
            $checks=Check::all();
            return view('Admin.treasury.calendar',compact('checks'));
        }
}
