<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Exceptions\ContractMediaException;
use App\Services\V1\Admin\ContractMediaService;
use App\Models\Media;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractFolder;
use App\Models\ContractUsage;
use App\Models\GhorfeOnlineList;
use App\Services\SmsSettings\SmsContractFilesService;
use App\Services\SmsSettings\SmsContractService;
use App\Services\SmsSettings\SmsPanelAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SmsContractController extends Controller
{
    public function __construct(
        protected SmsContractService $contractService,
        protected \App\Services\SmsSettings\SmsCmsContentService $cmsContent,
        protected SmsContractFilesService $files
    ) {
    }

    /**
     * Display SMS contracts & legal rules view.
     * For SuperAdmin: Management table (OTP & Adv tabs) + Templates/General Contracts modal + Assign modal + Legal/Regulatory Rules modal.
     * For Business Users: Read-only / Sign-ready view of their OTP & Advertising contracts, usage agreement, and regulatory rules.
     */
    public function index(Request $request): View
    {
        $business = ghorfe();

        if (!$business && (auth()->user()?->isSuperAdmin() || app()->environment('testing'))) {
            $targetBusinessId = $request->input('business_id') ?: $request->input('business');
            $business = $targetBusinessId ? GhorfeOnlineList::find($targetBusinessId) : null;
        }

        $hasContractCols = \Illuminate\Support\Facades\Schema::hasColumn('ghorfe_online_lists', 'sms_contract_accepted');
        $all_businesses = auth()->user()?->isSuperAdmin()
            ? ($hasContractCols
                ? GhorfeOnlineList::select('id', 'title', 'sms_contract_accepted', 'sms_contract_accepted_at', 'sms_contract_signature_name')->get()
                : GhorfeOnlineList::select('id', 'title')->get())
            : collect();

        $otpContractUsage = $this->contractService->getOtpContract();
        $advContractUsage = $this->contractService->getGlobalAdvertisingContract();

        $advertisingContracts = null;
        $businessAdvUsage = null;
        $businessOtpUsage = null;

        if ($business) {
            $businessAdvUsage = $this->contractService->getBusinessAdvertisingContract($business);
            $businessOtpUsage = $this->contractService->getBusinessOtpContract($business);
        } else {
            $filters = $request->only(['search', 'per_page']);
            $advertisingContracts = $this->contractService->getAdvertisingContractsList($filters);
        }

        $folders = ContractFolder::options();
        $bankContracts = Contract::latest('id')->take(50)->get();

        $contractText = $this->cmsContent->contractText();
        $regulatoryRulesText = $this->cmsContent->regulatoryRulesText();
        $isContractSigned = (bool) ($business?->sms_contract_accepted);
        $contractSigner = $business?->sms_contract_signature_name;
        $contractSignedAt = $business?->sms_contract_accepted_at;

        // Masked phone for OTP signing if business user hasn't signed yet
        $rawPhone = (string) preg_replace('/\D/', '', strtr((string) auth()->user()?->phone, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]));
        $otpPhoneMasked = strlen($rawPhone) >= 11
            ? substr($rawPhone, 0, 4) . '***' . substr($rawPhone, -4)
            : null;
        $otpResendSeconds = (int) config('admin_auth.contract_otp.resend_cooldown_seconds', 60);

        // Approve / reject of client files: super admin on the staff list only, never from a business's own view.
        $canReviewContracts = (bool) (auth()->user()?->isSuperAdmin() && !$business);

        return view('Admin.sms.settings.contracts.index', compact(
            'canReviewContracts',
            'otpContractUsage',
            'advContractUsage',
            'businessAdvUsage',
            'businessOtpUsage',
            'advertisingContracts',
            'business',
            'all_businesses',
            'folders',
            'bankContracts',
            'contractText',
            'regulatoryRulesText',
            'isContractSigned',
            'contractSigner',
            'contractSignedAt',
            'otpPhoneMasked',
            'otpResendSeconds'
        ));
    }

    /**
     * Store or attach an SMS contract (OTP or Advertising — Global Template or Per-Business).
     */
    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'تنظیم و تغییر قراردادها منحصراً در اختیار مدیریت ارشد سامانه است.');
        }

        $validated = $request->validate([
            'service_type' => 'required|in:otp,advertising',
            'mode' => 'required|in:new,existing',
            'is_global_template' => 'nullable|boolean',
            'is_global_advertising' => 'nullable|boolean',
            'save_as_template' => 'nullable|boolean',
            'template_only' => 'nullable|boolean',
            'business_id' => 'nullable|exists:ghorfe_online_lists,id',
            'contract_id' => 'required_if:mode,existing|nullable|exists:contracts,id',
            'title' => 'required_if:mode,new|nullable|string|max:191',
            'type' => 'nullable|integer',
            'contract_folder_id' => 'nullable|exists:contract_folders,id',
            'contract_number' => 'nullable|string|max:100',
            'contract_date' => 'nullable|date',
            'content' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'file_path' => 'nullable|string',
            'staff_attachments' => 'nullable|array|max:5',
            'staff_attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ], [
            'service_type.required' => 'نوع سرویس قرارداد را مشخص کنید.',
            'mode.required' => 'نحوه انتساب قرارداد را مشخص کنید.',
            'contract_id.required_if' => 'الگوی قرارداد یا قرارداد مورد نظر را انتخاب کنید.',
            'title.required_if' => 'عنوان قرارداد یا الگوی قرارداد را وارد کنید.',
            'staff_attachments.max' => 'حداکثر ۵ فایل پیوست مجاز است.',
            'staff_attachments.*.mimes' => 'فرمت فایل‌های پیوست باید pdf, doc, docx, jpg, jpeg یا png باشد.',
            'staff_attachments.*.max' => 'حجم هر فایل پیوست نباید بیش از ۱۰ مگابایت باشد.',
            'staff_attachments.*.uploaded' => ContractMediaService::uploadFailedMessage(),
            'file.uploaded' => ContractMediaService::uploadFailedMessage(),
        ]);

        $serviceType = $validated['service_type'];
        $business = ghorfe();

        if (!$business && !empty($validated['business_id'])) {
            $business = GhorfeOnlineList::find($validated['business_id']);
        }

        try {
            $this->contractService->saveContract(
                $serviceType,
                $validated,
                $business,
                $request->file('file'),
                $request->input('file_path'),
                $request->file('staff_attachments')
            );
        } catch (ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        }

        $label = !empty($validated['template_only'])
            ? 'الگوی قرارداد جدید'
            : (($serviceType === 'otp') ? 'قرارداد پیامک خدماتی (OTP)' : 'قرارداد پیامک تبلیغاتی');

        return redirect()->route('admin.sms.settings.contracts.index')
            ->with('success', "{$label} با موفقیت ثبت گردید.");
    }

    /**
     * Update contract usage details (e.g. contract number or date).
     */
    public function update(Request $request, ContractUsage $contractUsage): RedirectResponse
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'ویرایش اطلاعات قرارداد منحصراً در اختیار مدیریت ارشد سامانه است.');
        }

        $validated = $request->validate([
            'contract_number' => 'nullable|string|max:100',
            'contract_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $contractUsage->update($validated);

        return redirect()->back()->with('success', 'اطلاعات انتساب قرارداد با موفقیت به‌روزرسانی شد.');
    }

    /**
     * Detach contract usage.
     */
    public function destroy(ContractUsage $contractUsage): RedirectResponse
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'لغو قرارداد منحصراً در اختیار مدیریت ارشد سامانه است.');
        }

        $this->contractService->detachUsage($contractUsage);

        return redirect()->route('admin.sms.settings.contracts.index')
            ->with('success', 'قرارداد با موفقیت از محل استفاده لغو گردید.');
    }

    /**
     * Staff approve of one client file of a business.
     */
    public function approveFile(Request $request, string $media): RedirectResponse
    {
        $file = $this->clientFile($media);

        $this->files->approve($file, auth()->id());

        return redirect()->back()->with(['update' => 'success']);
    }

    /**
     * Staff rejection of one client file of a business. The reason is required.
     */
    public function rejectFile(Request $request, string $media): RedirectResponse
    {
        $file = $this->clientFile($media);

        $request->validate(
            ['reason' => 'required|string|max:' . SmsContractFilesService::REASON_MAX_LENGTH],
            [
                'reason.required' => 'دلیل رد فایل الزامی است.',
                'reason.max' => 'دلیل رد فایل نباید بیش از ۵۰۰ نویسه باشد.',
            ]
        );

        try {
            $this->files->reject($file, auth()->id(), (string) $request->input('reason'));
        } catch (ContractMediaException $e) {
            return redirect()->back()->withErrors(['reason' => $e->getMessage()]);
        }

        // TODO (CTO decision, not live): notify the client by SMS that this file was rejected and
        // can be re-uploaded from the sign page. The SMS-notifying path is deliberately not wired yet.

        return redirect()->back()->with(['update' => 'success']);
    }

    /**
     * Only a client file of a business can be reviewed here, and only by a super admin. Business
     * admins also pass the route's checkAdmin, so the route group alone would let them review any file.
     */
    private function clientFile(string $mediaId): Media
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $media = Media::query()->find($mediaId);

        abort_unless(
            $media && $media->model_type === GhorfeOnlineList::class && $media->collection_name === SmsContractFilesService::COLLECTION,
            404
        );

        return $media;
    }

    /**
     * Staff switch for one business's SMS panel. Refused for everyone but the super admin, the same guard as
     * the contract management above. A reason is required to disable; nothing is sent or deleted by it.
     * The business is taken by id: its route key is the slug, and the panel URLs use the id.
     */
    public function disablePanel(Request $request, int $business, string $type): RedirectResponse
    {
        $this->assertPanelSwitchAllowed($type);
        $business = GhorfeOnlineList::query()->findOrFail($business);

        $validated = $request->validate([
            'reason' => 'required|string|min:3|max:500',
        ], [
            'reason.required' => 'دلیل غیرفعال‌سازی پنل را وارد کنید.',
            'reason.min' => 'دلیل غیرفعال‌سازی پنل باید حداقل ۳ نویسه باشد.',
            'reason.max' => 'دلیل غیرفعال‌سازی پنل نباید بیش از ۵۰۰ نویسه باشد.',
        ]);

        app(SmsPanelAccess::class)->disable($business, $type, $validated['reason'], auth()->user());

        return redirect()->back()->with('success', 'پنل ' . SmsPanelAccess::label($type) . ' «' . $business->title . '» غیرفعال شد.');
    }

    /** Enables a business's SMS panel again. Only the super admin, as above. */
    public function enablePanel(Request $request, int $business, string $type): RedirectResponse
    {
        $this->assertPanelSwitchAllowed($type);
        $business = GhorfeOnlineList::query()->findOrFail($business);

        app(SmsPanelAccess::class)->enable($business, $type, auth()->user());

        return redirect()->back()->with('success', 'پنل ' . SmsPanelAccess::label($type) . ' «' . $business->title . '» فعال شد.');
    }

    private function assertPanelSwitchAllowed(string $type): void
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'غیرفعال‌سازی پنل پیامک کسب‌وکارها منحصراً در اختیار مدیریت ارشد سامانه است.');
        }

        if (!in_array($type, SmsPanelAccess::TYPES, true)) {
            abort(404);
        }
    }
}
