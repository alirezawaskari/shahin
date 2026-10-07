<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Http\Controllers\Controller;
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
     * Display listing of templates.
     */
    public function index(Request $request): View
    {
        $business = ghorfe();
        $filters = $request->only(['service_type', 'status', 'group_id', 'search', 'per_page']);
        $templates = $this->templateService->list($filters);
        $groups = $this->groupService->tree();

        return view('Admin.sms.settings.templates.index', compact('templates', 'groups', 'filters', 'business'));
    }

    /**
     * Show form for creating a new template.
     */
    public function create(): View
    {
        $business = ghorfe();
        $groupOptions = $this->groupService->options();
        $groups = $this->groupService->tree();

        return view('Admin.sms.settings.templates.create', compact('groupOptions', 'groups', 'business'));
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request): RedirectResponse
    {
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

        $template = $this->templateService->create($validated, Auth::id());

        return redirect()->route('admin.sms.settings.templates.index')
            ->with('success', "الگوی پیامک «{$template->title}» با موفقیت افزوده شد.");
    }

    /**
     * Show form for editing a template.
     */
    public function edit(SmsMessageTemplate $template): View
    {
        $business = ghorfe();
        $groupOptions = $this->groupService->options();
        $groups = $this->groupService->tree();

        return view('Admin.sms.settings.templates.edit', compact('template', 'groupOptions', 'groups', 'business'));
    }

    /**
     * Update an existing template.
     * Fires SmsMessageTemplateObserver to record history snapshot.
     */
    public function update(Request $request, SmsMessageTemplate $template): RedirectResponse
    {
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

        return redirect()->route('admin.sms.settings.templates.index')
            ->with('success', "الگوی پیامک «{$template->title}» با موفقیت به‌روزرسانی شد و در تاریخچه ثبت گردید.");
    }

    /**
     * Delete a template.
     */
    public function destroy(SmsMessageTemplate $template): RedirectResponse
    {
        $title = $template->title;
        $this->templateService->delete($template);

        return redirect()->route('admin.sms.settings.templates.index')
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
        $business = ghorfe();
        $historyId = $request->input('history_id') ? (int) $request->input('history_id') : null;
        $historyData = $this->templateService->getHistoryWithDiff($template, $historyId);

        return view('Admin.sms.settings.templates.history', array_merge($historyData, ['business' => $business]));
    }
}
