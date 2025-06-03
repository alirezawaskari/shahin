<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\CenterCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CenterRequestController extends Controller
{
    /**
     *
     */
    public function index()
    {
        //
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        $categories = CenterCategory::where('type' , 'category')->get();
        $services = CenterCategory::where('type' , 'service')->get();
        return view('site.centerRequest', compact('categories', 'services'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request['phone'] = Helper::numberConverter($request->phone);

        $this->validate(request(), [
            'name' => 'required|string',
            'family' => 'required|string',
            'gender' => ['required',Rule::in(array(0,1))],
            'subject' => 'required|string',
            'phone' => 'numeric|required|regex:/(09)[0-9]{9}/|digits:11|',
            'description' => 'required',
        ]);

        if($request->cat_type == 'category')
        {
            $center_category_id = $request->category ? $request->category : 16;
        }
        else
        {
            $center_category_id = $request->service ? $request->service : 30;
        }
        //check if user not exist -> create user
        $request['representation_id'] = session()->has('representation') ? session()->get('representation')->id : '';
        User::createUser($request);
        $user = User::where('phone',$request->phone)->first();
        //create task (callCenter)
        \App\Http\Controllers\Admin\CenterRequestController::createTask($user,$request);
        $center_request =\App\Http\Controllers\Admin\CenterRequestController::createCenterRequest($center_category_id, $request);

        $this->storeFiles($request, $center_request);

        return redirect()->back()->with([
            'center_success' => 'success'
        ]);
    }

    /**
     * @param Request $request
     * @param $center_request
     * @return void
     */
    public function storeFiles(Request $request, $center_request): void
    {
        $file_center = $request->urlfile;
        $alt = $request->alt;
        if ($file_center) {
            $files = $file_center;
            foreach ($files as $key => $file) {
                if ($file) {
                    if (isset($file_center)) {
                        $url = Helper::moveImage($file, 'document');
                        $center_request->galleries()->create(['title' => $alt[$key], 'image' => $url]);
                    }
                }
            }
        }
    }

//    /**
//     * @param mixed $center_category_id
//     * @param Request $request
//     * @return mixed
//     */
//    public function createCenterRequest(mixed $center_category_id, Request $request)
//    {
//        $center_request = CenterRequest::create([
//            'center_category_id' => $center_category_id,
//            'phone' => $request->phone,
//            'status' => 0,
//            'type' => $request->type,
//            'name' => $request->name. ' ' . $request->family,
//            'subject' => $request->subject,
//            'description' => $request->description,
//            'city_id' => $request->city_id,
//            'province_id' => $request->province_id,
//        ]);
//        return $center_request;
//    }
}
