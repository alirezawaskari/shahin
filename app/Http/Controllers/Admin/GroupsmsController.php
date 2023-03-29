<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Models\Groupsms;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use function PHPSTORM_META\elementType;

class GroupsmsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groupsms = Groupsms::latest()->get();
        return view('Admin.groupsms.all', compact('groupsms'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $services = Service::get();
        return view('Admin.groupsms.create', compact('services'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'message' => 'required',
            'service_id' => 'required',
            'send_at' => 'required',
            'flag' => 'required',
        ]);

        $inputs = $request->all();
        unset($inputs['service_id']);
        unset($inputs['date']);
        $groupsms = Groupsms::create(array_merge($inputs, ['send_at' => Carbon::createFromTimestamp($inputs['send_at'] / 1000)->toDateString(), 'type' => 'groupsService' , 'flag' => true]));

        if ($request->input('service_id')[0] != 'all') {
            $groupsms->services()->sync($request->input("service_id"));
        }

        return redirect(route('groupsms.index'))->with([
            'create' => 'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Groupsms $groupsms
     * @return \Illuminate\Http\Response
     */
    public function show(Groupsms $groupsm)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Groupsms $groupsms
     * @return \Illuminate\Http\Response
     */
    public function edit(Groupsms $groupsm)
    {
        $services = Service::get();
        return view('Admin.groupsms.edit', compact('groupsm', 'services'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Groupsms $groupsms
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Groupsms $groupsm)
    {
        $this->validate($request, [
            'title' => 'required',
            'message' => 'required',
            'service_id' => 'required',
        ]);

        $inputs = $request->all();
        unset($inputs['service_id']);
        unset($inputs['date']);

        if (strpos($request->input('send_at'), '-')) {
            $groupsm->update($inputs);
        } else {
            $groupsm->update(array_merge($inputs, ['send_at' => Carbon::createFromTimestamp($inputs['send_at'] / 1000)->toDateString()]));
        }

        if ($request->input('service_id')[0] == 'all') {
            $groupsm->services()->detach();
        } else {
            $groupsm->services()->sync($request->input("service_id"));
        }

        return redirect(route('groupsms.index'))->with([
            'update' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Groupsms $groupsms
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Groupsms $groupsm)
    {
        $groupsm->delete();

        return redirect(route('groupsms.index'))->with([
            'delete' => 'success'
        ]);
    }


    public function single()
    {
        return view('Admin.groupsms.single');
    }


    public function singleEdit(Groupsms $groupsms)
    {
        return view('Admin.groupsms.singleUpdate', compact('groupsms'));
    }

    public function singleStore(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'message' => 'required',
            'phones' => 'required',
        ]);

        $phones = $request->input('phones');
        $message = $request->input('message');
        Helper::sms($phones , $message);
        //save
        $inputs = $request->all();
        $groupsms = Groupsms::create(array_merge($inputs, ['type' => 'single', 'status' => 2 , 'flag' => true]));

        return redirect(route('groupsms.index'))->with([
            'create' => 'success'
        ]);

    }

    public function sendConsent(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'message' => 'required',
            'phones' => 'required',
        ]);

        $phones = $request->input('phones');
        $message = $request->input('message');
        Helper::sms($phones , $message);
        //save
        $inputs = $request->all();
        $groupsms = Groupsms::create(array_merge($inputs, ['type' => 'single', 'status' => 2 , 'flag' => true]));

        return redirect(route('groupsms.index'))->with([
            'create' => 'success'
        ]);

    }

    public function groupsmsPhone(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'message' => 'required',
            'phone_list_id' => 'required',
            'send_at' => 'required',
        ]);

        $inputs = $request->all();
        unset($inputs['date2']);
        unset($inputs['phone_list_id']);

        if ($request->input('phone_list_id') == 'default') {
            Groupsms::create(array_merge($inputs, ['send_at' => Carbon::createFromTimestamp($inputs['send_at'] / 1000)->toDateString(), 'type' => 'groupsPhone']));
        } else {
            Groupsms::create(array_merge($inputs, ['send_at' => Carbon::createFromTimestamp($inputs['send_at'] / 1000)->toDateString(), 'phone_list_id' => $request->input('phone_list_id'), 'type' => 'groupsPhone']));
        }

        return redirect(route('groupsms.index'))->with([
            'create' => 'success'
        ]);

    }

    public function groupsmsPhoneUpdate(Request $request, Groupsms $groupsms)
    {
        $this->validate($request, [
            'title' => 'required',
            'message' => 'required',
            'phone_list_id' => 'required',
            'send_at' => 'required',
        ]);

        $inputs = $request->all();
        unset($inputs['date']);
        unset($inputs['phone_list_id']);


        if (strpos($request->input('send_at'), '-')) {
            if ($request->input('phone_list_id') == 'default') {
                $groupsms->update(array_merge(['phone_list_id' => null]));
            } else {
                $groupsms->update(array_merge($inputs, ['phone_list_id' => $request->input('phone_list_id')]));
            }
        } else {
            if ($request->input('phone_list_id') == 'default') {
                $groupsms->update(array_merge($inputs, ['send_at' => Carbon::createFromTimestamp($inputs['send_at'] / 1000)->toDateString() ,'phone_list_id' => null]));
            } else {
                $groupsms->update(array_merge($inputs, ['send_at' => Carbon::createFromTimestamp($inputs['send_at'] / 1000)->toDateString(), 'phone_list_id' => $request->input('phone_list_id')]));
            }
        }

        return redirect(route('groupsms.index'))->with([
            'update' => 'success'
        ]);
    }
}
