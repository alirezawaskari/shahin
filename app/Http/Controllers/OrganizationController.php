<?php

namespace App\Http\Controllers;

use App\Models\Callcenter;
use App\Models\City;
use App\Models\Factor;
use App\Models\Organization;
use App\Models\Province;
use App\Models\Service;
use App\Models\User;

class OrganizationController extends Controller
{
    /**
     * @param Organization $organization
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function index(Organization $organization)
    {
        if ($organization->status){
            $organization->load('services');
            $services = Service::filter()->whereStatus(false)->where('isShow' , true)->orderBy('created_at', 'desc')->get();
            return view('site.organization.index', compact('organization', 'services'));
        }
       return abort(404);
    }

    /**
     * @param Organization $organization
     * @param Service $service
     * @param User $callcenter
     * @param Factor $factor
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function single(Organization $organization , Service $service , User $callcenter , Factor $factor)
    {
        $user = auth()->user();
		//if(!$user)
		//{
		    $required = null;
			$provinces = Province::get();
            $citys = City::get();
            return view('site.organization.single' , compact('organization','service' , 'provinces' , 'citys' , 'required' , 'callcenter' , 'factor'));
		//}
        if($user->credit >= ($service->price + $service->price * $service->tax/100))
        {
            $check = $user->factors()->where('type' , 'organ')->whereStatus(true)->whereHas('factor_items', function ($query) use ($service) {
                $query->where('service_id', $service->id);
            })->exists();
            if($check == null)
            {
                $callcenter = Callcenter::create([
                    'reg_id' => $user->id,
                    'user_id' => 11333,
                    'type' => 2,
                    'status' => 0,
                    'representation_id' => auth()->user()->admin_representation ? auth()->user()->admin_representation->id : null,
                ]);
                $factor = $callcenter->factors()->create([
                    'status' => true,
                    'confirm' => false,
                    'user_id' => $user->id,
                    'task_id' => $callcenter->id,
                    'price' => $service->price + $service->price * $service->tax/100,
                    'tax' => $service->price * $service->tax/100,
                    'count' => 1,
                    'callcenter_id' => 11333,
                    'type' => 'organ',
                    'representation_id' => auth()->user()->admin_representation ? auth()->user()->admin_representation->id : null,
                    'organization_id' => $user->organization_id,
                    'expired_at' => now()->addDay(1),
                ]);
                 $factor_item = $factor->factor_items()->create([
                    'service_id' => $service->id,
                    'service_item_id' => null,
                    'count' => 1,
                    'complete'=>1,
                    'price' => $service->price,
                ]);

				 $factor_item = $factor_item->update([
                    'supervisor_id' => $factor_item->id,
                ]);

                $callcenter->update([
                    'status' => 3
                ]);
                $user->update([
                    'credit' => $user->credit - $factor->price
                ]);
                $callcenter->histories()->create([
                    'user_id' => auth()->user()->id,
                    'callcenter_id' => $callcenter->callcenter->id,
                    'status' => 1,
                    'description' => 'فاکتور: ' . $factor->resnumber . ' ارسال گردید و در انتظار پرداخت قرار دارد',
                ]);
                return redirect(route('checkout', [$factor->resnumber]))->with([
                    'payment' => 'success',
                ]);
            }
			return redirect('profile');
        }
		$provinces = Province::get();
            $citys = City::get();
		            $required = null;
        $representation = null;
		$callcenter = null;
	    $factor = null;
            return view('site.organization.single' , compact('organization','service' , 'provinces' , 'citys' , 'required' , 'representation' , 'callcenter' , 'factor'));
        return redirect()->back()->with([
            'payment' => 'danger',
        ]);
    }
}
