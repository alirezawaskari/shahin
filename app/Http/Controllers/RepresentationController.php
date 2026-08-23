<?php

namespace App\Http\Controllers;

use App\Models\Callcenter;
use App\Models\Factor;
use App\Models\Representation;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\User;
use Illuminate\Support\Facades\View;

class RepresentationController extends Controller
{
    /**
     * @param Representation $representation
     * @param User $callcenter
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function index(Representation $representation , User $callcenter)
    {
        session()->put('representation', $representation);
        if ($representation->status){
            $sliders = $representation->sliders()->whereStatus(true)->where('type' , 0)->orderBy('id', 'desc')->get();
            $sliders_mobile = $representation->sliders()->whereStatus(true)->where('type' , 4)->orderBy('id', 'desc')->get();
            $representation->load('services');
            $services = $representation->services()->filter()->whereStatus(1)->get();

            return view('site.representation.index', compact('representation' , 'callcenter','sliders', 'services','sliders_mobile'));
        }
       return abort(404);
    }
    /**
     * Summary of recruitmentNoticeList
     * @param Representation $representation
     * @return \Illuminate\Contracts\View\View
     */
    public function recruitmentNoticeList(Representation $representation)
    {
        $recruitment_notices = $representation->recruitmentNotices()->get()->groupBy('province_id');
        return view('site.recruitmentNotice', compact('recruitment_notices'));
    }

    /**
     * @param Representation $representation
     * @param Service $service
     * @param User $callcenter
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function single(Representation $representation , Service $service, User $callcenter , Callcenter $task, Factor $factor=null )
    {
        session()->put('representation', $representation);
        if(!$representation->status)
        {
            return redirect('/');
        }
        $financial_plans_types = $service->financialPlansTypes()->where('status' , 1)->where('is_show' , 1)->whereRaw('capacity > reserved')->where('end_date' , '>=' , now())->get()->unique();
        $questions=$service->askedQuestions;

        //$callcenter=$task->callcenter;
		$images = $service->galleries;
	    $is_service = true;
		$check = false;
        if (!$service->isShow && !$service->status)
        {
            return redirect('/');
        }

		if ($service->id == 102)
        {
            return redirect('/agency');
        }


		$user = auth()->user();
		if(isset($factor['id']))
		{
			$user = $factor->user;
		}
        $registerForm = View::make('site.services.registerForm' , compact('service' , 'financial_plans_types' , 'callcenter'))->render();
        $content = str_replace('[form]', $registerForm, $service->content);
        if ($user) {
            $required = null;
            $founded = [];
            $requireServices = [];
            if (!is_null($service->required) && count($service->required) > 0) {
                foreach ($service->required as $item) {
                    $user_factor = $user->factors()->where('service_id', $item)->whereStatus(true)->first();
                    if ($user_factor) {
                        array_push($founded, $user_factor->id);
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

            return view('site.representation.single', compact('content','images' ,'questions', 'is_service' , 'factor', 'representation' ,'callcenter','service', 'financial_plans_types' ,'required', 'requireServices', 'check','task'));
        }

        return view('site.representation.single', compact('content' , 'images' ,'questions', 'is_service' , 'factor' , 'representation' , 'callcenter','service', 'financial_plans_types' ,'task'));
       }

    /**
     * Summary of serviceItemSingle
     * @param Representation $representation
     * @param ServiceItem $serviceItem
     * @param User $callcenter
     * @param Callcenter $task
     * @param Factor|null $factor
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function serviceItemSingle(Representation $representation , ServiceItem $serviceItem, User $callcenter , Callcenter $task, Factor $factor=null )
    {
        session()->put('representation', $representation);
        $service = $serviceItem->service;

        if(!$representation->status)
        {
            return redirect('/');
        }
        $financial_plans_types = $service->financialPlansTypes()->where('status' , 1)->where('is_show' , 1)->whereRaw('capacity > reserved')->where('end_date' , '>=' , now())->get()->unique();
        $questions=$serviceItem->askedQuestions;

        //$callcenter=$task->callcenter;
		$images = $serviceItem->galleries;
	    $is_service = true;
		$check = false;
        if (!$serviceItem->isShow && !$serviceItem->status)
        {
            return redirect('/');
        }

		$user = auth()->user();
		if(isset($factor['id']))
		{
			$user = $factor->user;
		}
        if ($user) {
            $required = null;
            $founded = [];
            $requireServices = [];
            if (!is_null($service->required) && count($service->required) > 0) {
                foreach ($service->required as $item) {
                    $user_factor = $user->factors()->where('service_id', $item)->whereStatus(true)->first();
                    if ($user_factor) {
                        array_push($founded, $user_factor->id);
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
            $serviceItem['service_item_id'] = $serviceItem->id;
            $serviceItem['id'] = $service->id;
            $serviceItem['slug'] = $service->slug;
            $service = $serviceItem;
            return view('site.representation.single', compact('images' ,'questions', 'is_service' , 'factor', 'representation' ,'callcenter','service', 'financial_plans_types' ,'required', 'requireServices', 'check','task'));
        }
        $serviceItem['service_item_id'] = $serviceItem->id;
        $serviceItem['id'] = $service->id;
        $serviceItem['slug'] = $service->slug;
        $service = $serviceItem;
        return view('site.representation.single', compact('images' ,'questions', 'is_service' , 'factor' , 'representation' , 'callcenter','service', 'financial_plans_types' ,'task'));
       }
}
