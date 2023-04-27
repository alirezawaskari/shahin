<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cardservice;
use App\Models\Constructionservice;
use App\Models\Dentistryservice;
use App\Models\Foodservice;
use App\Models\Hotelservice;
use App\Models\Insuranceservice;
use App\Models\Internetservice;
use Carbon\Carbon;
use DB;

class WalletController extends Controller
{
    public static function vertaTocarbon($date)
    {
        return Carbon::instance($date->DateTime())->toDateString();
    }
}
