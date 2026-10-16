<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\Sms\ContractOtpException;
use App\Http\Controllers\Controller;
use App\Models\GhorfeOnlineList;
use App\Services\Sms\SmsContractOtpService;
use App\Services\SmsSettings\SmsCmsContentService;
use Illuminate\Http\JsonResponse;
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
 *
 * The signature is verified with a 6-digit OTP sent to the signer's own phone
 * (SmsContractOtpService), bound to the exact contract + rules text and signature name.
 */
class SmsContractSignController extends Controller
{
    public function __construct(
        protected SmsCmsContentService $cmsContent,
        protected SmsContractOtpService $otp
    ) {
    }

    /** The business being signed for (same rule on every action of this controller). */
    private function resolveBusiness(Request $request): GhorfeOnlineList
    {
        $business = ghorfe();
        if (!$business && (auth()->user()?->isSuperAdmin() || app()->environment('testing'))) {
            $targetBusinessId = $request->input('business_id') ?: $request->input('business');
            $business = $targetBusinessId ? GhorfeOnlineList::find($targetBusinessId) : null;
        }

        if (!$business) {
            abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');
        }

        return $business;
    }

    /** Persian / Arabic-Indic digits -> ASCII, so a code typed on a Persian keyboard still verifies. */
    private function asciiDigits(?string $value): string
    {
        return strtr((string) $value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    public function index(Request $request): View
    {
        $business = $this->resolveBusiness($request);
        $phone = $this->otp->maskPhone((string) preg_replace('/\D/', '', $this->asciiDigits(auth()->user()?->phone)));

        return view('Admin.sms.contract.sign', [
            'business' => $business,
            'contractText' => $this->cmsContent->contractText(),
            'regulatoryRulesText' => $this->cmsContent->regulatoryRulesText(),
            'isSigned' => (bool) $business->sms_contract_accepted,
            'otpPhoneMasked' => strlen($phone) >= 11 ? $phone : null,
            'otpResendSeconds' => (int) config('admin_auth.contract_otp.resend_cooldown_seconds'),
        ]);
    }

    /**
     * Send the signing OTP to the signer's OWN phone (never a number from the request).
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $business = $this->resolveBusiness($request);

        if ($business->sms_contract_accepted) {
            return response()->json(['status' => 'error', 'message' => 'این تعهدنامه قبلاً امضا شده است.'], 409);
        }

        $validated = $request->validate([
            'agree' => 'accepted',
            'signature_name' => 'required|string|min:3|max:191',
        ], [
            'agree.accepted' => 'ابتدا تعهدنامه را مطالعه و تایید کنید.',
            'signature_name.required' => 'ابتدا نام و نام خانوادگی خود را به‌عنوان امضا وارد کنید.',
            'signature_name.min' => 'نام امضاکننده بسیار کوتاه است.',
        ]);

        try {
            $result = $this->otp->send(
                auth()->user(),
                $business,
                $this->cmsContent->contractText(),
                $this->cmsContent->regulatoryRulesText(),
                $validated['signature_name']
            );
        } catch (ContractOtpException $e) {
            $status = match ($e->reason) {
                'cooldown', 'hourly_limit' => 429,
                'send_failed' => 503,
                default => 422,
            };

            return response()->json([
                'status' => 'error',
                'reason' => $e->reason,
                'message' => $e->getMessage(),
                'retry_after' => $e->retryAfter,
            ], $status);
        }

        return response()->json(['status' => 'success', 'message' => 'کد تایید به شماره ' . $result['masked_phone'] . ' ارسال شد.'] + $result);
    }

    public function accept(Request $request): RedirectResponse
    {
        $business = $this->resolveBusiness($request);

        if ($business->sms_contract_accepted) {
            return redirect()->back()->with('warning', 'این تعهدنامه قبلاً امضا و ثبت نهایی شده است و صرفاً قابل مشاهده می‌باشد.');
        }

        $request->merge(['otp' => $this->asciiDigits($request->input('otp'))]);

        $validated = $request->validate([
            'agree' => 'accepted',
            'signature_name' => 'required|string|max:191',
            'otp' => 'required|digits:6',
        ], [
            'agree.accepted' => 'برای ادامه باید تعهدنامه را مطالعه و تایید نمایید.',
            'signature_name.required' => 'ثبت نام و نام خانوادگی به‌عنوان امضای الکترونیکی الزامی است.',
            'otp.required' => 'کد تایید ۶ رقمی که به موبایل شما پیامک شده را وارد کنید.',
            'otp.digits' => 'کد تایید باید ۶ رقم باشد.',
        ]);

        $contractText = $this->cmsContent->contractText();
        $rulesText = $this->cmsContent->regulatoryRulesText();

        try {
            $verified = $this->otp->verify(auth()->user(), $business, $contractText, $rulesText, $validated['signature_name'], $validated['otp']);
        } catch (ContractOtpException $e) {
            return redirect()->back()->withInput($request->except('otp'))->withErrors(['otp' => $e->getMessage()]);
        }

        $business->update([
            'sms_contract_accepted' => true,
            'sms_contract_accepted_at' => now(),
            'sms_contract_signature_name' => $validated['signature_name'],
            'sms_contract_accepted_by' => auth()->id(),
            // Evidence that this signature was verified: the phone the code went to, when, and the exact text.
            'sms_contract_signed_phone' => $verified->phone,
            'sms_contract_text_hash' => $verified->contract_hash,
            'sms_contract_otp_verified_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'تعهدنامه استفاده از سرویس پیامک با موفقیت امضا و ثبت نهایی شد.');
    }
}
