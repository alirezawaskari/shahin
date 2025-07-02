<?php

namespace App\Http\Controllers\Admin;


use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Contact;
use App\Models\Representation;
use App\Models\Request as RequestModal;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkSample;
use App\Services\V1\Admin\DailyDesktopService;
use App\Services\V1\Admin\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;


class AdminController extends Controller
{
    protected DashboardService $dashboardService;
    protected DailyDesktopService $dailyDesktopService;

    public  function __construct(DashboardService $dashboardService,DailyDesktopService $dailyDesktopService)
    {
        $this->dashboardService = $dashboardService;
        $this->dailyDesktopService = $dailyDesktopService;
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

    public function dashboard(Request $request)
    {
        $totalMonthFactorCount = 0;
        $totalMonthFactorPaidCount = 0;
        $type = $request->type;
        $representation = auth()->user()->admin_representation;
        $label_month = ['6 ماه قبل', '5 ماه قبل', '4 ماه قبل', '3 ماه قبل', '2 ماه قبل', '1 ماه قبل', 'ماه جاری'];
        $label_week = ['6 روز قبل', '5 روز قبل', '4 روز قبل', '3 روز قبل', '2 روز قبل', '1 روز قبل', 'امروز'];
        $services = Service::filter()->orderBy('created_at', 'desc')->get();
        $detail_data = $this->dashboardService->getDetailData($representation);
        extract($detail_data);

        $factor_data = $this->dashboardService->getFactorData($representation);
        extract($factor_data);

        $service_data = $this->dashboardService->getServicesData($services,$representation);
        extract($service_data);

        $representations = Representation::all();

        return view('Admin.index', compact('totalMonthFactorCount',
            'totalMonthFactorPaidCount','link',
            'services' , 'representations','type','service_name','service_count','label_month',
            'label_week', 'factor_week', 'factor_month', 'user_month', 'user_week','totalUser',
            'totalDayUser', 'totalDayFactor', 'totalDayFactorPrice','totalDayFactorPriceOrgan',
            'totalYesterdayFactorPrice', 'totalWeekFactorPrice' ,'totalMonthFactorPrice',
            'totalSixMonthFactorPrice', 'totalMonthFactor','totalBeforeMonthFactorPrice',
            'totalYearFactorPrice', 'totalAllFactorPrice' ,'totalMonthUser'));
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
        $role = $this->dailyDesktopService->getUserRole($user);
        $representation = $this->dailyDesktopService->getUserRepresentation($user);
        $centerId = $this->dailyDesktopService->getUserCenterId($user);
        $item = [
            'count_tickets' => $this->dailyDesktopService->getUnprocessedTicketCount($user),
            'count_factoritem_confirm' => 0,
            'count_resume' => $this->dailyDesktopService->getResumeCount($role, $representation),
            'count_request' => RequestModal::where('status', 2)->count(),
            'count_contact' => Contact::where('card_id', $role->card_id)->where('status', false)->count(),
        ];

        if ($centerId && $user->isAbleTo('file-detail')) {
            $item['count_factoritem_confirm'] = $this->dailyDesktopService->getFactorItemConfirmCount($user);
        }

        $item += $this->dailyDesktopService->getFactorCounts($user, $representation);

        $expire_dates = $this->dailyDesktopService->getExpireDates();
        $near_end_date_financial_plans = $this->dailyDesktopService->getNearEndDateFinancialPlans();

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
