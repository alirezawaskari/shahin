<?php

namespace App\Http\Controllers\Admin;

use App\Classes\External\IranSms;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Contact;
use App\Models\Factor;
use App\Models\FactorItem;
use App\Models\FinancialPlansType;
use App\Models\PurchaseInvoice;
use App\Models\Representation;
use App\Models\Request as RequestModal;
use App\Models\Resume;
use App\Models\Service;
use App\Models\TotalFactorItem;
use App\Models\User;
use App\Models\WorkSample;
use App\Services\V1\Admin\DashboardService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;


class AdminController extends Controller
{
    protected DashboardService $dashboardService;

    public  function __construct(DashboardService $dashboardService){
        $this->dashboardService = $dashboardService;
    }
    /**
     * @param Request $request
     * @return string
     */
    public function uploadImage(Request $request ) {
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

    /**
     * @param Request $request
     * @return array|false[]
     * @throws \Exception
     */
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
            // image max size
            //$max_file_size = 1 * 1024 * 1024;
            $max_file_size =700 * 1024;
            // Image quality
            $quality = 80;
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $file_extension = $file->getClientOriginalExtension();
            if(in_array($file_extension, $allowed_extensions) && $file->getSize() > $max_file_size ) {
                $image = Image::make($file->getRealPath());
                $image->resize(null, 1500, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $image->save(public_path($filepath) . $filename, $quality);
            }
            else{
                $file->move(public_path($filepath), $filename);
            }

            $image_name =  $filepath . $filename;

            return ['image_name' => $image_name, 'status' => true];
        }
        return ['status' => false];
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
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
            $totalUser = User::filter()->where('representation_id', $representation->id)->count();
            $totalDayUser = User::filter()->wfilter()->here('representation_id', $representation->id)->where('created_at', 'like', date("Y-m-d").'%')->count();
            $totalMonthUser = User::filter()->where('representation_id', $representation->id)->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalDayFactor = Factor::filter()->where('representation_id', $representation->id)->where('created_at', '>', Carbon::now()->subDay())->where('status', '1')->count();
            $totalMonthFactor = Factor::filter()->where('representation_id', $representation->id)->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();
            $totalDayFactorPrice = Factor::filter()->where('representation_id', $representation->id)->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->where('type' ,'!=', 'organ')->sum('price');
            $totalDayFactorPriceOrgan = Factor::filter()->where('representation_id', $representation->id)->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->where('type','organ')->sum('price');
            $totalYesterdayFactorPrice = Factor::filter()->where('representation_id', $representation->id)->whereBetween('created_at',[Helper::vertaTocarbon(verta()->subDays(1)->startDay()->addHours(1)), Helper::vertaTocarbon(verta()->subDays(1)->endDay()->addHours(1))])->where('status', '1')->sum('price');
            $totalWeekFactorPrice = Factor::filter()->filter()->where('representation_id', $representation->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startWeek()))->where('status', '1')->sum('price');
            $totalMonthFactorPrice = Factor::filter()->where('representation_id', $representation->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->sum('price');
            $totalBeforeMonthFactorPrice = Factor::filter()->where('representation_id', $representation->id)->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth(1)->startMonth()), Helper::vertaTocarbon(verta()->subMonth(1)->endMonth()->addDays(1))])->whereStatus(true)->sum('price');
            $totalSixMonthFactorPrice = Factor::filter()->where('representation_id', $representation->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->subMonths(6)))->where('status', '1')->sum('price');
            $totalYearFactorPrice =  Factor::filter()->where('representation_id', $representation->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startYear()))->where('status', '1')->sum('price');
            $totalAllFactorPrice = Factor::filter()->where('representation_id', $representation->id)->where('status', true)->sum('price');
        }
        else
        {
            $link = config('app.url')."/mobile/link/".auth()->user()->id;
            $totalUser = User::filter()->count();
            $totalDayUser = User::filter()->where('created_at', 'like', date("Y-m-d").'%')->count();
            $totalMonthUser = User::filter()->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalDayFactor = Factor::filter()->where('created_at', '>', Carbon::now()->subDay())->where('status', '1')->count();
            $totalMonthFactor = Factor::filter()->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();
            $totalDayFactorPrice = Factor::filter()->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalDayFactorPriceOrgan = Factor::filter()->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->where('type' , 'credit')->sum('price');
            $totalYesterdayFactorPrice = Factor::filter()->whereBetween('created_at',[Helper::vertaTocarbon(verta()->subDays(1)->startDay()->addHours(1)), Helper::vertaTocarbon(verta()->subDays(1)->endDay()->addHours(1))])->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalWeekFactorPrice = Factor::filter()->filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startWeek()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalMonthFactorPrice = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalBeforeMonthFactorPrice = Factor::filter()->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth(1)->startMonth()), Helper::vertaTocarbon(verta()->subMonth(1)->endMonth()->addDays(1))])->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalSixMonthFactorPrice = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->subMonths(6)))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalYearFactorPrice =  Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startYear()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalAllFactorPrice = Factor::filter()->where('status', true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
        }
        if(auth()->user()->hasPermission('view-sales-revenue'))
        {
            $totalMonthFactorCount = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalMonthFactorPaidCount = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();

            $totalUser = User::filter()->count();
            $totalDayUser = User::filter()->where('created_at', '>', Carbon::now()->subDay())->count();
            $totalMonthUser = User::filter()->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalDayFactor = Factor::filter()->where('created_at', '>', Carbon::now()->subDay())->where('status', '1')->count();
            $totalMonthFactor = Factor::filter()->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();
            $totalDayFactorPrice = Factor::filter()->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalDayFactorPriceOrgan = Factor::filter()->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->where('type' , 'credit')->sum('price');
            $totalYesterdayFactorPrice = Factor::filter()->whereBetween('created_at',[Helper::vertaTocarbon(verta()->subDays(1)->startDay()->addHours(1)), Helper::vertaTocarbon(verta()->subDays(1)->endDay()->addHours(1))])->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalWeekFactorPrice = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startWeek()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalMonthFactorPrice = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalBeforeMonthFactorPrice = Factor::filter()->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth(1)->startMonth()), Helper::vertaTocarbon(verta()->subMonth(1)->endMonth()->addDays(1))])->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalSixMonthFactorPrice = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->subMonths(6)))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalYearFactorPrice =  Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startYear()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalAllFactorPrice = Factor::filter()->where('status', true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
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
                $factor_month['success'][$i]= Factor::filter()->where('representation_id', $representation->id)->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonths($i)->startMonth()), Helper::vertaTocarbon(verta()->subMonths($i)->endMonth())])->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            }
            $factor_month['success'] = array_reverse($factor_month['success']);
            //            -------------------------------------------------------------------
            $factor_month['unsuccess']= array();
            for ($i = 0; $i <= 6; $i++) {
                $factor_month['unsuccess'][$i]= Factor::filter()->where('representation_id', $representation->id)->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonths($i)->startMonth()), Helper::vertaTocarbon(verta()->subMonths($i)->endMonth())])->where('status' , '!=' , 1)->sum('price');
            }
            $factor_month['unsuccess'] = array_reverse(  $factor_month['unsuccess']);
            //            -------------------------------------------------------------------
            for ($i = 0; $i <= 6; $i++) {
                $factor_week['success'][$i]=  Factor::filter()->where('representation_id', $representation->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->whereStatus(true)->sum('price');
            }
            $factor_week['success'] = array_reverse($factor_week['success']);
            //            -------------------------------------------------------------------
            for ($i = 0; $i <= 6; $i++) {
                $factor_week['unsuccess'][$i]=  Factor::filter()->where('representation_id', $representation->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->where('status' , '!=' , true)->sum('price');
            }
            $factor_week['unsuccess'] = array_reverse($factor_week['unsuccess']);
            //            -------------------------------------------------------------------
            $user_week=array();
            for ($i = 0; $i <= 6; $i++) {
                $user_week[$i]= User::filter()->where('representation_id', $representation->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->count();
            }
            $user_week=array_reverse($user_week);
            //            -------------------------------------------------------------------
            $user_month=array();
            for ($i = 0; $i <= 6; $i++) {
                $user_month[$i]= User::filter()->where('representation_id', $representation->id)->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonths($i)->startMonth()), Helper::vertaTocarbon(verta()->subMonths($i)->endMonth())])->count();
            }
            $user_month=array_reverse($user_month);
        } else {
            /*----------- user and payment monthly chart -----------*/
            $factor_month['success']=array();

            for ($i = 0; $i <= 6; $i++) {
                $from = date(Helper::vertaTocarbon(verta()->subMonths($i)->startMonth()));
                $to = date(Helper::vertaTocarbon(verta()->subMonths($i)->endMonth()));
                $factor_month['success'][$i]= Factor::filter()->whereBetween('created_at', [$from, $to])->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            }
            $factor_month['success'] = array_reverse( $factor_month['success']);
            //            -------------------------------------------------------------------
            $factor_month['unsuccess']=array();
            for ($i = 0; $i <= 6; $i++) {
                $from = date(Helper::vertaTocarbon(verta()->subMonths($i)->startMonth()));
                $to = date(Helper::vertaTocarbon(verta()->subMonths($i)->endMonth()));
                $factor_month['unsuccess'][$i]= Factor::filter()->whereBetween('created_at', [$from , $to])->where('status','!=',1)->sum('price');
            }
            $factor_month['unsuccess'] = array_reverse( $factor_month['unsuccess']);
            //            -------------------------------------------------------------------
            $factor_week['success']=array();
            for ($i = 0; $i <= 6; $i++) {
                $factor_week['success'][$i]=  Factor::filter()->whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            }
            $factor_week['success'] = array_reverse($factor_week['success']);
            //            -------------------------------------------------------------------
            $factor_week['unsuccess']=array();
            for ($i = 0; $i <= 6; $i++) {
                $factor_week['unsuccess'][$i]= Factor::filter()->whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->where('status' , '!=' , true)->sum('price');
            }
            $factor_week['unsuccess'] = array_reverse(  $factor_week['unsuccess']);
            //            -------------------------------------------------------------------
            $user_week=array();
            for ($i = 0; $i <= 6; $i++) {
                $user_week[$i]= User::filter()->whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->count();
            }
            $user_week = array_reverse($user_week);
            //            -------------------------------------------------------------------
            $user_month=array();
            for ($i = 0; $i <= 6; $i++) {
                $user_month[$i]=  User::filter()->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonths($i)->startMonth()), Helper::vertaTocarbon(verta()->subMonths($i)->endMonth())])->count();
            }
            $user_month = array_reverse($user_month);
            //            -------------------------------------------------------------------

        }
        if(auth()->user()->hasPermission('view-sales-revenue')){
            $factor_month['success'] =array();
            for ($i = 0; $i <= 6; $i++) {
                $from = date(Helper::vertaTocarbon(verta()->subMonths($i)->startMonth()));
                $to = date(Helper::vertaTocarbon(verta()->subMonths($i)->endMonth()));
                $factor_month['success'][$i]= Factor::filter()->whereBetween('created_at', [$from, $to])->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            }
            $factor_month['success'] = array_reverse( $factor_month['success']);
            //            -------------------------------------------------------------------
            $factor_month['unsuccess'] =array();
            for ($i = 0; $i <= 6; $i++) {
                $from = date(Helper::vertaTocarbon(verta()->subMonths($i)->startMonth()));
                $to = date(Helper::vertaTocarbon(verta()->subMonths($i)->endMonth()));
                $factor_month['unsuccess'][$i]=  Factor::filter()->whereBetween('created_at', [$from , $to])->where('status','!=',1)->whereIn('type' , Factor::getCashPaymentType())->filter()->sum('price');
            }
            $factor_month['unsuccess'] = array_reverse($factor_month['unsuccess']);
            //            -------------------------------------------------------------------
            $factor_week['success']=array();
            for ($i = 0; $i <= 6; $i++) {
                $factor_week['success'][$i]= Factor::filter()->whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->where('status' , 1)->whereIn('type' , Factor::getCashPaymentType())->filter()->sum('price');
            }
            $factor_week['success'] = array_reverse($factor_week['success']);
            //            -------------------------------------------------------------------
            $factor_week['unsuccess']=array();
            for ($i = 0; $i <= 6; $i++) {
                $factor_week['unsuccess'][$i]= Factor::filter()->whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->where('status' , '!=' , 1)->whereIn('type' , Factor::getCashPaymentType())->filter()->sum('price');
            }
            $factor_week['unsuccess'] = array_reverse($factor_week['unsuccess']);
            //            -------------------------------------------------------------------
            $user_week=array();
            for ($i = 0; $i <= 6; $i++) {
                $user_week[$i]= User::whereDate('created_at', Helper::vertaTocarbon(verta()->subDays($i)))->filter()->count();
            }
            $user_week = array_reverse($user_week);
        }
        $label_month = ['6 ماه قبل', '5 ماه قبل', '4 ماه قبل', '3 ماه قبل', '2 ماه قبل', '1 ماه قبل', 'ماه جاری'];
        $label_week = ['6 روز قبل', '5 روز قبل', '4 روز قبل', '3 روز قبل', '2 روز قبل', '1 روز قبل', 'امروز'];
        $services = Service::filter()->orderBy('created_at', 'desc')->get();
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

    /**
     * @param $service
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
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

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function dailyDesktop()
    {
        $user = auth()->user();
        $role = $this->dashboardService->getUserRole($user);
        $representation = $this->dashboardService->getUserRepresentation($user);
        $centerId = $this->dashboardService->getUserCenterId($user);
        $item = [
            'count_tickets' => $this->dashboardService->getUnprocessedTicketCount($user),
            'count_factoritem_confirm' => 0,
            'count_resume' => $this->dashboardService->getResumeCount($role, $representation),
            'count_request' => RequestModal::where('status', 2)->count(),
            'count_contact' => Contact::where('card_id', $role->card_id)->where('status', false)->count(),
        ];

        if ($centerId && $user->isAbleTo('file-detail')) {
            $item['count_factoritem_confirm'] = $this->dashboardService->getFactorItemConfirmCount($user);
        }

        $item += $this->dashboardService->getFactorCounts($user, $representation);

        $expire_dates = $this->dashboardService->getExpireDates();
        $near_end_date_financial_plans = $this->dashboardService->getNearEndDateFinancialPlans();

        return view('Admin.dailyDesktop', compact('item', 'expire_dates', 'near_end_date_financial_plans'));
    }
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function fetchUser(Request $request)
    {
        $users = User::where('family', 'LIKE', '%' . $request->value . '%')->orWhere('phone', $request->value)->get();
        return response(['operator' => true, 'data' => $users]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function cities(Request $request)
    {
        return response([
            'cities' => City::where('province_id', $request->id)->get(),
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
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

    /**
     * @param Request $request
     * @return mixed
     */
    public function summernote_image(Request $request)
    {
        return $this->upload($request->image);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function uploadFile(Request $request ) {
        return Helper::uploadImage( $request->file, $request->path );
    }

    /**
     * @param Request $request
     * @return string[]
     */
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
