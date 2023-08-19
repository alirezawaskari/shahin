<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Check;
use Illuminate\Http\Request;

class TreasuryController extends Controller
{
    public function index()
    {
          $checks=Check::all();
//            dd(date('d-m-Y', strtotime($checks->date)));
            return view('Admin.treasury.index',compact('checks'));
        }
}
