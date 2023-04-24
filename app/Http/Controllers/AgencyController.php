<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Factor;
use App\Models\Image;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    /**
     * @param Factor $factor
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index(Factor $factor)
     {
		$check = false;
        $callcenter = user::find('23235');
        $service = Service::where('slug' , 'business-investment-therapeutic')->first();
        $financial_plans_types = $service->financialPlansTypes->where('is_show');

        $user = auth()->user();
		if(isset($factor))
		{
			$user = $factor->user;
		}
		$images = Image::where('service_id' , $service->id)->get();
        if ($user) {
            $required = null;
            $founded = [];
            $requireServices = [];
            if (!is_null($service->required) && count($service->required) > 0) {
                foreach ($service->required as $item) {
                    $factor = $user->factors()->where('service_id', $item)->whereStatus(true)->first();
                    if ($factor) {
                        array_push($founded, $factor->id);
                    }
                }
                $requireServices = Service::find($service->required);
                $required = false;
                if (!is_null($founded) && count($founded) == count($service->required)) {
                    $required = true;
                }
            }
            $check = $user->factors()->whereStatus(true)->whereHas('factor_items', function ($query) use ($service) {
                $query->where('service_id', $service->id);
            })->exists();

            if ($check) {
                if ($service->multiple) {
                    $check = false;
                }
            }
            return view('site.agencies.index', compact('financial_plans_types' , 'factor' , 'callcenter','images' ,'service', 'required', 'requireServices', 'check'));
        }

        return view('site.agencies.index' , compact('financial_plans_types' , 'factor' , 'callcenter' , 'service' , 'images'));
    }

    /**
     * @param Service $service
     * @param User $callcenter
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function single(Service $service , User $callcenter)
    {
        $user = auth()->user();
		$images = Image::where('service_id' , $service->id)->get();
        if ($user) {
            $required = null;
            $founded = [];
            $requireServices = [];
            if (!is_null($service->required) && count($service->required) > 0) {
                foreach ($service->required as $item) {
                    $factor = $user->factors()->where('service_id', $item)->whereStatus(true)->first();
                    if ($factor) {
                        array_push($founded, $factor->id);
                    }
                }
                $requireServices = Service::find($service->required);
                $required = false;
                if (!is_null($founded) && count($founded) == count($service->required)) {
                    $required = true;
                }
            }
            $check = $user->factors()->whereStatus(true)->whereHas('factor_items', function ($query) use ($service) {
                $query->where('service_id', $service->id);
            })->exists();

            if ($check) {
                if ($service->multiple) {
                    $check = false;
                }
            }
            return view('site.agencies.single', compact('callcenter','images' ,'service', 'required', 'requireServices', 'check'));
        }

        return view('site.agencies.single', compact('callcenter','images' ,'service'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function agency_update()
    {
        return view('site.agencies.index');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
            $values = [
                'name_company' => $request->input('name_company'),
                'job' => $request->input('job'),
                'first_date' => $request->input('first_date'),
                'last_date' => $request->input('last_date'),
                'phone_company' => $request->input('phone_company'),
                'address_company' => $request->input('address_company'),
            ];
        $input = $request->all();
        unset($input["name_company"]);
        unset($input["job"]);
        unset($input["first_date"]);
        unset($input["last_date"]);
        unset($input["phone_company"]);
        unset($input["address_company"]);
        $input['values'] = json_encode($values);
        if (Agency::where('phone' , $request->input('phone'))->first()){
            Agency::where('phone' , $request->input('phone'))->update($input);
            return redirect(route('agencies'))->with([
                'agency' => 'success'
            ]);
        }else{
            Agency::Create($input);
            return redirect(route('agencies'))->with([
                'agency' => 'success'
            ]);
        }
    }
}
