<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CenterRequestExport;
use App\Helpers\Helper;
use App\Http\Resources\CallcenterCollection;
use App\Http\Resources\CenterRequestCollection;
use App\Models\Callcenter;
use App\Models\CenterRequest;
use App\Models\History;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Center;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class CenterRequestController extends AdminController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        return view('Admin.centerRequest.all');
    }

    /**
     * @param Request $request
     * @return CenterRequestCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter(Request $request)
    {

        $center_requests = CenterRequest::filter()->latest();
        if(auth()->user()->admin_representation_id)
        {
            $center_requests = auth()->user()->admin_representation->centerRequests()->filter()->latest();
        }
        if ($request->exel == 'on') {
            return (new CenterRequestExport($center_requests))->download('centerRequest.xlsx');
        }

        return new CenterRequestCollection($center_requests->paginate(20));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        return view('Admin.centerRequest.create');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $request['phone'] = Helper::numberConverter($request->phone);

        $this->validate(request(), [
            'name' => 'required|string',
            'family' => 'required|string',
            'phone' => 'numeric|required|regex:/(09)[0-9]{9}/|digits:11|',
            'description' => 'required',
            'product_category' => "required_if:cat_type,==,category",
            'service_category' => "required_if:cat_type,==,service",
        ]);

        if ($request->cat_type == 'category') {
            $center_category_id = $request->product_category;
        } else {
            $center_category_id = $request->service_category;
        }

        $input['center_category_id'] = $center_category_id;
        unset($input['product_category']);
        unset($input['service_category']);
        unset($input['cat_type']);
        //check if user not exist -> create user
        User::createUser($request);
        $user = User::where('phone',$request->phone)->first();
        //create task (callCenter)
        self::createTask($user , $request);
        $center_request = self::createCenterRequest($center_category_id, $request);
        if(auth()->user()->admin_representation_id)
        {
            $center_request->update(['representation_id' => auth()->user()->admin_representation_id]);
        }
        return redirect(route('centerRequest.index'))->with([
            'create' => 'success',
        ]);
    }

    /**
     * @param CenterRequest $centerRequest
     */
    public function show(CenterRequest $centerRequest)
    {
        //
    }

    /**
     * @param CenterRequest $centerRequest
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(CenterRequest $centerRequest)
    {

        $center_request = $centerRequest;
        $histories = $center_request->user_id !== null ? History::where('user_id',$center_request->user_id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            : [];
        // Spatie media if any, else the legacy `galleries` rows (which a save migrates).
        // Each entry exposes ->url and ->title regardless of which system served it.
        $galleries = $centerRequest->galleryImages();
        return view('Admin.centerRequest.edit', compact('galleries', 'histories', 'center_request'));
    }

    /**
     * @param Request $request
     * @param CenterRequest $centerRequest
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, CenterRequest $centerRequest)
    {
        $this->validate(request(), [
            'name' => 'required|string',
            'phone' => 'required',
            'description' => 'required',
            'product_category' => "required_if:cat_type,==,category",
            'service_category' => "required_if:cat_type,==,service",
        ]);

        $input = $request->all();
        $representation = auth()->user()->admin_representation_id;
        if ($request->cat_type == 'category') {
            $center_category_id = $input['product_category'];
        } else {
            $center_category_id = $input['service_category'];
        }

        $input['center_category_id'] = $center_category_id;
        unset($input['cat_type']);
        unset($input['product_category']);
        unset($input['service_category']);

        $centerRequest->update($input);

        if ($centerRequest->status == 1) {
            Center::firstOrCreate(['creator_representation_id' => $representation, 'center_request_id' => $centerRequest->id, 'status' => 1]);
        }

        $centerRequest->histories()->create([
            'user_id' => auth()->user()->id,
            'status' => 2,
            'description' => $request->answer,
        ]);

        return redirect(route('centerRequest.index'))->with([
            'update' => 'success',
        ]);
    }

    /**
     * @param CenterRequest $centerRequest
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function serviceTariffs(CenterRequest $centerRequest)
    {
        $service_tariffs = $centerRequest->service_tariffs;
        return view('Admin.centerRequest.tariff', compact('service_tariffs'));
    }

    /**
     * @param CenterRequest $centerRequest
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function destroy(CenterRequest $centerRequest)
    {
        if (auth()->user()->isAbleTo('center-request-delete')) {
            $centerRequest->delete();
            return response([
                'operator' => true,
                'status' => 'success',
                'message' => 'درخواست با موفقیت حذف شد'
            ]);
        } else {
            return response([
                'operator' => true,
                'status' => 'unauthorized',
                'message' => 'شما مجاز به انجام این کار نیستید.'
            ]);
        }
    }
    /**
     * @param \App\Models\User $user
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public static function createTask(User $user, Request $request): void
    {
        $callcenter_roles = Role::select('id')->where('name' , 'superadmin')->get();
        $center_description = '';
        if (isset($request->description) && $request->description != '') {
            $center_description = $request->description;
        }
        $description = "درخواست مرکز ،
          موضوع :
         {$request->subject}،
         توضیحات درخواست دهنده :
         {$center_description}،
         ";
        $task = Callcenter::createTask($user, $description , $callcenter_roles);
        $task->services()->attach([104]);
    }


    /**
     * @param mixed $center_category_id
     * @param Request $request
     */
    public static function createCenterRequest($center_category_id, Request $request)
    {
        $center_request = CenterRequest::create([
            'center_category_id' => $center_category_id,
            'representation_id' => $request->representation_id,
            'phone' => $request->phone,
            'status' => 0,
            'type' => $request->type,
            'name' => $request->name . ' ' . $request->family,
            'subject' => $request->subject,
            'description' => $request->description,
            'city_id' => $request->city_id,
            'province_id' => $request->province_id,
        ]);
        return $center_request;
    }
}
