<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Center;
use App\Models\CenterCategory;
use App\Models\CenterRequest;
use Illuminate\Http\Request;

class CenterRequestController extends Controller
{
    public function index()
    {
        //
    }

    public function create()
    {
        $categories = CenterCategory::where('type' , 'category')->get();
        $services = CenterCategory::where('type' , 'service')->get();
        return view('site.centerRequest', compact('categories', 'services'));
    }

    public function store(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required|string',
            'phone' => 'required',
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

        $center_request = CenterRequest::create([
            'center_category_id'=> $center_category_id,
            'phone'=> $request->phone,
            'status'=> 0,
            'type'=> $request->type,
            'name'=> $request->name,
            'subject'=> $request->subject,
            'description'=> $request->description,
            'city_id'=> $request->city_id,
            'province_id'=> $request->province_id,
        ]);

        $file_center = $request->urlfile;
        $alt = $request->alt;
        if ($file_center) {
            $files = $file_center;
            foreach ($files as $key => $file) {
                if ($file) {
                    if (isset($file_center)) {
                        $url = Helper::moveImage($file,'document');
                        $center_request->galleries()->create(['title' => $alt[$key] , 'image' => $url]);
                    }
                }
            }
        }

        return redirect()->back()->with([
            'center_success' => 'success'
        ]);
    }
}
