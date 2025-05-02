<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\SmsTemplateCollection;
use App\Models\SmsTemplate;
use Illuminate\Support\Facades\Auth;

class SmsTemplateController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        if (auth()->user()->hasPermission('sms-template'))
        {
            return view( 'Admin.smsTemplate.all');
        }
        return abort(401);
    }
    /**
     * @return \App\Http\Resources\SmsTemplateCollection
     */
    public function filter()
    {
        $sms_templates = SmsTemplate::filter()->latest();
        return new SmsTemplateCollection($sms_templates->paginate(20));
    }
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        if (auth()->user()->hasPermission('sms-template-create'))
        {
            return view('Admin.smsTemplate.create');
        }
        return abort(401);
    }
    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        if (auth()->user()->hasPermission('sms-template-create'))
        {
            $this->validate(request(), [
                'name' => 'required|string',
                'status' => 'required',
                'content' => 'required',
            ]);
            $input = $request->all();
            $input['user_id'] = Auth::id();
            SmsTemplate::create($input);
            return redirect()->route('smsTemplate.index')->with([
                'create' => 'success'
            ]);
        }
        return redirect()->route('smsTemplate.index')->with([
            'failByMessage' => 'امکان ویرایش برای شما وجود ندارد.',
        ]);
    }

    /**
     * @param SmsTemplate $smsTemplate
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(SmsTemplate $smsTemplate)
    {
        if (auth()->user()->hasPermission('sms-template-edit'))
        {
            return view('Admin.smsTemplate.edit' , compact(['smsTemplate']));
        }
        return abort(401);
    }
    /**
     * @param SmsTemplate $smsTemplate
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(SmsTemplate $smsTemplate , Request $request)
    {
        if (auth()->user()->hasPermission('sms-template-edit'))
        {
            $smsTemplate->update($request->all());
            return redirect()->back()->with([
                'update' => 'success',
            ]);
        }
        return redirect()->route('smsTemplate.index')->with([
            'failByMessage' => 'امکان ویرایش برای شما وجود ندارد.',
        ]);
    }
}

