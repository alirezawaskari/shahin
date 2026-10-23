<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ContractMediaException;
use App\Exceptions\Sms\ContractOtpException;
use App\Http\Controllers\Controller;
use App\Models\GhorfeOnlineList;
use App\Services\Sms\SmsContractOtpService;
use App\Services\SmsSettings\SmsCmsContentService;
use App\Services\SmsSettings\SmsContractFilesService;
use App\Services\SmsSettings\SmsContractService;
use App\Services\SmsSettings\SmsSignatureRecord;
use App\Services\V1\Admin\ContractMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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
 *
 * The signing itself is recorded the central way (a history line on the business and a
 * snapshot of the signed text, see SmsSignatureRecord). Contracts are the central ones.
 */
class SmsContractSignController extends Controller
{
    public function __construct(
        protected SmsCmsContentService $cmsContent,
        protected SmsContractOtpService $otp,
        protected SmsContractService $contracts,
        protected SmsContractFilesService $files
    ) {
    }

    /** The business being signed for (same rule on every action of this controller). */
    private function resolveBusiness(Request $request): GhorfeOnlineList
    {
        $business = ghorfe();
        if (!$business && (auth()->user()?->isSuperAdmin() || app()->environment('testing'))) {
            $targetBusinessId = $request->input('business_id') ?: $request->input('business');
            $business = $targetBusinessId ? GhorfeOnlineList::find($targetBusinessId) : GhorfeOnlineList::first();
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

        $otpContractUsage = null;
        $businessAdvUsage = null;
        $staffFiles = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('contract_usages')) {
            $otpContractUsage = $this->contracts->getBusinessOtpContract($business) ?: $this->contracts->getOtpContract();
            $businessAdvUsage = $this->contracts->getBusinessAdvertisingContract($business) ?: $this->contracts->getGlobalAdvertisingContract();
            $staffFiles = $this->contracts->staffFilesFor($otpContractUsage)
                ->merge($this->contracts->staffFilesFor($businessAdvUsage))
                ->unique('id')
                ->values();
        }

        // The client's own files, and the ones staff rejected (the only ones that can be re-uploaded).
        $clientFiles = $this->files->clientFiles($business);

        return view('Admin.sms.contract.sign', [
            'business' => $business,
            'contractText' => $this->cmsContent->contractText(),
            'regulatoryRulesText' => $this->cmsContent->regulatoryRulesText(),
            'otpContractUsage' => $otpContractUsage,
            'businessAdvUsage' => $businessAdvUsage,
            'isSigned' => (bool) $business->sms_contract_accepted,
            'otpPhoneMasked' => strlen($phone) >= 11 ? $phone : null,
            'otpResendSeconds' => (int) config('admin_auth.contract_otp.resend_cooldown_seconds'),
            'staffFiles' => $staffFiles,
            'clientFiles' => $clientFiles,
            'contractMedia' => $this->files,
            'rejectedFiles' => $clientFiles->filter(fn ($m) => $this->files->statusOf($m) === SmsContractFilesService::STATUS_REJECTED)->values(),
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
            'client_attachments' => 'nullable|array|max:5',
            'client_attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ], [
            'agree.accepted' => 'برای ادامه باید تعهدنامه را مطالعه و تایید نمایید.',
            'signature_name.required' => 'ثبت نام و نام خانوادگی به‌عنوان امضای الکترونیکی الزامی است.',
            'otp.required' => 'کد تایید ۶ رقمی که به موبایل شما پیامک شده را وارد کنید.',
            'otp.digits' => 'کد تایید باید ۶ رقم باشد.',
            'client_attachments.max' => 'حداکثر ۵ فایل پیوست مجاز است.',
            'client_attachments.*.mimes' => 'فرمت فایل‌های پیوست باید pdf, doc, docx, jpg, jpeg یا png باشد.',
            'client_attachments.*.max' => 'حجم هر فایل پیوست نباید بیش از ۱۰ مگابایت باشد.',
        ]);

        $contractText = $this->cmsContent->contractText();
        $rulesText = $this->cmsContent->regulatoryRulesText();

        try {
            $verified = $this->otp->verify(auth()->user(), $business, $contractText, $rulesText, $validated['signature_name'], $validated['otp']);
        } catch (ContractOtpException $e) {
            return redirect()->back()->withInput($request->except('otp'))->withErrors(['otp' => $e->getMessage()]);
        }

        try {
            DB::transaction(function () use ($request, $business, $validated, $verified, $contractText, $rulesText) {
                $clientFiles = array_values(array_filter((array) $request->file('client_attachments', []), fn ($f) => $f instanceof UploadedFile));
                $this->files->storeClientFiles($business, $clientFiles);

                // The signing record: one history line on the business + the exact text that was signed.
                SmsSignatureRecord::record(
                    $business,
                    (int) auth()->id(),
                    $validated['signature_name'],
                    $verified->phone,
                    now()->format(SmsSignatureRecord::TIME_FORMAT),
                    $verified->contract_hash,
                    $this->signedText($business, $contractText, $rulesText)
                );
            });
        } catch (ContractMediaException $e) {
            return redirect()->back()->withInput($request->except('otp'))->withErrors(['client_attachments' => $e->getMessage()]);
        }

        return redirect()->back()
            ->with('success', 'تعهدنامه استفاده از سرویس پیامک با موفقیت امضا و ثبت نهایی شد.');
    }

    /**
     * The client re-uploads ONLY the files staff rejected. Rejection does not change the
     * signature, and this does not reopen the signing step. The form posts replace[<media id>] => file.
     */
    public function reupload(Request $request): RedirectResponse
    {
        $business = $this->resolveBusiness($request);

        if (!$business->sms_contract_accepted) {
            return redirect()->route('admin.sms.contract.sign')
                ->with('warning', 'ابتدا تعهدنامه را امضا کنید؛ سپس می‌توانید فایل‌های ردشده را دوباره بارگذاری کنید.');
        }

        $request->validate([
            'replace' => 'required|array|min:1|max:' . SmsContractFilesService::CLIENT_LIMIT,
            'replace.*' => ContractMediaService::fileRules(),
        ], [
            'replace.required' => 'فایلی برای بارگذاری مجدد انتخاب نشده است.',
            'replace.*.mimes' => 'فرمت فایل‌ها باید pdf, doc, docx, jpg, jpeg یا png باشد.',
            'replace.*.max' => 'حجم هر فایل نباید بیش از ۱۰ مگابایت باشد.',
        ]);

        try {
            $this->files->reuploadRejected($business, $request->file('replace', []));
        } catch (ContractMediaException $e) {
            return redirect()->back()->withErrors(['replace' => $e->getMessage()]);
        }

        return redirect()->back()
            ->with('success', 'فایل‌های ردشده با موفقیت دوباره بارگذاری شدند و در انتظار بررسی مدیریت هستند.');
    }

    /** The text that is signed: the telecom rules, the contract text, and the contracts shown to the business. */
    private function signedText(GhorfeOnlineList $business, string $contractText, string $rulesText): string
    {
        $parts = [$contractText, $rulesText];

        foreach ([
            $this->contracts->getBusinessOtpContract($business) ?: $this->contracts->getOtpContract(),
            $this->contracts->getBusinessAdvertisingContract($business) ?: $this->contracts->getGlobalAdvertisingContract(),
        ] as $usage) {
            if ($usage?->contract?->content) {
                $parts[] = $usage->contract->content;
            }
        }

        return implode("\n<hr>\n", $parts);
    }
}
