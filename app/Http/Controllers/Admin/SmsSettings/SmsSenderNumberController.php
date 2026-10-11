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

    /**
     * Display listing of sender numbers.
     * Enforces: "A business only sees its own assigned numbers".
     */
    public function index(Request $request): View
    {
        $business = ghorfe();
        $filters = $request->only(['type', 'status', 'search', 'ghorfe_id', 'per_page']);
        $senderNumbers = $this->senderNumberService->list($business, $filters);
        $businesses = $business ? collect([$business]) : GhorfeOnlineList::orderBy('title')->get();
        $panels = SmsPanel::active()->get();

        return view('Admin.sms.settings.senders.index', compact('senderNumbers', 'filters', 'business', 'businesses', 'panels'));
    }

    /**
     * Show form for creating a new sender number.
     */
    public function create(): View
    {
        $business = ghorfe();
        $businesses = $business ? collect([$business]) : GhorfeOnlineList::orderBy('title')->get();
        $panels = SmsPanel::active()->get();

        return view('Admin.sms.settings.senders.create', compact('business', 'businesses', 'panels'));
    }

    /**
     * Store a newly created sender number.
     * Catches single-OTP-row guard and surfaces as a clean validation error.
     */
    public function store(Request $request): RedirectResponse
    {
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

        $business = ghorfe();
        $businessIds = $request->input('business_ids', []);
        if ($business && empty($businessIds)) {
            $businessIds = [$business->id];
        }

        // Security check: Clients/Business owners cannot approve their own contract!
        if (!auth()->user()?->isSuperAdmin()) {
            $validated['contract_status'] = 'در انتظار تایید تعهدنامه';
        } else {
            $validated['contract_status'] = $validated['contract_status'] ?: 'تایید شده';
        }

        try {
            $sender = $this->senderNumberService->create($validated, $businessIds);
        } catch (ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (DomainException $e) {
            return redirect()->back()->withInput()->withErrors(['type' => $e->getMessage()]);
        }

        return redirect()->route('admin.sms.settings.senders.index')
            ->with('success', "شماره اختصاصی «{$sender->number}» با موفقیت افزوده شد.");
    }

    /**
     * Show form for editing a sender number.
     */
    public function edit(SenderNumber $senderNumber): View
    {
        $business = ghorfe();
        if ($business && !$this->senderNumberService->canAccess($senderNumber, $business)) {
            abort(403, 'شما به این خط اختصاصی دسترسی ندارید.');
        }

        $businesses = $business ? collect([$business]) : GhorfeOnlineList::orderBy('title')->get();
        $assignedBusinessIds = $senderNumber->businesses()->pluck('ghorfe_online_lists.id')->toArray();
        $panels = SmsPanel::active()->get();

        return view('Admin.sms.settings.senders.edit', compact('senderNumber', 'business', 'businesses', 'assignedBusinessIds', 'panels'));
    }

    /**
     * Update an existing sender number.
     * Catches single-OTP-row guard and surfaces as a clean validation error.
     */
    public function update(Request $request, SenderNumber $senderNumber): RedirectResponse
    {
        $business = ghorfe();
        if ($business && !$this->senderNumberService->canAccess($senderNumber, $business)) {
            abort(403, 'شما به این خط اختصاصی دسترسی ندارید.');
        }

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

        // Security check: Clients/Business owners cannot modify contract_status!
        if (!auth()->user()?->isSuperAdmin()) {
            unset($validated['contract_status']);
        }

        $businessIds = $business ? null : $request->input('business_ids', []);

        try {
            $this->senderNumberService->update($senderNumber, $validated, $businessIds);
        } catch (ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (DomainException $e) {
            return redirect()->back()->withInput()->withErrors(['type' => $e->getMessage()]);
        }

        return redirect()->route('admin.sms.settings.senders.index')
            ->with('success', "شماره اختصاصی «{$senderNumber->number}» با موفقیت به‌روزرسانی شد.");
    }

    /**
     * Delete a sender number.
     */
    public function destroy(SenderNumber $senderNumber): RedirectResponse
    {
        $business = ghorfe();
        if ($business && !$this->senderNumberService->canAccess($senderNumber, $business)) {
            abort(403, 'شما به این خط اختصاصی دسترسی ندارید.');
        }

        $number = $senderNumber->number;
        $this->senderNumberService->delete($senderNumber);

        return redirect()->route('admin.sms.settings.senders.index')
            ->with('success', "شماره اختصاصی «{$number}» با موفقیت حذف شد.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Request $request, SenderNumber $senderNumber): JsonResponse|RedirectResponse
    {
        $business = ghorfe();
        if ($business && !$this->senderNumberService->canAccess($senderNumber, $business)) {
            abort(403, 'شما به این خط اختصاصی دسترسی ندارید.');
        }

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
     * Assign businesses to a sender number.
     */
    public function assignBusinesses(Request $request, SenderNumber $senderNumber): RedirectResponse
    {
        $business = ghorfe();
        if ($business) {
            abort(403, 'فقط مدیر سامانه می‌تواند تخصیص خطوط به کسب‌وکارها را تغییر دهد.');
        }

        $validated = $request->validate([
            'business_ids' => 'nullable|array',
            'business_ids.*' => 'nullable|exists:ghorfe_online_lists,id',
        ]);

        $this->senderNumberService->assignBusinesses($senderNumber, $validated['business_ids'] ?? []);

        return redirect()->back()->with('success', "کسب‌وکارهای اختصاص داده شده به خط «{$senderNumber->number}» با موفقیت ذخیره شدند.");
    }
}
