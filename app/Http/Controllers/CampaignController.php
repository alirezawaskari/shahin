<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Callcenter;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\City;
use App\Models\History;
use App\Models\Province;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\User;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function campaignRegister($slug)
    {
        $campaign = Campaign::where('slug',$slug)->firstOrFail();
        $provinces = Province::get();
        $citys = City::get();
        $categories = Category::filter()->orderBy('created_at', 'desc')->get();
        $services = Service::filter()->whereStatus(true)->where('isShow', true)->orderBy('created_at', 'desc')->get();
        $service_items = ServiceItem::get();
        return view('site.campaign.campaign', compact('service_items', 'provinces', 'citys', 'services', 'categories','campaign'));
    }
    public function campaignRegisterInsert(Request $request,$campaign_id)
    {
        $request['city'] = $request->city_id;
        $request['province'] = $request->province_id;
        User::createUser($request);
        $user = User::where('phone',$request->phone)->first();
        $id = Helper::getCallcenter($user->province_id,null);
        $callcenter_roles = Role::select('id')->where('card_id' , $request->card_id)->get();
        if($request->description !== null)
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
            $Campaign = Campaign::findOrFail($campaign_id);
            $Campaign->usage = $Campaign->usage - 1;
            $Campaign->update();
            $description =  "برای پوییش : $Campaign->name";
            History::create($task , $task->user_id , null , $task->status , $description);
        }
        return redirect('home')->with([
            'create' => 'success'
        ]);

    }

    public function getTypeCategory(Request $request)
    {
        if ($request->ajax()) {
            $category = Category::where('parent_id',$request->input('id'))->get();
            if (empty(!$category))
                return response()->json(['count' => 'available', 'service' => $category, 'length' => count($category)]);
            else
                return response()->json(['count' => 'notavailable']);
        }
        abort(404);
    }
    public function getServiceCategory(Request $request)
    {
        if ($request->ajax()) {
            if($request->input('id') === "more")
                return response()->json(['count' => 'more']);
            $category = Category::find($request->input('id'));
            $services = Service::filter()->where('category_id', $category->id)->get();
            if (empty(!$services))
                return response()->json(['count' => 'available', 'service' => $services, 'length' => count($services)]);
            else
                return response()->json(['count' => 'notavailable']);
        }
        abort(404);
    }
}
