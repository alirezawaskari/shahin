<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CenterRequestExport;
use App\Helpers\Helper;
use App\Http\Resources\CenterRequestCollection;
use App\Models\CenterRequest;
use Illuminate\Http\Request;
use App\Models\Center;

class CenterRequestController extends AdminController
{
    public function index()
    {
        return view('Admin.centerRequest.all');
    }

    public function filter(Request $request) {

        $center_requests = CenterRequest::filter()->latest();

        if ($request->exel == 'on') {
            return (new CenterRequestExport($center_requests))->download('centerRequest.xlsx');
        }

        return new CenterRequestCollection( $center_requests->paginate( 20 ) );
    }

    public function create()
    {
        return view('Admin.centerRequest.create');
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $this->validate(request(), [
            'name' => 'required|string',
            'phone' => 'required',
            'description' => 'required',
            'product_category' => "required_if:cat_type,==,category",
            'service_category' => "required_if:cat_type,==,service",
        ]);

        if ( $request->cat_type == 'category' ) {
            $center_category_id = $request->product_category;
        } else {
            $center_category_id = $request->service_category;
        }

        $input['center_category_id'] = $center_category_id;
        unset($input['product_category']);
        unset($input['service_category']);
        unset($input['cat_type']);

        CenterRequest::create($input);

        return redirect(route('centerRequest.index'))->with([
            'create' => 'success',
        ]);
    }

    public function show(CenterRequest $centerRequest)
    {
        //
    }

    public function edit(CenterRequest $centerRequest)
    {
        $center_request = $centerRequest;
        $histories = [];
        $comments = $center_request->histories ? Helper::historyCollection($histories , $center_request->histories) : [];
        $galleries = $centerRequest->galleries;

        return view('Admin.centerRequest.edit' , compact('galleries' , 'comments' , 'center_request'));
    }

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
        if ( $request->cat_type == 'category' ) {
            $center_category_id = $input['product_category'];
        } else {
            $center_category_id = $input['service_category'];
        }

        $input['center_category_id'] = $center_category_id;
        unset($input['cat_type']);
        unset($input['product_category']);
        unset($input['service_category']);

        $centerRequest->update($input);

        if($centerRequest->status == 1)
        {
            Center::firstOrCreate(['representation_id' => $representation, 'center_request_id' => $centerRequest->id , 'status' => 1]);
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

    public function serviceTariffs(CenterRequest $centerRequest)
    {
        $service_tariffs = $centerRequest->service_tariffs;
        return view('Admin.centerRequest.tariff' , compact('service_tariffs'));
    }

    public function destroy(CenterRequest $centerRequest)
    {
        if(auth()->user()->isAbleTo('center-request-delete'))
        {
            $centerRequest->delete();
            return response([
                'operator' => true,
                'status'=>'success',
                'message'=>'درخواست با موفقیت حذف شد'
            ]);
        }
        else
        {
            return response([
                'operator' => true,
                'status'=>'unauthorized',
                'message'=>'شما مجاز به انجام این کار نیستید.'
            ]);
        }
    }
}
