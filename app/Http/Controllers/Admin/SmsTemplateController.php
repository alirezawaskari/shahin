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
     * Check if current user can view SMS templates.
     */
    protected function canViewTemplates(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->hasRole('ghorfeAdmin')
            || $user->hasPermission('sms-template')
            || $user->hasPermission('sms-settings');
    }

    /**
     * Check if current user can create/edit SMS templates.
     */
    protected function canManageTemplates(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->hasRole('ghorfeAdmin')
            || $user->hasPermission('sms-template-create')
            || $user->hasPermission('sms-template-edit')
            || $user->hasPermission('sms-template')
            || $user->hasPermission('sms-settings');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        return redirect()->route('admin.sms.settings.templates.index');
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        return redirect()->route('admin.sms.settings.templates.create');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        if ($this->canManageTemplates())
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
        if ($this->canManageTemplates())
        {
            return view('Admin.smsTemplate.edit' , compact(['smsTemplate']));
        }
        return abort(403, 'امکان ویرایش قالب پیامک برای شما وجود ندارد.');
    }

    /**
     * @param SmsTemplate $smsTemplate
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(SmsTemplate $smsTemplate , Request $request)
    {
        if ($this->canManageTemplates())
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
