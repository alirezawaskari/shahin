<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Http\Controllers\Controller;
use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
use App\Models\SmsPanel;
use App\Services\SmsSettings\SmsSenderNumberService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SmsSenderNumberController extends Controller
{
    public function __construct(
        protected SmsSenderNumberService $senderNumberService
    ) {
    }

    protected function isSuperAdmin(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    /**
     * Display listing of sender numbers.
     * Enforces: "A business only sees its own assigned numbers".
     */
    public function index(Request $request): View
    {
        $business = ghorfe();
        if (!$business && $this->isSuperAdmin() && $request->filled('ghorfe_id') && $request->input('ghorfe_id') !== 'all') {
            // SuperAdmin remains in global view with filter applied in service
        }

        $filters = $request->only(['type', 'status', 'search', 'ghorfe_id', 'per_page']);
        $senderNumbers = $this->senderNumberService->list($business, $filters);
        $businesses = $business ? collect([$business]) : GhorfeOnlineList::orderBy('title')->get();
        $panels = $this->isSuperAdmin() ? SmsPanel::active()->get() : collect();
        $otpSender = SenderNumber::where('type', 'otp')->first();

        return view('Admin.sms.settings.senders.index', compact(
            'senderNumbers',
            'filters',
            'business',
            'businesses',
            'panels',
            'otpSender'
        ));
    }

    /**
     * Show form for creating a new sender number in the global pool (SuperAdmin only).
     */
    public function create(): View
    {
        abort_unless($this->isSuperAdmin(), 403, 'تعریف شماره جدید در مخزن خطوط منحصراً در اختیار مدیریت ارشد سامانه است.');

        $business = ghorfe();
        $businesses = GhorfeOnlineList::orderBy('title')->get();
        $panels = SmsPanel::active()->get();

        return view('Admin.sms.settings.senders.create', compact('business', 'businesses', 'panels'));
    }

    /**
     * Store a newly created sender number in the global pool (SuperAdmin only).
     * Catches single-OTP-row guard and surfaces as a clean validation error.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->isSuperAdmin(), 403, 'تعریف شماره جدید در مخزن خطوط منحصراً در اختیار مدیریت ارشد سامانه است.');

        $validated = $request->validate([
            'number' => 'required|string|max:50|unique:sender_numbers,number',
            'type' => 'required|in:otp,advertising',
            'sms_panel' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',
            'contract_status' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'business_ids' => 'nullable|array',
            'business_ids.*' => 'nullable|exists:ghorfe_online_lists,id',
        ], [
            'number.required' => 'شماره خط فرستنده الزامی است.',
            'number.unique' => 'این شماره قبلاً در سامانه ثبت شده است.',
            'type.required' => 'نوع خط را مشخص کنید.',
            'sms_panel.required' => 'پنل پیامک مربوطه را انتخاب کنید.',
            'status.required' => 'وضعیت خط را مشخص کنید.',
        ]);

        $businessIds = $request->input('business_ids', []);
        $validated['contract_status'] = $validated['contract_status'] ?: 'تایید شده';

        try {
            $sender = $this->senderNumberService->create($validated, $businessIds);
        } catch (ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (DomainException $e) {
            return redirect()->back()->withInput()->withErrors(['type' => $e->getMessage()]);
        }

        return redirect()->route('admin.sms.settings.senders.index')
            ->with('success', "شماره «{$sender->number}» با موفقیت به مخزن خطوط افزوده شد.");
    }

    /**
     * Show form for editing a sender number in the global pool (SuperAdmin only).
     */
    public function edit(SenderNumber $senderNumber): View
    {
        abort_unless($this->isSuperAdmin(), 403, 'ویرایش مشخصات پایه خطوط منحصراً در اختیار مدیریت ارشد سامانه است.');

        $business = ghorfe();
        $businesses = GhorfeOnlineList::orderBy('title')->get();
        $assignedBusinessIds = $senderNumber->businesses()->pluck('ghorfe_online_lists.id')->toArray();
        $panels = SmsPanel::active()->get();

        return view('Admin.sms.settings.senders.edit', compact('senderNumber', 'business', 'businesses', 'assignedBusinessIds', 'panels'));
    }

    /**
     * Update an existing sender number in the global pool (SuperAdmin only).
     * Catches single-OTP-row guard and surfaces as a clean validation error.
     */
    public function update(Request $request, SenderNumber $senderNumber): RedirectResponse
    {
        abort_unless($this->isSuperAdmin(), 403, 'ویرایش مشخصات پایه خطوط منحصراً در اختیار مدیریت ارشد سامانه است.');

        $validated = $request->validate([
            'number' => 'required|string|max:50|unique:sender_numbers,number,' . $senderNumber->id,
            'type' => 'required|in:otp,advertising',
            'sms_panel' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',
            'contract_status' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'business_ids' => 'nullable|array',
            'business_ids.*' => 'nullable|exists:ghorfe_online_lists,id',
        ], [
            'number.required' => 'شماره خط فرستنده الزامی است.',
            'number.unique' => 'این شماره برای خط دیگری ثبت شده است.',
            'type.required' => 'نوع خط را مشخص کنید.',
            'sms_panel.required' => 'پنل پیامک مربوطه را انتخاب کنید.',
            'status.required' => 'وضعیت خط را مشخص کنید.',
        ]);

        $businessIds = $request->input('business_ids', []);

        try {
            $this->senderNumberService->update($senderNumber, $validated, $businessIds);
        } catch (ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (DomainException $e) {
            return redirect()->back()->withInput()->withErrors(['type' => $e->getMessage()]);
        }

        return redirect()->route('admin.sms.settings.senders.index')
            ->with('success', "شماره «{$senderNumber->number}» با موفقیت به‌روزرسانی شد.");
    }

    /**
     * Delete a sender number from the global pool (SuperAdmin only).
     */
    public function destroy(SenderNumber $senderNumber): RedirectResponse
    {
        abort_unless($this->isSuperAdmin(), 403, 'حذف خط از مخزن سامانه منحصراً در اختیار مدیریت ارشد است.');

        $number = $senderNumber->number;
        $this->senderNumberService->delete($senderNumber);

        return redirect()->route('admin.sms.settings.senders.index')
            ->with('success', "شماره «{$number}» با موفقیت حذف شد.");
    }

    /**
     * Toggle active/inactive status of a sender number in the global pool (SuperAdmin only).
     */
    public function toggleStatus(Request $request, SenderNumber $senderNumber): JsonResponse|RedirectResponse
    {
        abort_unless($this->isSuperAdmin(), 403, 'تغییر وضعیت سراسری خط در مخزن منحصراً در اختیار مدیریت ارشد سامانه است.');

        $updated = $this->senderNumberService->toggleStatus($senderNumber);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $updated->status,
                'message' => "وضعیت شماره «{$updated->number}» به «{$updated->status}» تغییر یافت.",
            ]);
        }

        return redirect()->back()->with('success', 'وضعیت شماره تغییر یافت.');
    }

    /**
     * Assign businesses to a sender number (SuperAdmin only; many-to-many).
     */
    public function assignBusinesses(Request $request, SenderNumber $senderNumber): RedirectResponse
    {
        abort_unless($this->isSuperAdmin(), 403, 'فقط مدیر ارشد سامانه می‌تواند تخصیص خطوط به کسب‌وکارها را تغییر دهد.');

        $validated = $request->validate([
            'business_ids' => 'nullable|array',
            'business_ids.*' => 'nullable|exists:ghorfe_online_lists,id',
        ]);

        $this->senderNumberService->assignBusinesses($senderNumber, $validated['business_ids'] ?? []);

        return redirect()->back()->with('success', "کسب‌وکارهای مشترک خط «{$senderNumber->number}» با موفقیت ذخیره شدند.");
    }
}
