<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Province;
use App\Models\SingleWebPage;
use App\Models\Service;
use App\Models\User;

class SingleWebPageController extends Controller
{
    /**
     * @param SingleWebPage $SingleWebPage
     * @param User $callcenter
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function index(SingleWebPage $singleWebPage)
    {
        if ($singleWebPage->status){
            $singleWebPage->load('services');
            return view('site.singleWebPage.index', compact('singleWebPage'));
        }
        return abort(404);
    }


    /**
     * @param SingleWebPage $singleWebPage
     * @param Service $service
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function single(SingleWebPage $singleWebPage , Service $service )
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

            $factor=null;

            if ($check) {
                if ($service->multiple) {
                    $check = false;
                }
                $factor=  $user->factors()->whereStatus(true)->whereHas('factorItems', function ($query) use ($service) {
                    $query->where('factoritemable_id', $service->id);
                })->first();
            }

            return view('site.singleWebPage.single', compact('singleWebPage','financial_plans_types' ,'service', 'required', 'requireServices' , 'check','factor'));
        }

        $provinces = Province::get();
        $citys = City::get();
        return view('site.singleWebPage.single' , compact('singleWebPage','financial_plans_types' , 'service' , 'provinces' , 'citys'));
    }
}
