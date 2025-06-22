<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Callcenter;
use App\Models\Category;
use App\Models\City;
use App\Models\Province;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterCampaignController extends Controller
{
    public function campaignRegister()
    {
        $provinces = Province::get();
        $citys = City::get();
        $categories = Category::filter()->orderBy('created_at', 'desc')->get();
        $services = Service::filter()->whereStatus(true)->where('isShow', true)->orderBy('created_at', 'desc')->get();
        $service_items = ServiceItem::get();
        return view('auth.register-campaign', compact('service_items', 'provinces', 'citys', 'services', 'categories'));
    }
    public function campaignRegisterInsert(Request $request)
    {
        $request['city'] = $request->city_id;
        $request['province'] = $request->province_id;
        User::createUser($request);
        $user = User::where('phone',$request->phone)->first();
        $id = Helper::getCallcenter($user->province_id,null);
        $callcenter_roles = Role::select('id')->where('card_id' , $request->card_id)->get();
        if($request->description)
            Callcenter::createTask($user,$request->description,$callcenter_roles);
        else{
            $task = Callcenter::create([
                'reg_id' => $user->id,
                'user_id' => $id,
                'type' => isset($agent) ? 3 : 0,
                'status' => '0'
            ]);
            if ($request->category_id) {
                $task->categories()->attach(array($request->category_id));
            }
            if ($request->service_id) {
                $task->services()->attach(array($request->service_id));
            }
            if ($request->service_item_id) {
                $task->services_item()->attach(array($request->service_item_id));
            }
        }
        return redirect('home')->with([
            'create' => 'success'
        ]);

    }

    public function getServiceCategory(Request $request)
    {
        if ($request->ajax()) {
            if($request->input('id') === "more")
                return response()->json(['count' => 'more']);
            $category = Category::find($request->input('id'));
            $services = $category?->services;
            if (empty(!$services))
                return response()->json(['count' => 'available', 'service' => $services, 'length' => count($services)]);
            else
                return response()->json(['count' => 'notavailable']);
        }
        abort(404);
    }
    function checkExistUser(Request $request)
    {
        if($request->ajax()){
            $user = User::where('phone', $request->phone_number)->first();
            if ($user) {
                return response([
                    'exist' => true ,
                    'data' => [
                        'name' => $user->name,
                        'family' => $user->family,
                        'phone' => $user->phone,
                        'gender' => $user->sex,
                        'province_id' => $user->province_id,
                        'city_id' => $user->city_id,
                    ]
                ]);
            }
            else
                return response(['exist' => false]);
        }
        else
            abort(404);
    }

}
