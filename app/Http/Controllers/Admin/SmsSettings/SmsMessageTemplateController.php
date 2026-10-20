<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Enums\SmsOtpCategory;
use App\Http\Controllers\Controller;
use App\Models\GhorfeOnlineList;
use App\Models\Setting;
use App\Models\SmsTemplate;
use App\Services\Sms\SmsAccessTierService;
use App\Services\SmsSettings\SmsMessageGroupService;
use App\Services\SmsSettings\SmsMessageTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SmsMessageTemplateController extends Controller
{
    public function __construct(
        protected SmsMessageTemplateService $templateService,
        protected SmsMessageGroupService $groupService
    ) {
    }

    /**
     * Resolve the active business for scoping.
     */
    protected function resolveBusiness(Request $request): ?GhorfeOnlineList
    {
        $business = ghorfe();
        if (!$business && (auth()->user()?->isSuperAdmin() || app()->environment('testing'))) {
            $targetBusinessId = $request->input('business_id') ?: $request->input('business');
            $business = $targetBusinessId ? GhorfeOnlineList::find($targetBusinessId) : null;
        }

        return $business;
    }

    /**
     * OTP templates are staff-only: same audience as OTP sending (Titanium / superadmin).
     */
    protected function canManageOtpTemplates(): bool
    {
        return app(SmsAccessTierService::class)->canUseOtp(auth()->user());
    }

    /**
     * Non-staff can neither see nor touch an existing OTP template.
     */
    protected function guardOtpTemplate(SmsTemplate $template): void
    {
        if ($template->service_type === 'otp' && !$this->canManageOtpTemplates()) {
            abort(403, 'مدیریت الگوهای OTP فقط برای مدیران سیستم مجاز است.');
        }
    }

    /**
     * Allowed service types for the submitted form.
     */
    protected function serviceTypeRule(): string
    {
        return $this->canManageOtpTemplates() ? 'required|in:otp,normal,advertising' : 'required|in:normal,advertising';
    }

    /**
     * Display listing of templates for the current business.
     */
    public function index(Request $request): View
    {
        $business = $this->resolveBusiness($request);
        $all_businesses = auth()->user()?->isSuperAdmin() ? GhorfeOnlineList::select('id', 'title')->get() : collect();

        $filters = $request->only(['service_type', 'status', 'group_id', 'search', 'per_page']);
        $templates = $this->templateService->list($filters, $business?->id, $this->canManageOtpTemplates());
        $groups = $this->groupService->tree($business?->id);

        return view('Admin.sms.settings.templates.index', compact('templates', 'groups', 'filters', 'business', 'all_businesses'));
    }

    /**
     * Show form for creating a new template for the current business.
     */
    public function create(Request $request): View
    {
        $business = $this->resolveBusiness($request);
        $groupOptions = $this->groupService->options($business?->id);
        $groups = $this->groupService->tree($business?->id);

        $otpCategories = $this->canManageOtpTemplates() ? SmsOtpCategory::options() : [];

        $promotionalGroupIds = $this->groupService->promotionalIds($business?->id);

        return view('Admin.sms.settings.templates.create', compact('groupOptions', 'groups', 'business', 'otpCategories', 'promotionalGroupIds'));
    }

    /**
     * Store a newly created template for the current business.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = $this->resolveBusiness($request);

        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'content' => 'required|string|max:1000',
            'sms_message_group_id' => 'nullable',
            'service_type' => $this->serviceTypeRule(),
            'otp_category' => ['nullable', 'required_if:service_type,otp', Rule::in(SmsOtpCategory::values())],
            'status' => 'required|in:active,inactive',
        ], [
            'otp_category.required_if' => 'برای الگوی OTP انتخاب دسته‌بندی سیستمی الزامی است.',
            'otp_category.in' => 'دسته‌بندی OTP انتخاب‌شده معتبر نیست.',
            'title.required' => 'عنوان الگو الزامی است.',
            'content.required' => 'متن الگو الزامی است.',
            'service_type.required' => 'نوع سرویس الگو را مشخص کنید.',
            'service_type.in' => 'نوع سرویس انتخاب‌شده برای شما مجاز نیست.',
            'status.required' => 'وضعیت الگو را مشخص کنید.',
        ]);

        $this->assertPromotionalGroup($validated);

        // Security: verify chosen group belongs to the same business
        if (!empty($validated['sms_message_group_id']) && $business) {
            $group = Setting::find($validated['sms_message_group_id']);
            if ($group && $group->settingable_id && $group->settingable_id !== $business->id && !auth()->user()?->isSuperAdmin()) {
                abort(403, 'گروه انتخابی متعلق به کسب‌وکار شما نیست.');
            }
        }

        $template = $this->templateService->create($validated, Auth::id(), $business?->id);

        $redirectParams = $business && auth()->user()?->isSuperAdmin() ? ['business_id' => $business->id] : [];

        return redirect()->route('admin.sms.settings.templates.index', $redirectParams)
            ->with('success', "الگوی پیامک «{$template->title}» با موفقیت افزوده شد.");
    }

    /**
     * Non-OTP templates (promotional / normal) only support the promotional subject groups;
     * OTP templates keep the full tree (they use fixed OTP categories on top).
     */
    private function assertPromotionalGroup(array $validated): void
    {
        if (($validated['service_type'] ?? null) !== 'otp'
            && !Setting::isPromotionalId($validated['sms_message_group_id'] ?? null)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'sms_message_group_id' => 'دسته‌بندی موضوعی انتخاب‌شده برای الگوی تبلیغاتی مجاز نیست (فقط مناسبتی/تبریک، فروش و پشتیبانی).',
            ]);
        }
    }

    public function edit(Request $request, SmsTemplate $template): View
    {
        $business = $this->resolveBusiness($request);
        $this->guardOtpTemplate($template);

        // Security: customer cannot edit another customer's template
        if ($template->ghorfe_online_list_id && $business && $template->ghorfe_online_list_id !== $business->id && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'شما اجازه دسترسی به این الگوی پیامک را ندارید.');
        }

        $targetBizId = $template->ghorfe_online_list_id ?: $business?->id;
        $groupOptions = $this->groupService->options($targetBizId);
        $groups = $this->groupService->tree($targetBizId);

        $otpCategories = $this->canManageOtpTemplates() ? SmsOtpCategory::options() : [];

        $promotionalGroupIds = $this->groupService->promotionalIds($targetBizId);

        return view('Admin.sms.settings.templates.edit', compact('template', 'groupOptions', 'groups', 'business', 'otpCategories', 'promotionalGroupIds'));
    }

    /**
     * Update an existing template.
     */
    public function update(Request $request, SmsTemplate $template): RedirectResponse
    {
        $business = $this->resolveBusiness($request);
        $this->guardOtpTemplate($template);

        // Security: customer cannot update another customer's template
        if ($template->ghorfe_online_list_id && $business && $template->ghorfe_online_list_id !== $business->id && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'شما اجازه دسترسی به این الگوی پیامک را ندارید.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'content' => 'required|string|max:1000',
            'sms_message_group_id' => 'nullable',
            'service_type' => $this->serviceTypeRule(),
            'otp_category' => ['nullable', 'required_if:service_type,otp', Rule::in(SmsOtpCategory::values())],
            'status' => 'required|in:active,inactive',
        ], [
            'otp_category.required_if' => 'برای الگوی OTP انتخاب دسته‌بندی سیستمی الزامی است.',
            'otp_category.in' => 'دسته‌بندی OTP انتخاب‌شده معتبر نیست.',
            'title.required' => 'عنوان الگو الزامی است.',
            'content.required' => 'متن الگو الزامی است.',
            'service_type.required' => 'نوع سرویس الگو را مشخص کنید.',
            'service_type.in' => 'نوع سرویس انتخاب‌شده برای شما مجاز نیست.',
            'status.required' => 'وضعیت الگو را مشخص کنید.',
        ]);

        // An unchanged legacy group stays editable; a newly chosen one must be promotional (non-OTP).
        if (($validated['sms_message_group_id'] ?? null) != $template->sms_message_group_id) {
            $this->assertPromotionalGroup($validated);
        }

        $this->templateService->update($template, $validated, Auth::id());

        $redirectParams = $business && auth()->user()?->isSuperAdmin() ? ['business_id' => $business->id] : [];

        return redirect()->route('admin.sms.settings.templates.index', $redirectParams)
            ->with('success', "الگوی پیامک «{$template->title}» با موفقیت به‌روزرسانی شد و در تاریخچه ثبت گردید.");
    }

    /**
     * Delete a template.
     */
    public function destroy(Request $request, SmsTemplate $template): RedirectResponse
    {
        $business = $this->resolveBusiness($request);
        $this->guardOtpTemplate($template);

        // Security: customer cannot delete another customer's template
        if ($template->ghorfe_online_list_id && $business && $template->ghorfe_online_list_id !== $business->id && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'شما اجازه دسترسی به این الگوی پیامک را ندارید.');
        }

        $title = $template->title;
        $this->templateService->delete($template);

        $redirectParams = $business && auth()->user()?->isSuperAdmin() ? ['business_id' => $business->id] : [];

        return redirect()->route('admin.sms.settings.templates.index', $redirectParams)
            ->with('success', "الگوی پیامک «{$title}» با موفقیت حذف شد.");
    }

    /**
     * Live preview endpoint using DynamicParameterResolver.
     */
    public function preview(Request $request): JsonResponse
    {
        $content = (string) $request->input('content', '');
        $context = (array) $request->input('context', []);

        $result = $this->templateService->preview($content, $context);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Request $request, SmsTemplate $template): JsonResponse|RedirectResponse
    {
        $business = $this->resolveBusiness($request);
        $this->guardOtpTemplate($template);

        // Security: customer cannot toggle another customer's template
        if ($template->ghorfe_online_list_id && $business && $template->ghorfe_online_list_id !== $business->id && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'شما اجازه دسترسی به این الگوی پیامک را ندارید.');
        }

        $updated = $this->templateService->toggleStatus($template);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $updated->status,
                'message' => "وضعیت الگو به «{$updated->status}» تغییر یافت.",
            ]);
        }

        return redirect()->back()->with('success', 'وضعیت الگو تغییر یافت.');
    }

    /**
     * Display revision history and visual diff view for a template.
     */
    public function history(Request $request, SmsTemplate $template): View
    {
        $business = $this->resolveBusiness($request);
        $this->guardOtpTemplate($template);

        // Security: customer cannot view history of another customer's template
        if ($template->ghorfe_online_list_id && $business && $template->ghorfe_online_list_id !== $business->id && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'شما اجازه دسترسی به این الگوی پیامک را ندارید.');
        }

        $historyId = $request->input('history_id') ? (int) $request->input('history_id') : null;
        $historyData = $this->templateService->getHistoryWithDiff($template, $historyId);

        return view('Admin.sms.settings.templates.history', array_merge($historyData, ['business' => $business]));
    }
}
