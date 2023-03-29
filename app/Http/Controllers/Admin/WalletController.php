<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cardservice;
use App\Models\Constructionservice;
use App\Models\Dentistryservice;
use App\Events\smsService;
use App\Models\Factor;
use App\Models\Product;
use App\Models\Session;
use App\Models\User;
use App\Models\WalletTransaction;
use DB;
use App\Models\Foodservice;
use App\Models\Hotelservice;
use App\Http\Controllers\Controller;
use App\Http\Resources\FactorCollection;
use App\Models\Insuranceservice;
use App\Models\Internetservice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public static function vertaTocarbon($date)
    {
        return Carbon::instance($date->DateTime())->toDateString();
    }
}
