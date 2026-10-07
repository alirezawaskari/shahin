<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Http\Controllers\Controller;
use App\Models\SmsPanel;
use App\Services\SmsSettings\SmsPanelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsPanelController extends Controller
{
    public function __construct(
        protected SmsPanelService $panelService
    ) {
    }

    /**
     * Display listing of SMS panels.
     */
    public function index(Request $request): View
    {
        $business = ghorfe();
        $filters = $request->only(['type', 'status', 'search', 'per_page']);
        $panels = $this->panelService->list($filters, $business);

        return view('Admin.sms.settings.panels.index', compact('panels', 'filters', 'business'));
    }

    /**
     * Show form for creating a new SMS panel.
     */
    public function create(): View
    {
        $business = ghorfe();
        return view('Admin.sms.settings.panels.create', compact('business'));
    }

    /**
     * Store a newly created SMS panel.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'nullable|string|max:191',
            'type' => 'required|in:otp,normal-advertising,advertising,all',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
            'connection_info' => 'nullable|array',
            'connection_info.api_key' => 'nullable|string',
            'connection_info.username' => 'nullable|string',
            'connection_info.password' => 'nullable|string',
            'connection_info.base_url' => 'nullable|string',
            'send_settings' => 'nullable|array',
            'send_settings.default_sender' => 'nullable|string',
            'send_settings.timeout' => 'nullable|integer',
            'send_settings.auto_append_cancel' => 'nullable|boolean',
            'receive_settings' => 'nullable|array',
            'receive_settings.webhook_url' => 'nullable|string',
            'delivery_settings' => 'nullable|array',
            'delivery_settings.callback_url' => 'nullable|string',
            'delivery_settings.auto_check' => 'nullable|boolean',
        ], [
            'name.required' => 'نام پنل پیامک الزامی است.',
            'type.required' => 'نوع پنل پیامک را انتخاب کنید.',
            'status.required' => 'وضعیت پنل را مشخص کنید.',
        ]);

        $business = ghorfe();
        $panel = $this->panelService->create($validated, $business);

        return redirect()->route('admin.sms.settings.panels.index')
            ->with('success', "پنل پیامک «{$panel->name}» با موفقیت افزوده شد.");
    }

    /**
     * Show form for editing an SMS panel.
     */
    public function edit(SmsPanel $panel): View
    {
        $business = ghorfe();
        return view('Admin.sms.settings.panels.edit', compact('panel', 'business'));
    }

    /**
     * Update an SMS panel.
     */
    public function update(Request $request, SmsPanel $panel): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'nullable|string|max:191',
            'type' => 'required|in:otp,normal-advertising,advertising,all',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
            'connection_info' => 'nullable|array',
            'connection_info.api_key' => 'nullable|string',
            'connection_info.username' => 'nullable|string',
            'connection_info.password' => 'nullable|string',
            'connection_info.base_url' => 'nullable|string',
            'send_settings' => 'nullable|array',
            'send_settings.default_sender' => 'nullable|string',
            'send_settings.timeout' => 'nullable|integer',
            'send_settings.auto_append_cancel' => 'nullable|boolean',
            'receive_settings' => 'nullable|array',
            'receive_settings.webhook_url' => 'nullable|string',
            'delivery_settings' => 'nullable|array',
            'delivery_settings.callback_url' => 'nullable|string',
            'delivery_settings.auto_check' => 'nullable|boolean',
        ], [
            'name.required' => 'نام پنل پیامک الزامی است.',
            'type.required' => 'نوع پنل پیامک را انتخاب کنید.',
            'status.required' => 'وضعیت پنل را مشخص کنید.',
        ]);

        $this->panelService->update($panel, $validated);

        return redirect()->route('admin.sms.settings.panels.index')
            ->with('success', "تنظیمات پنل «{$panel->name}» با موفقیت به‌روزرسانی شد.");
    }

    /**
     * Delete an SMS panel.
     */
    public function destroy(SmsPanel $panel): RedirectResponse
    {
        $name = $panel->name;
        $this->panelService->delete($panel);

        return redirect()->route('admin.sms.settings.panels.index')
            ->with('success', "پنل پیامک «{$name}» با موفقیت حذف شد.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Request $request, SmsPanel $panel): JsonResponse|RedirectResponse
    {
        $updated = $this->panelService->toggleStatus($panel);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $updated->status,
                'message' => "وضعیت پنل به «{$updated->status}» تغییر یافت.",
            ]);
        }

        return redirect()->back()->with('success', 'وضعیت پنل پیامک تغییر یافت.');
    }
}
