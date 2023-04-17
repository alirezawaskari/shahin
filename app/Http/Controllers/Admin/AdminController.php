<?php

namespace App\Http\Controllers\Admin;

use App\Classes\External\IranSms;
use App\Models\City;
use App\Models\Contact;
use App\Models\Factor;
use App\Models\PurchaseInvoice;
use App\Models\Resume;
use App\Http\Controllers\Controller;
use App\Http\Resources\FactorCollection;
use App\Models\Service;
use App\Models\FactorItem;
use App\Models\ProductStoreRoom;
use App\Models\User;
use App\Models\Representation;
use App\Models\TotalFactorItem;
use App\Models\WorkSample;
use Carbon\Carbon;
use App\Helpers\Helper;
use DB;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function uploadImage( Request $request ) {
        $file = $request->file;
        $path = $request->path;
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        $filepath = "/upload/{$year}/{$month}/{$path}/";

        $rand = rand( 1, 999 );
        $filename = "{$rand}_" . $file->getClientOriginalName();
        $file->move( public_path( $filepath ), $filename );

        return $filepath . $filename;
    }

    //upload img to temp folder
    public function uploadPhotosTemporarily(Request $request)
    {
        if (isset($request->file)) {
            if (isset($request->image_name) && $request->image_name != '') {
                if (\File::exists(public_path($request->image_name))) {
                    \File::delete(public_path($request->image_name));
                }
            }

            $path = '/upload/temp';

            $year = Carbon::now()->year;
            $month = Carbon::now()->month;
            $user_id=\Auth::user()->id;
            $file = $request->file;
            $random=random_int('10000','99999');
            $filepath = "{$path}/{$year}/{$month}/{$user_id}/";
            $filename = "{$random}_" . $file->getClientOriginalName();
            $file->move(public_path($filepath), $filename);
            $image_name =  $filepath . $filename;

            return ['image_name' => $image_name, 'status' => true];
        }
        return ['status' => false];
    }


    public function dashboard(Request $request)
    {
        $percent_employee = auth()->user()->employee->percent/100;
        $subset_employees = auth()->user()->employee->parents;
        $totalMonthFactorCount = 0;
        $totalMonthFactorPaidCount = 0;

        $type = $request->type;
        $representation = auth()->user()->admin_representation;

        /*------------ statistics card --------------*/
        if ($representation) {
            $link = "lalecard.ir/".$representation->slug.'/link/'.auth()->user()->id;
            $totalUser = User::where('representation_id', $representation->id)->count();
            $totalDayUser = User::where('created_at', 'like', date("Y-m-d").'%')->count();
            $totalMonthUser = User::where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalDayFactor = Factor::where('created_at', '>', Carbon::now()->subDay())->where('status', '1')->count();
            $totalMonthFactor = Factor::where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();
            $totalDayFactorPrice = Factor::where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->where('type' ,'!=', 'organ')->sum('price');
            $totalDayFactorPriceOrgan = Factor::where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->where('type','organ')->sum('price');
            $totalYesterdayFactorPrice = Factor::whereBetween('created_at',[Helper::vertaTocarbon(verta()->subDays(1)->startDay()->addHours(1)), Helper::vertaTocarbon(verta()->subDays(1)->endDay()->addHours(1))])->where('status', '1')->sum('price');
            $totalWeekFactorPrice = Factor::where('created_at', '>', Helper::vertaTocarbon(verta()->startWeek()))->where('status', '1')->sum('price');
            $totalMonthFactorPrice = Factor::where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->sum('price');
            $totalBeforeMonthFactorPrice = Factor::whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth(1)->startMonth()), Helper::vertaTocarbon(verta()->subMonth(1)->endMonth()->addDays(1))])->whereStatus(true)->sum('price');
            $totalSixMonthFactorPrice = Factor::where('created_at', '>', Helper::vertaTocarbon(verta()->subMonths(6)))->where('status', '1')->sum('price');
            $totalYearFactorPrice =  Factor::where('created_at', '>', Helper::vertaTocarbon(verta()->startYear()))->where('status', '1')->sum('price');
            $totalAllFactorPrice = Factor::where('status', true)->sum('price');
        }
        else
        {
            $link = config('app.url')."/mobile/link/".auth()->user()->id;
            $totalUser = User::count();
            $totalDayUser = User::where('created_at', 'like', date("Y-m-d").'%')->count();
            $totalMonthUser = User::where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalDayFactor = Factor::where('created_at', '>', Carbon::now()->subDay())->where('status', '1')->count();
            $totalMonthFactor = Factor::where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();
            $totalDayFactorPrice = Factor::where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalDayFactorPriceOrgan = Factor::where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->where('type' , 'credit')->sum('price');
            $totalYesterdayFactorPrice = Factor::whereBetween('created_at',[Helper::vertaTocarbon(verta()->subDays(1)->startDay()->addHours(1)), Helper::vertaTocarbon(verta()->subDays(1)->endDay()->addHours(1))])->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalWeekFactorPrice = Factor::where('created_at', '>', Helper::vertaTocarbon(verta()->startWeek()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalMonthFactorPrice = Factor::where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalBeforeMonthFactorPrice = Factor::whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth(1)->startMonth()), Helper::vertaTocarbon(verta()->subMonth(1)->endMonth()->addDays(1))])->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalSixMonthFactorPrice = Factor::where('created_at', '>', Helper::vertaTocarbon(verta()->subMonths(6)))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalYearFactorPrice =  Factor::where('created_at', '>', Helper::vertaTocarbon(verta()->startYear()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalAllFactorPrice = Factor::where('status', true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
        }
        if(auth()->user()->hasPermission('view-sales-revenue'))
        {
            $totalMonthFactorCount = Factor::where('callcenter_id' , auth()->user()->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalMonthFactorPaidCount = Factor::where('callcenter_id' , auth()->user()->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();

            $totalUser = User::where('callcenter_id' , auth()->user()->id)->count();
            $totalDayUser = User::where('callcenter_id' , auth()->user()->id)->where('created_at', '>', Carbon::now()->subDay())->count();
            $totalMonthUser = User::where('callcenter_id' , auth()->user()->id)->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalDayFactor = Factor::where('callcenter_id' , auth()->user()->id)->where('created_at', '>', Carbon::now()->subDay())->where('status', '1')->count();
            $totalMonthFactor = Factor::where('callcenter_id' , auth()->user()->id)->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();
            $totalDayFactorPrice = Factor::where('callcenter_id' , auth()->user()->id)->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price') * $percent_employee;
            $totalDayFactorPriceOrgan = Factor::where('callcenter_id' , auth()->user()->id)->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->where('type' , 'credit')->sum('price') * $percent_employee;
            $totalYesterdayFactorPrice = Factor::where('callcenter_id' , auth()->user()->id)->whereBetween('created_at',[Helper::vertaTocarbon(verta()->subDays(1)->startDay()->addHours(1)), Helper::vertaTocarbon(verta()->subDays(1)->endDay()->addHours(1))])->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price') * $percent_employee;
            $totalWeekFactorPrice = Factor::where('callcenter_id' , auth()->user()->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startWeek()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price') * $percent_employee;
            $totalMonthFactorPrice = Factor::where('callcenter_id' , auth()->user()->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price') * $percent_employee;
            $totalBeforeMonthFactorPrice = Factor::where('callcenter_id' , auth()->user()->id)->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth(1)->startMonth()), Helper::vertaTocarbon(verta()->subMonth(1)->endMonth()->addDays(1))])->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price') * $percent_employee;
            $totalSixMonthFactorPrice = Factor::where('callcenter_id' , auth()->user()->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->subMonths(6)))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price') * $percent_employee;
            $totalYearFactorPrice =  Factor::where('callcenter_id' , auth()->user()->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startYear()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price') * $percent_employee;
            $totalAllFactorPrice = Factor::where('callcenter_id' , auth()->user()->id)->where('status', true)->whereIn('type' , Factor::getCashPaymentType())->sum('price') * $percent_employee;
        }
        if(auth()->user()->center_id)
        {
            $totalUser = User::where('center_id' , auth()->user()->id)->count();
            $totalDayUser = User::where('center_id' , auth()->user()->id)->where('created_at', '>', Carbon::now()->subDay())->count();
            $totalMonthUser = User::where('center_id' , auth()->user()->id)->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalDayFactor = Factor::where('center_id' , auth()->user()->center_id)->where('created_at', '>', Carbon::now()->subDay())->where('status', '1')->count();
            $totalMonthFactor = Factor::where('center_id' , auth()->user()->center_id)->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();
            $totalDayFactorPrice = Factor::where('center_id' , auth()->user()->center_id)->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->with('factor_items')->sum('price');
            $totalDayFactorPriceOrgan = Factor::where('center_id' , auth()->user()->center_id)->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->where('type' , 'credit')->sum('price');
            $totalYesterdayFactorPrice = Factor::where('center_id' , auth()->user()->center_id)->whereBetween('created_at',[Helper::vertaTocarbon(verta()->subDays(1)->startDay()->addHours(1)), Helper::vertaTocarbon(verta()->subDays(1)->endDay()->addHours(1))])->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalWeekFactorPrice = Factor::where('center_id' , auth()->user()->center_id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startWeek()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalMonthFactorPrice = Factor::where('center_id' , auth()->user()->center_id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalBeforeMonthFactorPrice = Factor::where('center_id' , auth()->user()->center_id)->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth(1)->startMonth()), Helper::vertaTocarbon(verta()->subMonth(1)->endMonth()->addDays(1))])->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalSixMonthFactorPrice = Factor::where('center_id' , auth()->user()->center_id)->where('created_at', '>', Helper::vertaTocarbon(verta()->subMonths(6)))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalYearFactorPrice =  Factor::where('center_id' , auth()->user()->center_id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startYear()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalAllFactorPrice = Factor::where('center_id' , auth()->user()->center_id)->where('status', true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
        }
        if ($representation) {
//            -------------------------------------------------------------------
            $factor_month['success']=array();
            for ($i = 0; $i <= 6; $i++) {
                $factor_month['success'][$i]= Factor::where('representation_id', $representation->id)->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth($i)->startMonth()), Helper::vertaTocarbon(verta()->subMonth($i)->endMonth())])->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            }
            $factor_month['success'] = array_reverse($factor_month['success']);
            //            -------------------------------------------------------------------
            $factor_month['unsuccess']= array();
            for ($i = 0; $i <= 6; $i++) {
                $factor_month['unsuccess'][$i]= Factor::where('representation_id', $representation->id)->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth($i)->startMonth()), Helper::vertaTocarbon(verta()->subMonth($i)->endMonth())])->where('status' , '!=' , 1)->sum('price');
            }
            $factor_month['unsuccess'] = array_reverse(  $factor_month['unsuccess']);
            //            -------------------------------------------------------------------
            for ($i = 0; $i <= 6; $i++) {
                $factor_week['success'][$i]=  Factor::where('representation_id', $representation->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->whereStatus(true)->sum('price');
            }
            $factor_week['success'] = array_reverse($factor_week['success']);
            //            -------------------------------------------------------------------
            for ($i = 0; $i <= 6; $i++) {
                $factor_week['unsuccess'][$i]=  Factor::where('representation_id', $representation->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->where('status' , '!=' , true)->sum('price');
            }
            $factor_week['unsuccess'] = array_reverse($factor_week['unsuccess']);
            //            -------------------------------------------------------------------
            $user_week=array();
            for ($i = 0; $i <= 6; $i++) {
                $user_week[$i]= User::where('representation_id', $representation->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->count();
            }
            $user_week=array_reverse($user_week);
            //            -------------------------------------------------------------------
            $user_month=array();
            for ($i = 0; $i <= 6; $i++) {
                $user_month[$i]= User::where('representation_id', $representation->id)->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth($i)->startMonth()), Helper::vertaTocarbon(verta()->subMonth($i)->endMonth())])->count();
            }
            $user_month=array_reverse($user_month);
        } else {
            /*----------- user and payment monthly chart -----------*/
            $factor_month['success']=array();
            for ($i = 0; $i <= 6; $i++) {
                $factor_month['success'][$i]= Factor::whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth($i)->startMonth()), Helper::vertaTocarbon(verta()->subMonth($i)->endMonth())])->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            }
            $factor_month['success'] = array_reverse( $factor_month['success']);
            //            -------------------------------------------------------------------
            $factor_month['unsuccess']=array();
            for ($i = 0; $i <= 6; $i++) {
                $factor_month['unsuccess'][$i]= Factor::whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth($i)->startMonth()), Helper::vertaTocarbon(verta()->subMonth($i)->endMonth())])->where('status','!=',1)->sum('price');
            }
            $factor_month['unsuccess'] = array_reverse( $factor_month['unsuccess']);
            //            -------------------------------------------------------------------
            $factor_week['success']=array();
            for ($i = 0; $i <= 6; $i++) {
                $factor_week['success'][$i]=  Factor::whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            }
            $factor_week['success'] = array_reverse($factor_week['success']);
            //            -------------------------------------------------------------------
            $factor_week['unsuccess']=array();
            for ($i = 0; $i <= 6; $i++) {
                $factor_week['unsuccess'][$i]=    Factor::whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->where('status' , '!=' , true)->sum('price');
            }
            $factor_week['unsuccess'] = array_reverse(  $factor_week['unsuccess']);
            //            -------------------------------------------------------------------
            $user_week=array();
            for ($i = 0; $i <= 6; $i++) {
                $user_week[$i]= User::whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->count();
            }
            $user_week = array_reverse($user_week);
            //            -------------------------------------------------------------------
            $user_month=array();
            for ($i = 0; $i <= 6; $i++) {
                $user_month[$i]=  User::whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth($i)->startMonth()), Helper::vertaTocarbon(verta()->subMonth($i)->endMonth())])->count();
            }
            $user_month = array_reverse($user_month);
            //            -------------------------------------------------------------------

        }
        if(auth()->user()->hasPermission('callcenter|influencer-marketing')){
            $factor_month['success'] =array();
            for ($i = 0; $i <= 6; $i++) {
                $factor_month['success'][$i]=  Factor::where('callcenter_id' , auth()->user()->id)->orWhereHas('task',function ($query){
                    $query->whereHas('callcenter',function ($query){
                        $query->whereHas('employee',function ($query){
                            $query->where('parent_id',auth()->user()->employee->id);
                        });
                    });
                })->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth($i)->startMonth()), Helper::vertaTocarbon(verta()->subMonth($i)->endMonth())])->whereStatus(true)->sum('price');
            }

            $factor_month['success'] = array_reverse( $factor_month['success']);
            //            -------------------------------------------------------------------
            $factor_month['unsuccess'] =array();
            for ($i = 0; $i <= 6; $i++) {
                $factor_month['unsuccess'][$i]=  Factor::where('callcenter_id' , auth()->user()->id)->orWhereHas('task',function ($query){
                    $query->whereHas('callcenter',function ($query){
                        $query->whereHas('employee',function ($query){
                            $query->where('parent_id',auth()->user()->employee->id);
                        });
                    });
                })->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth($i)->startMonth()), Helper::vertaTocarbon(verta()->subMonth($i)->endMonth())])->where('status','!=',1)->sum('price');
            }
            $factor_month['unsuccess'] = array_reverse($factor_month['unsuccess']);
            //            -------------------------------------------------------------------
            $factor_week['success']=array();
            for ($i = 0; $i <= 6; $i++) {
                $factor_week['success'][$i]= Factor::where('callcenter_id' , auth()->user()->id)->orWhereHas('task',function ($query){
                    $query->whereHas('callcenter',function ($query){
                        $query->whereHas('employee',function ($query){
                            $query->where('parent_id',auth()->user()->employee->id);
                        });
                    });
                })->whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->whereStatus(true)->sum('price');
            }
            $factor_week['success'] = array_reverse($factor_week['success']);
            //            -------------------------------------------------------------------
            $factor_week['unsuccess']=array();
            for ($i = 0; $i <= 6; $i++) {
                $factor_week['unsuccess'][$i]= Factor::where('callcenter_id' , auth()->user()->id)->orWhereHas('task',function ($query){
                    $query->whereHas('callcenter',function ($query){
                        $query->whereHas('employee',function ($query){
                            $query->where('parent_id',auth()->user()->employee->id);
                        });
                    });
                })->whereDate('created_at', Helper::vertaTocarbon(verta()->subDays(1)))->where('status' , '!=' , true)->sum('price');
            }
            $factor_week['unsuccess'] = array_reverse($factor_week['unsuccess']);
            //            -------------------------------------------------------------------
            $user_week=array();
            for ($i = 0; $i <= 6; $i++) {
                $user_week[$i]= User::where('callcenter_id' , auth()->user()->id)->orWhereHas('tasks',function ($query){
                    $query->whereHas('callcenter',function ($query){
                        $query->whereHas('employee',function ($query){
                            $query->where('parent_id',auth()->user()->employee->id);
                        });
                    });
                })->whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->count();
            }
            $user_week = array_reverse($user_week);
        }
        $label_month = ['6 ماه قبل', '5 ماه قبل', '4 ماه قبل', '3 ماه قبل', '2 ماه قبل', '1 ماه قبل', 'ماه جاری'];
        $label_week = ['6 روز قبل', '5 روز قبل', '4 روز قبل', '3 روز قبل', '2 روز قبل', '1 روز قبل', 'امروز'];
        $services = Service::orderBy('created_at', 'desc')->get();
        $start_date = "";
        $service_name = [];
        $service_count = [];
        $final_date = date("Y-m-d");
        if (auth()->user()->admin_organization_id)
        {
            $services = auth()->user()->admin_organization->services;
            foreach ($services as $service) {
                if (DB::table('factors')->join('factor_items', 'factors.id', '=', 'factor_items.factor_id')->where('factor_items.factoritemable_id', '=', $service->id)->where('factors.status', '=', 1)->count() >= 50) {
                    $service_count[] = DB::table('factors')->where('organization_id', auth()->user()->admin_organization_id)->whereBetween('factors.created_at', [$start_date, $final_date])->join('factor_items', 'factors.id', '=', 'factor_items.factor_id')->where('factor_items.factoritemable_id', '=', $service->id)->where('factors.status', '=', 1)->count();
                    $service_name[] = $service->title;
                    $service_count_total[] = DB::table('factors')->where('organization_id', auth()->user()->admin_organization_id)->whereBetween('factors.created_at', [$start_date, $final_date])->join('factor_items', 'factors.id', '=', 'factor_items.factor_id')->where('factor_items.factoritemable_id', '=', $service->id)->count();
                }
            }
        }

        if(auth()->user()->hasPermission('callcenter|influencer-marketing')){
            foreach ($services as $service) {
                if (DB::table('factors')->join('factor_items', 'factors.id', '=', 'factor_items.factor_id')->where('factor_items.factoritemable_id', '=', $service->id)->where('factors.status', '=', 1)->count() >= 50) {
                    $service_count[] = DB::table('factors')->where('callcenter_id', auth()->user()->id)->whereBetween('factors.created_at', [$start_date, $final_date])->join('factor_items', 'factors.id', '=', 'factor_items.factor_id')->where('factor_items.factoritemable_id', '=', $service->id)->where('factors.status', '=', 1)->count();
                    $service_name[] = $service->title;
                    $service_count_total[] = DB::table('factors')->where('callcenter_id', auth()->user()->id)->whereBetween('factors.created_at', [$start_date, $final_date])->join('factor_items', 'factors.id', '=', 'factor_items.factor_id')->where('factor_items.factoritemable_id', '=', $service->id)->count();
                }
            }
        }
        elseif($representation)
        {
            foreach ($services as $service) {
                if (DB::table('factors')->join('factor_items', 'factors.id', '=', 'factor_items.factor_id')->where('factor_items.factoritemable_id', '=', $service->id)->where('factors.status', '=', 1)->count() >= 50) {
                    $service_count[] = DB::table('factors')->where('representation_id', $representation->id)->whereBetween('factors.created_at', [$start_date, $final_date])->join('factor_items', 'factors.id', '=', 'factor_items.factor_id')->where('factor_items.factoritemable_id', '=', $service->id)->where('factors.status', '=', 1)->count();
                    $service_name[] = $service->title;
                    $service_count_total[] = DB::table('factors')->where('representation_id', $representation->id)->whereBetween('factors.created_at', [$start_date, $final_date])->join('factor_items', 'factors.id', '=', 'factor_items.factor_id')->where('factor_items.factoritemable_id', '=', $service->id)->count();
                }
            }
        }
        else
        {
            foreach ($services as $service)
            {
                if (DB::table('factors')->join('factor_items', 'factors.id', '=', 'factor_items.factor_id')->where('factor_items.factoritemable_id', '=', $service->id)->where('factors.status', '=', 1)->count() >= 50) {
                    $service_count[] = DB::table('factors')->whereBetween('factors.created_at', [$start_date, $final_date])->join('factor_items', 'factors.id', '=', 'factor_items.factor_id')->where('factor_items.factoritemable_id', '=', $service->id)->where('factors.status', '=', 1)->count();
                    $service_name[] = $service->title;
                }
            }
        }

        $representations = Representation::all();
        $admin_users = User::where('level', 'admin')->withCount('tasks')->get();
        $callcenters = [];
        foreach ($admin_users as $u) {
            if ($u->hasRole('callcenter')) {
                $callcenters[] = $u;
            }
        }
        $callcenter_users = [];
        foreach ($admin_users as $u) {
            if ($u->hasRole('presentore')) {
                $callcenter_users[] = $u;
            }
        }

        return view('Admin.index', compact('totalMonthFactorCount', 'totalMonthFactorPaidCount','link','callcenter_users','callcenters' , 'services' , 'representations','type','service_name','service_count','label_month', 'label_week', 'factor_week', 'factor_month', 'user_month', 'user_week','totalUser', 'totalDayUser', 'totalDayFactor', 'totalDayFactorPrice','totalDayFactorPriceOrgan','totalYesterdayFactorPrice', 'totalWeekFactorPrice' ,'totalMonthFactorPrice','totalSixMonthFactorPrice', 'totalMonthFactor','totalBeforeMonthFactorPrice', 'totalYearFactorPrice', 'totalAllFactorPrice' ,'totalMonthUser'));
    }

    public function setLink($service)
    {
        $representation = auth()->user()->admin_representation;
        if ($representation)
        {
            $link = "lalecard.ir/".$representation->slug."/".($service == 'all' ? 'link' : $service)."/".auth()->user()->id;
        }
        else
        {
            $link = "lalecard.ir/".($service == 'all' ? 'mobile/link' : 'services/'.$service)."/".auth()->user()->id;
        }

        $message = 'لینک با موفقیت بروزرسانی شد.';

        return response(['message' => $message, 'link' => $link]);
    }

    public function dailyDesktop()
    {
        $item = [];
        $item['count_tickets'] = auth()->user()->tickets->where('status' , false)->count();
        $item['count_factoritem_confirm'] = 0;
        $item['count_resume'] = Resume::where('status' , 0)->count();

        //$item['count_product_storeRoom_expiring'] = count(ProductStoreRoom::productStoreRoomExpiring());
        $item['count_request'] = \App\Models\Request::where('status' , 2)->count();

        if(auth()->user()->center_id && auth()->user()->isAbleTo('file-detail'))
        {
            $item['count_factoritem_confirm'] = FactorItem::where('status_factor', 10)->where('center_id' , auth()->user()->center_id)->count();
        }

        $representation = auth()->user()->admin_representation;

        $item['count_contact'] = Contact::where('card_id' , auth()->user()->roles->first()->card_id)->whereStatus(false)->count();

        if(auth()->user()->hasPermission('resume-desktop'))
        {
            $item['count_resume'] = Resume::where('card_id' , auth()->user()->roles->first()->card_id)->where('status' , '!=' , 4)->count();
        }

        /*------------ statistics card --------------*/
        if ($representation) {
            $item['count_factor_supplier'] = 0;
            $item['count_factor_mali'] = 0;
            $item['count_factor_superadmin'] = 0;
            $item['count_factor_warehouseKeeper'] = 0;
            $item['count_factoritem_mali'] = 0;
            $item['count_factoritem_consulting_contract_affairs'] = 0;
            $item['count_factoritem_mali_consulting_contract_affairs'] = 0;
            $item['count_factoritem_mali_legal'] = 0;
            $item['count_factoritem_mali_mali'] = 0;
            $item['count_factoritem_mali_superadmin'] = 0;
            $item['count_factoritem_superadmin'] = 0;
            $item['count_factor_mali_representation'] = PurchaseInvoice::where('status', 1)->where('representation_id', auth()->user()->admin_representation->id)->count();
            $item['count_factor_superadmin_representation'] = PurchaseInvoice::where('status', 2)->where('representation_id', auth()->user()->admin_representation->id)->count();
            $item['count_factoritem_mali_representation'] = TotalFactorItem::where('total_factor_item_status_id', 11)->whereHas('center', function($q){
                $q->where('representation_id', auth()->user()->admin_representation->id);
            })->count();
            $item['count_factoritem_superadmin_representation'] = TotalFactorItem::where('total_factor_item_status_id', 12)->whereHas('center', function($q){
                $q->where('representation_id', auth()->user()->admin_representation->id);
            })->count();
            $item['count_factoritem_filing'] = FactorItem::where('status_factor', 9)->whereHas('center', function($q){
                $q->where('representation_id', auth()->user()->admin_representation->id);
            })->count();
        } else {
            $item['count_factor_mali_representation'] = 0;
            $item['count_factor_superadmin_representation'] = 0;
            $item['count_factoritem_mali_representation'] = 0;
            $item['count_factoritem_superadmin_representation'] = 0;
            $item['count_factor_supplier'] = isset(auth()->user()->supplier) ? PurchaseInvoice::where('supplier_id' , auth()->user()->supplier->id)->where('status' , 0)->count() : 0;
            $item['count_factor_mali'] = PurchaseInvoice::where('status', 1)->count();
            $item['count_factor_superadmin'] = PurchaseInvoice::where('status', 2)->count();
            $item['count_factor_warehouseKeeper'] = PurchaseInvoice::where('status', 6)->count();
            $item['count_factoritem_mali'] = TotalFactorItem::where('total_factor_item_status_id', 5)->count();
            $item['count_factoritem_consulting_contract_affairs'] = TotalFactorItem::where('total_factor_item_status_id', 13)->count();
            $item['count_factoritem_mali_legal'] = TotalFactorItem::where('status_mali', 10)->count();
            $item['count_factoritem_mali_consulting_contract_affairs'] = TotalFactorItem::where('status_mali', 4)->count();
            $item['count_factoritem_mali_mali'] = TotalFactorItem::where('status_mali', 7)->orWhere('status_mali', 9)->count();
            $item['count_factoritem_mali_superadmin'] = TotalFactorItem::where('status_mali', 8)->count();
            $item['count_factoritem_superadmin'] = TotalFactorItem::where('total_factor_item_status_id', 8)->count();
            $item['count_factoritem_filing'] = TotalFactorItem::where('total_factor_item_status_id', 9)->count();
        }
        $expire_dates=ContextController::fetchExpireDates();

        $iran_sms_credit=IranSms::fetchCredit()->credit;
        return view('Admin.dailyDesktop' , compact('item','expire_dates','iran_sms_credit'));
    }

    public function fetchUser(Request $request)
    {
        $users = User::where('family', 'LIKE', '%' . $request->value . '%')->orWhere('phone', $request->value)->get();
        return response(['operator' => true, 'data' => $users]);
    }

    public function cities(Request $request)
    {
        return response([
            'cities' => City::where('province_id', $request->id)->get(),
        ]);
    }

    public function user(Request $request)
    {
        $user = User::where('phone', $request->phone)->get();
        return response([
            'user' => $user->map(function ($item)
            {
                $temp = [];
                $temp['label'] = $item->name . ' ' . $item->family . ' - ' . $item->phone;
                $temp['id'] = $item->id;
                $factors = $item->factors;
                $histories = [];
                $calcenters = [];

                if(auth()->user()->isAbleTo('history-callcenter'))
                {
                    foreach($item->callcenters as $task)
                    {
                        foreach($task->histories as $history)
                        {
                            $item['user'] = $history->user['name'] . ' ' . $history->user['family'];
                            $item['callcenter'] = $history->callcenter['name'] . ' ' . $history->callcenter['family'];
                            $item['description'] = $history->description;
                            $item['date'] = verta($history->created_at)->format('%d %B ، %Y - H:i');
                            $item['created_at'] = $history->created_at;
                            $histories[] = $item;
                        }
                    }
                }

                if(auth()->user()->isAbleTo('history-factor'))
                {
                    foreach($item->factors as $factor)
                    {
                        foreach($factor->comments as $comment)
                        {
                            $item['user'] = $comment->user['name'] . ' ' . $comment->user['family'];
                            $item['callcenter'] = $comment->user['name'] . ' ' . $comment->user['family'];
                            $item['description'] = $comment->comment;
                            $item['date'] = verta($comment->created_at)->format('%d %B ، %Y - H:i');
                            $item['created_at'] = $comment->created_at;
                            $histories[] = $item;
                        }
                    }
                }
                $temp['histories'] = $histories;
                return $temp;
            })
        ]);
    }

    public function summernote_image(Request $request)
    {
        return $this->upload($request->image);
    }

    public function uploadFile( Request $request ) {
        return Helper::uploadImage( $request->file, $request->path );
    }

    public function removeImgWorkSample(Request $request)
    {
        if (isset($request->work_sample_id)) {
            $work = WorkSample::find($request->work_sample_id);
            if ($work) {
                if (\File::exists(public_path($work->image))) {
                    \File::delete(public_path($work->image));
                }
                $work->delete();
            }
            return ['status' => 'true'];
        }
        return ['status' => 'false'];
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function showLogs()
    {
        if (! auth()->user()->hasRole( 'superadmin' ) )
            abort(403);
        $logFile = storage_path('logs/laravel.log');

        $logContents = file_get_contents($logFile);
        $logLines = explode("\n", $logContents);
        $logs=array();
        foreach ($logLines as $lite_item){
            $logs[]=Helper::replaceDateTimeWithPersian($lite_item);
        }
        return view('logs', ['errorLines' => $logs]);
    }



}
