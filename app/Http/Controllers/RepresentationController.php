<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Province;
use App\Models\Representation;
use App\Models\Service;
use App\Models\User;

class RepresentationController extends Controller
{
    public function index(Representation $representation , User $callcenter)
    {
        if ($representation->status){
            $representation->load('services');
            return view('site.representation.index', compact('callcenter','representation'));
        }
       return abort(404);
    }


    public function single(Representation $representation , Service $service, User $callcenter )
    {
        $user = auth()->user();

        if ($user) {
            $required = null;
            $founded = [];
            $requireServices = [];
            if (!is_null($service->required) && count($service->required) > 0) {
                foreach ($service->required as $item) {
                    $factor = $user->factors()->where('service_id', $item)->whereStatus(true)->first();
                    if ($factor){
                        array_push($founded , $factor->id);
                    }
                }
                $requireServices = Service::find($service->required);
                $required = false;
                if (!is_null($founded) && count($founded) == count($service->required)) {
                    $required = true;
                }
            }
            return view('site.representation.single', compact('callcenter','representation' ,'service', 'required', 'requireServices'));
        }

        $provinces = Province::get();
        $citys = City::get();
        return view('site.representation.single' , compact('callcenter','representation' , 'service' , 'provinces' , 'citys'));
    }
}
