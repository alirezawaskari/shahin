<?php

namespace App\Http\Controllers\Admin;

use App\Models\Representation;
use App\Models\Service;
use App\Models\Province;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class RepresentationController extends AdminController
{
    public function index()
    {
        $representations = Representation::latest()->get();
        return view('Admin.representation.all', compact('representations'));
    }

    public function create()
    {
        $services = Service::where('status' , true)->get();
        return view('Admin.representation.create' , compact('services'));
    }

    public function store(Request $request)
    {
        $this->validate(request(), [
            'fa_name' => 'required|string',
            'en_name' => 'required|string',
			'agency_name' => 'required|string',
            'agency_melicode' => 'numeric|required',
            'header_description' => 'nullable|string',
            'status' => 'required|string',
            'service_id' => 'required',
        ]);
        $inputs = $request->all();

        if($request->urlstamp)
        {
            $inputs['stamp'] = Helper::moveImage($request->urlstamp,'document');
        }
        if($request->urlimage)
        {
            $inputs['image'] = Helper::moveImage($request->urlimage,'document');
        }

        unset($inputs['service_id']);
        unset($inputs['sms_code']);
        unset($inputs['urlstamp']);
        unset($inputs['urlimage']);

        $representation = Representation::create($inputs);

        $representation->services()->sync($request->input('service_id'));

        $codes = [];
        $array = explode(',' , $request->input('sms_code'));
        foreach ($array as $item) {
           array_push($codes , Helper::numberConverter($item));
        }
        $representation->update([
           'sms_code' => $codes,
        ]);

        return redirect(route('representation.index'))->with([
            'create' => 'success'
        ]);
    }

    public function edit(Representation $representation)
    {
//        $services = Service::status()->get();
        $services = Service::all();
        $provinces = Province::all();
        return view('Admin.representation.edit' ,compact('representation' , 'services' , 'provinces'));
    }

    public function update(Request $request, Representation $representation)
    {
        $this->validate(request(), [
            'fa_name' => 'required|string',
            'slug' => 'required|string',
			'agency_name' => 'required|string',
            'agency_melicode' => 'numeric|required',
			'header_description' => 'nullable|string',
            'status' => 'required|string',
            'service_id' => 'required',
        ]);
        $inputs = $request->all();


        if($request->urlstamp)
        {
            Helper::deleteImage($representation->stamp);
            $inputs['stamp'] = Helper::moveImage($request->urlstamp,'document');
        }
        if($request->urlimage)
        {
            Helper::deleteImage($representation->image);
            $inputs['image'] = Helper::moveImage($request->urlimage,'document');
        }

        unset($inputs['service_id']);
        unset($inputs['sms_code']);
        unset($inputs['urlstamp']);
        unset($inputs['urlimage']);

        $representation->update($inputs);
        $representation->services()->sync($request->input('service_id'));

        $codes = [];
        $array = explode(',' , $request->input('sms_code'));
        foreach ($array as $item) {
            array_push($codes , Helper::numberConverter($item));
        }
        $representation->update([
            'sms_code' => $codes,
        ]);

        return redirect(route('representation.index'))->with([
            'update' => 'success'
        ]);
    }

    public function destroy(Representation $representation)
    {
        $representation->delete();
        return redirect(route('representation.index'))->with([
            'delete' => 'success'
        ]);
    }
}
