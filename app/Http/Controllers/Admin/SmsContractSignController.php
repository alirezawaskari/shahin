<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SmsSettings\SmsCmsContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * One-time usage-contract sign page for the SMS module (task.md §2-6):
 * checkbox + a recorded signature, gating the whole module via
 * EnsureSmsContractSigned until accepted. The contract text and the
 * telecom-regulatory rules text are both admin-editable (see
 * SmsCmsSettingsController) and rendered here so they stay reachable
 * after signing too, as required.
 */
class SmsContractSignController extends Controller
{
    public function __construct(
        protected SmsCmsContentService $cmsContent
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $business = ghorfe() ?? abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');

        if ($business->sms_contract_accepted) {
            return redirect()->route('admin.sms.settings.index');
        }

        return view('Admin.sms.contract.sign', [
            'business' => $business,
            'contractText' => $this->cmsContent->contractText(),
            'regulatoryRulesText' => $this->cmsContent->regulatoryRulesText(),
        ]);
    }

    public function accept(Request $request): RedirectResponse
    {
        $business = ghorfe() ?? abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');

        $validated = $request->validate([
            'agree' => 'accepted',
            'signature_name' => 'required|string|max:191',
        ], [
            'agree.accepted' => 'برای ادامه باید تعهدنامه را مطالعه و تایید نمایید.',
            'signature_name.required' => 'ثبت نام و نام خانوادگی به‌عنوان امضای الکترونیکی الزامی است.',
        ]);

        $business->update([
            'sms_contract_accepted' => true,
            'sms_contract_accepted_at' => now(),
            'sms_contract_signature_name' => $validated['signature_name'],
            'sms_contract_accepted_by' => auth()->id(),
        ]);

        return redirect()->intended(route('admin.sms.settings.index'))
            ->with('success', 'تعهدنامه استفاده از سرویس پیامک با موفقیت تایید شد.');
    }
}
