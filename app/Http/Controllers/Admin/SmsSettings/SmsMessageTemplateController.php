<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Http\Controllers\Controller;
use App\Models\GhorfeOnlineList;
use App\Models\SmsMessageGroup;
use App\Models\SmsMessageTemplate;
use App\Services\SmsSettings\SmsMessageGroupService;
use App\Services\SmsSettings\SmsMessageTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Display listing of templates for the current business.
     */
    public function index(Request $request): View
    {
        $business = $this->resolveBusiness($request);
        $all_businesses = auth()->user()?->isSuperAdmin() ? GhorfeOnlineList::select('id', 'title')->get() : collect();

        $filters = $request->only(['service_type', 'status', 'group_id', 'search', 'per_page']);
        $templates = $this->templateService->list($filters, $business?->id);
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

        return view('Admin.sms.settings.templates.create', compact('groupOptions', 'groups', 'business'));
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
            'sms_message_group_id' => 'nullable|exists:sms_message_groups,id',
            'service_type' => 'required|in:otp,normal,advertising',
            'status' => 'required|in:active,inactive',
        ], [
            'title.required' => 'عنوان الگو الزامی است.',
            'content.required' => 'متن الگو الزامی است.',
            'service_type.required' => 'نوع سرویس الگو را مشخص کنید.',
            'status.required' => 'وضعیت الگو را مشخص کنید.',
        ]);

        // Security: verify chosen group belongs to the same business
        if (!empty($validated['sms_message_group_id']) && $business) {
            $group = SmsMessageGroup::find($validated['sms_message_group_id']);
            if ($group && $group->ghorfe_online_list_id && $group->ghorfe_online_list_id !== $business->id && !auth()->user()?->isSuperAdmin()) {
                abort(403, 'گروه انتخابی متعلق به کسب‌وکار شما نیست.');
            }
        }

        $template = $this->templateService->create($validated, Auth::id(), $business?->id);

        $redirectParams = $business && auth()->user()?->isSuperAdmin() ? ['business_id' => $business->id] : [];

        return redirect()->route('admin.sms.settings.templates.index', $redirectParams)
            ->with('success', "الگوی پیامک «{$template->title}» با موفقیت افزوده شد.");
    }

    /**
     * Show form for editing a template.
     */
    public function edit(Request $request, SmsMessageTemplate $template): View
    {
        $business = $this->resolveBusiness($request);

        // Security: customer cannot edit another customer's template
        if ($template->ghorfe_online_list_id && $business && $template->ghorfe_online_list_id !== $business->id && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'شما اجازه دسترسی به این الگوی پیامک را ندارید.');
        }

        $targetBizId = $template->ghorfe_online_list_id ?: $business?->id;
        $groupOptions = $this->groupService->options($targetBizId);
        $groups = $this->groupService->tree($targetBizId);

        return view('Admin.sms.settings.templates.edit', compact('template', 'groupOptions', 'groups', 'business'));
    }

    /**
     * Update an existing template.
     */
    public function update(Request $request, SmsMessageTemplate $template): RedirectResponse
    {
        $business = $this->resolveBusiness($request);

        // Security: customer cannot update another customer's template
        if ($template->ghorfe_online_list_id && $business && $template->ghorfe_online_list_id !== $business->id && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'شما اجازه دسترسی به این الگوی پیامک را ندارید.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'content' => 'required|string|max:1000',
            'sms_message_group_id' => 'nullable|exists:sms_message_groups,id',
            'service_type' => 'required|in:otp,normal,advertising',
            'status' => 'required|in:active,inactive',
        ], [
            'title.required' => 'عنوان الگو الزامی است.',
            'content.required' => 'متن الگو الزامی است.',
            'service_type.required' => 'نوع سرویس الگو را مشخص کنید.',
            'status.required' => 'وضعیت الگو را مشخص کنید.',
        ]);

        $this->templateService->update($template, $validated, Auth::id());

        $redirectParams = $business && auth()->user()?->isSuperAdmin() ? ['business_id' => $business->id] : [];

        return redirect()->route('admin.sms.settings.templates.index', $redirectParams)
            ->with('success', "الگوی پیامک «{$template->title}» با موفقیت به‌روزرسانی شد و در تاریخچه ثبت گردید.");
    }

    /**
     * Delete a template.
     */
    public function destroy(Request $request, SmsMessageTemplate $template): RedirectResponse
    {
        $business = $this->resolveBusiness($request);

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
    public function toggleStatus(Request $request, SmsMessageTemplate $template): JsonResponse|RedirectResponse
    {
        $business = $this->resolveBusiness($request);

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
    public function history(Request $request, SmsMessageTemplate $template): View
    {
        $business = $this->resolveBusiness($request);

        // Security: customer cannot view history of another customer's template
        if ($template->ghorfe_online_list_id && $business && $template->ghorfe_online_list_id !== $business->id && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'شما اجازه دسترسی به این الگوی پیامک را ندارید.');
        }

        $historyId = $request->input('history_id') ? (int) $request->input('history_id') : null;
        $historyData = $this->templateService->getHistoryWithDiff($template, $historyId);

        return view('Admin.sms.settings.templates.history', array_merge($historyData, ['business' => $business]));
    }
}
