<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
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

    protected function ensureSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'تنها مدیر ارشد سامانه به تنظیمات پنل‌های پیامک دسترسی دارد.');
    }

    /**
     * Display listing of SMS panels.
     */
    public function index(Request $request): View
    {
        $this->ensureSuperAdmin();
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
        $this->ensureSuperAdmin();
        $business = ghorfe();
        return view('Admin.sms.settings.panels.create', compact('business'));
    }

    /**
     * Store a newly created SMS panel.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'nullable|string|max:191',
            'type' => 'required|in:otp,normal-advertising,advertising,all',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
            'connection_info' => 'nullable|array',
            'connection_info.driver' => 'nullable|string|in:iransms,neginsms,custom',
            'connection_info.portal_code' => 'nullable|string|max:191',
            'connection_info.api_key' => 'nullable|string|max:191',
            'connection_info.username' => 'nullable|string|max:191',
            'connection_info.password' => 'nullable|string|max:191',
            'connection_info.base_url' => 'nullable|string|max:255',
            'connection_info.line_number' => 'nullable|string|max:50',
            'send_settings' => 'nullable|array',
            'send_settings.default_sender' => 'nullable|string',
            'send_settings.timeout' => 'nullable|integer',
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
    public function edit(Setting $panel): View
    {
        $this->ensureSuperAdmin();
        $business = ghorfe();
        return view('Admin.sms.settings.panels.edit', compact('panel', 'business'));
    }

    /**
     * Update an SMS panel.
     */
    public function update(Request $request, Setting $panel): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'nullable|string|max:191',
            'type' => 'required|in:otp,normal-advertising,advertising,all',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
            'connection_info' => 'nullable|array',
            'connection_info.driver' => 'nullable|string|in:iransms,neginsms,custom',
            'connection_info.portal_code' => 'nullable|string|max:191',
            'connection_info.api_key' => 'nullable|string|max:191',
            'connection_info.username' => 'nullable|string|max:191',
            'connection_info.password' => 'nullable|string|max:191',
            'connection_info.base_url' => 'nullable|string|max:255',
            'connection_info.line_number' => 'nullable|string|max:50',
            'send_settings' => 'nullable|array',
            'send_settings.default_sender' => 'nullable|string',
            'send_settings.timeout' => 'nullable|integer',
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
    public function destroy(Setting $panel): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $name = $panel->name;
        $this->panelService->delete($panel);

        return redirect()->route('admin.sms.settings.panels.index')
            ->with('success', "پنل پیامک «{$name}» با موفقیت حذف شد.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Request $request, Setting $panel): JsonResponse|RedirectResponse
    {
        $this->ensureSuperAdmin();
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
