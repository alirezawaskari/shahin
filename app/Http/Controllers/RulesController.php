<?php

namespace App\Http\Controllers;

use App\Models\Context;
use Illuminate\Http\Request;

class RulesController extends Controller
{
    public function index()
    {
        $site_rules = array('body' => Context::whereIn('key', Context::SITE_RULES_KEYS)->first()->value);
        return view('site.rules', compact('site_rules'));
    }
}
