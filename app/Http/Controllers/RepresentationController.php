<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Province;
use App\Models\Representation;
use App\Models\Service;
use App\Models\User;

class RepresentationController extends Controller
{
    /**
     * @param Representation $representation
     * @param User $callcenter
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function index(Representation $representation , User $callcenter)
    {
        if ($representation->status){
            $representation->load('services');
            return view('site.representation.index', compact('callcenter','representation'));
        }
       return abort(404);
    }


    /**
     * @param Representation $representation
     * @param Service $service
     * @param User $callcenter
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function single(Representation $representation , Service $service, User $callcenter )
    {
        $user = auth()->user();
        $financial_plans_types = $service->financialPlansTypes()->where('status' , 1)->where('is_show' , 1)->whereRaw('capacity > reserved')->where('end_date' , '>=' , now())->get();

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
            $check = $user->factors()->whereStatus(true)->whereHas('factorItems', function ($query) use ($service) {
                $query->where('factoritemable_id', $service->id);
            })->exists();

            if ($check) {
                if ($service->multiple) {
                    $check = false;
                }
            }
            return view('site.representation.single', compact('financial_plans_types' , 'callcenter','representation' ,'service', 'required', 'requireServices' , 'check'));
        }

        $provinces = Province::get();
        $citys = City::get();
        return view('site.representation.single' , compact('financial_plans_types' , 'callcenter','representation' , 'service' , 'provinces' , 'citys'));
    }
}
