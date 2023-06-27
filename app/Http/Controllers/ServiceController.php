<?php

namespace App\Http\Controllers;

use App\Models\Callcenter;
use App\Models\Service;
use App\Models\User;
use App\Models\Factor;
use Illuminate\Http\Request;

class ServiceController extends Controller
{

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $services = Service::whereStatus(1)->where('isShow' , true)->orderBy('created_at', 'desc')->get();
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
        $financial_plans_types = $service->financialPlansTypes()->where('status' , 1)->where('is_show' , 1)->whereRaw('capacity > reserved')->where('end_date' , '>=' , now())->get()->unique();

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

            return view('site.services.single', compact('images' , 'is_service' , 'factor', 'callcenter','service', 'financial_plans_types' ,'required', 'requireServices', 'check','task'));
        }

        return view('site.services.single', compact('images' , 'is_service' , 'factor', 'callcenter','service', 'financial_plans_types' ,'task'));
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
                $financial_plans_types = $service->financialPlansTypes->where('status' , true)->where('is_show' , true);
                return response()->json(['count' => 'available', 'financial_plans_types' => $financial_plans_types , 'length' => $count]);
            }
        }
    }
}
