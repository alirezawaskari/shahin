<?php

namespace App\Http\Controllers\Admin;


use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Contact;
use App\Models\Representation;
use App\Models\Request as RequestModal;
use App\Models\Service;
use App\Models\TotalFactorItem;
use App\Models\User;
use App\Models\WorkSample;
use App\Services\V1\Admin\DailyDesktopService;
use App\Services\V1\Admin\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
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
    public function uploadFilesTemporarily(Request $request)
    {
        // Public endpoint: validate strictly to prevent abuse (arbitrary/oversized uploads).
        // A widget can opt into images-only by posting images_only=1 (e.g. the centerRequest
        // gallery form); document-bearing widgets (resumes, factor docs) omit it and keep the
        // wider whitelist below.
        $mimes = $request->boolean('images_only')
            ? 'mimes:jpg,jpeg,png,webp,svg,gif'
            : 'mimes:jpg,jpeg,png,webp,svg,gif,pdf,doc,docx,xls,xlsx,zip,rar';
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'max:6144', // 6 MB, matches the client-side limit
                $mimes,
            ],
        ], [
            'file.required' => 'فایلی ارسال نشده است.',
            'file.max'      => 'حجم فایل نباید بیشتر از ۶ مگابایت باشد.',
            'file.mimes'    => 'فرمت فایل مجاز نیست.',
        ]);

        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()->first()];
        }

        $media = MediaLibraryHelper::uploadImage($request->file, auth()->id());
        return [
            // Single temp id used for everything: hidden *_path (attachTo on save),
            // removeMedia (✕) and viewMedia (مشاهده). The whole MediaLibrary model is
            // deliberately NOT returned — this endpoint is public and serializing it
            // would leak internal columns (user_id, upload_location, …).
            'media_id' => optional($media->getFirstMedia('images'))->id,
            'status'   => true,
        ];
    }

    /**
     * Delete a Spatie media row by id (used by gallery remove buttons).
     */
    public function removeMedia(Request $request)
    {
        $media = \App\Models\Media::find($request->media_id);
        if ($media) {
            // native delete of just this file + row (avoids Spatie's recursive
            // directory listing that fails on Windows, and our shared per-date dirs)
            try {
                $path = $media->getPath();
                if (is_file($path)) {
                    @unlink($path);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('removeMedia unlink failed: ' . $e->getMessage());
            }
            \App\Models\Media::where('id', $media->id)->delete();
            return ['status' => true];
        }
        return ['status' => false];
    }

    /**
     * Stream a media file inline by id. Temp ajax uploads live on the private
     * `local` disk (no public url), so the "مشاهده" view links resolve here.
     * response()->file() sets the Content-Type from the file, so pdf/images
     * render in the browser and other types are offered for download.
     */
    public function viewMedia($id)
    {
        $media = \App\Models\Media::find($id);
        if (! $media) {
            abort(404);
        }
        $path = $media->getPath();
        if (! $path || ! is_file($path)) {
            abort(404);
        }
        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
        ]);
    }

    /**
     * Public inline view of a just-uploaded TEMP file (guest site forms, e.g. the
     * recruitment resume upload, have no dashboard auth). Scoped to temp holders
     * only — MediaLibrary + the private 'images' collection — so this public route
     * can never be used to stream a model's saved media (resumes, invoices, …) by
     * id. Temp ids are unguessable UUIDs and the file is deleted once the form is
     * submitted, so the exposure window is tiny.
     */
    public function viewTempMedia($id)
    {
        $media = \App\Models\Media::where('id', $id)
            ->where('model_type', \App\Models\MediaLibrary::class)
            ->where('collection_name', 'images')
            ->first();
        if (! $media) {
            abort(404);
        }
        $path = $media->getPath();
        if (! $path || ! is_file($path)) {
            abort(404);
        }
        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
        ]);
    }

    /**
     * Public removal of a just-uploaded TEMP file (guest site forms have no dashboard
     * auth). Scoped to temp holders only — MediaLibrary + the private 'images'
     * collection — so it can never delete a model's saved media by id. Called when a
     * site user removes the selection or picks a different file, so temp uploads don't
     * pile up orphaned on disk.
     */
    public function removeTempMedia(Request $request)
    {
        $media = \App\Models\Media::where('id', $request->media_id)
            ->where('model_type', \App\Models\MediaLibrary::class)
            ->where('collection_name', 'images')
            ->first();
        if (! $media) {
            return ['status' => false];
        }
        MediaLibraryHelper::deleteMedia($media);
        // drop the now-empty temp holder too
        \App\Models\MediaLibrary::where('id', $media->model_id)->delete();
        return ['status' => true];
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        if($user->level == 'ghorfeAdmin' && $user->hasPermission( 'ghorfe_online_list_site_content' ) && $user->hasPermission( 'ghorfe_online_list_official_info' ))
        {
            return redirect('dashboard/ghorfeOnlineList/'.ghorfe()->slug.'/edit');
        }
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
            $link = "lalecard.ir/".($service == 'all' ? 'link' : 'services/'.$service)."/".auth()->user()->id;
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
