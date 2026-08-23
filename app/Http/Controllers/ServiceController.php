<?php

namespace App\Http\Controllers;

use App\Models\Callcenter;
use App\Models\Service;
use App\Models\User;
use App\Models\Factor;
use App\Models\ServiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ServiceController extends Controller
{

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $services = Service::filter()->whereStatus(1)->where('isShow' , true)->orderBy('created_at', 'desc')->get();
        return view('site.services.index', compact('services'));
    }

    /**
     * @param Service $service
     * @param User $callcenter
     * @param Callcenter $task
     * @param Factor|null $factor
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function single(Service $service , User $callcenter , Callcenter $task, Factor $factor=null)
    {
        $financial_plans_types = $service->financialPlansTypes()->where('status' , 1)->where('is_show' , 1)->whereRaw('capacity > reserved')->where('end_date' , '>=' , now())->latest()->get()->unique();
        $questions=$service->askedQuestions;

        //$callcenter=$task->callcenter;
		$images = $service->galleries;
	    $is_service = true;
		$check = false;
        if (!$service->isShow && $service->status != 1)
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

            return view('site.services.single', compact('content' , 'images' ,'questions', 'is_service' , 'factor', 'callcenter','service', 'financial_plans_types' ,'required', 'requireServices', 'check','task'));
        }

        return view('site.services.single', compact('content' , 'images' ,'questions', 'is_service' , 'factor', 'callcenter','service', 'financial_plans_types' ,'task'));
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function getFinancialPlansType(Request $request)
    {
        if ($request->ajax()) {
            $service = Service::find($request->input('id'));
			$count = $service->financialPlansTypes->where('status' , true)->where('is_show' , true)->count();
            if ($count > 0) {
                $financial_plans_types = $service->financialPlansTypes->where('status' , true)->where('is_show' , true)->unique();
                return response()->json(['count' => 'available', 'financial_plans_types' => $financial_plans_types , 'length' => $count]);
            }
        }
    }
    public function getServiceItem(Request $request)
    {
        if ($request->ajax()) {
            if($request->input('id') === "more")
                return response()->json(['count' => 'more']);
            $service = Service::find($request->input('id'));
			$item = $service?->service_items;
            if (!empty($item))
                return response()->json(['count' => 'available', 'item' => $item , 'length' => count($item)]);
            else
                return response()->json(['count' => 'not available']);
        }
        abort(404);
    }
    /**
     * Summary of serviceItemSingle
     * @param ServiceItem $serviceItem
     * @param User $callcenter
     * @param Callcenter $task
     * @param Factor|null $factor
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function serviceItemSingle(ServiceItem $serviceItem, User $callcenter , Callcenter $task, Factor $factor=null )
    {
        $service = $serviceItem->service;
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
            return view('site.services.single', compact('images' ,'questions', 'is_service' , 'factor' ,'callcenter','service', 'financial_plans_types' ,'required', 'requireServices', 'check','task'));
        }
        $serviceItem['service_item_id'] = $serviceItem->id;
        $serviceItem['id'] = $service->id;
        $serviceItem['slug'] = $service->slug;
        $service = $serviceItem;
        return view('site.services.single', compact('images' ,'questions', 'is_service' , 'factor' , 'callcenter','service', 'financial_plans_types' ,'task'));
       }
}
