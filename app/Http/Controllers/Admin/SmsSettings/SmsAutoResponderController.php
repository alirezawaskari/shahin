<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Http\Controllers\Controller;
use App\Models\GhorfeOnlineList;
use App\Models\Setting;
use App\Services\Sms\SmsReservedCodes;
use App\Services\SmsSettings\SmsAutoResponderService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * «پاسخ‌گوی خودکار»: per-business keyword -> reply rules. Open to every SMS business user
 * (not OTP-only), always scoped to the current business like the other SMS settings pages.
 */
class SmsAutoResponderController extends Controller
{
    public function __construct(protected SmsAutoResponderService $service)
    {
    }

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
     * A rule is reachable only inside its own business (superadmin may cross).
     */
    protected function guardRule(Setting $rule, ?GhorfeOnlineList $business): void
    {
        if (auth()->user()?->isSuperAdmin()) {
            return;
        }
        if (!$business || (int) $rule->ghorfe_online_list_id !== (int) $business->id) {
            abort(403, 'شما اجازه دسترسی به این قانون پاسخ‌گوی خودکار را ندارید.');
        }
    }

    protected function requireBusiness(?GhorfeOnlineList $business): GhorfeOnlineList
    {
        if (!$business) {
            // e.g. super-admin in the 'all businesses' view: send them back to the list with a hint
            // instead of an error page. (Cross-business access to an existing rule stays a 403: guardRule.)
            throw new HttpResponseException(
                redirect()->route('admin.sms.settings.auto-responders.index')->with('error', 'ابتدا یک کسب‌وکار را انتخاب کنید.')
            );
        }

        return $business;
    }

    protected function redirectParams(?GhorfeOnlineList $business): array
    {
        return $business && auth()->user()?->isSuperAdmin() ? ['business_id' => $business->id] : [];
    }

    protected function rules(?GhorfeOnlineList $business): array
    {
        return [
            'name' => 'required|string|max:191',
            'trigger_keyword' => 'required|string|max:191',
            'match_type' => 'required|in:exact,starts_with',
            'reply_text' => 'required|string|max:1000',
            'sender_number_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'نام قانون الزامی است.',
            'trigger_keyword.required' => 'کلمه کلیدی (محرک) الزامی است.',
            'match_type.required' => 'نوع تطبیق را مشخص کنید.',
            'match_type.in' => 'نوع تطبیق معتبر نیست.',
            'reply_text.required' => 'متن پاسخ الزامی است.',
            'reply_text.max' => 'متن پاسخ نباید بیشتر از ۱۰۰۰ کاراکتر باشد.',
        ];
    }

    /**
     * Shared validation: field rules, sender line belongs to the business, then the collision rules.
     */
    protected function validated(Request $request, GhorfeOnlineList $business, ?int $ignoreId = null): array
    {
        $data = $request->validate($this->rules($business), $this->messages());
        $data['is_active'] = $request->boolean('is_active');

        if (!empty($data['sender_number_id'])) {
            $owns = $business->senderNumbers()
                ->where('sender_numbers.id', $data['sender_number_id'])
                ->where('sender_numbers.type', 'advertising')
                ->where('sender_numbers.status', 'active')
                ->exists();
            if (!$owns) {
                throw ValidationException::withMessages(['sender_number_id' => 'خط انتخابی به این کسب‌وکار تخصیص نیافته است یا فعال نمی‌باشد.']);
            }
        }

        // The collision rule applies to every saved trigger (even inactive ones: they would collide on re-activation,
        // which is re-checked then), but only reserved codes block inactive rules; duplicates only matter when active.
        $conflicts = $this->service->validateTrigger($business->id, $data['trigger_keyword'], $data['match_type'], $ignoreId);
        if (!$data['is_active']) {
            $conflicts = $this->reservedOnly($conflicts, $data['trigger_keyword'], $data['match_type'], $business->id, $ignoreId);
        }
        if ($conflicts) {
            throw ValidationException::withMessages(['trigger_keyword' => $conflicts]);
        }

        return $data;
    }

    /**
     * For an inactive rule, keep only the reserved-code messages (duplicates with other active rules do not block it).
     *
     * @param string[] $conflicts
     * @return string[]
     */
    protected function reservedOnly(array $conflicts, string $keyword, string $matchType, int $businessId, ?int $ignoreId): array
    {
        $entries = SmsReservedCodes::all();
        $reserved = SmsReservedCodes::find($keyword, $entries)
            ?? ($matchType === Setting::MATCH_STARTS_WITH ? SmsReservedCodes::shadowedBy($keyword, $entries) : null);

        return $reserved ? array_slice($conflicts, 0, 1) : [];
    }

    protected function senderOptions(?GhorfeOnlineList $business)
    {
        return $business
            ? $business->senderNumbers()->where('type', 'advertising')->where('status', 'active')->get()
            : collect();
    }

    public function index(Request $request): View
    {
        $business = $this->resolveBusiness($request);
        if (!$business && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'کسب‌وکاری برای حساب شما یافت نشد.');
        }
        $all_businesses = auth()->user()?->isSuperAdmin() ? GhorfeOnlineList::select('id', 'title')->get() : collect();

        $filters = $request->only(['status', 'search', 'per_page']);
        $rules = $this->service->list($business?->id, $filters);

        return view('Admin.sms.settings.auto_responders.index', compact('rules', 'filters', 'business', 'all_businesses'));
    }

    public function create(Request $request): View
    {
        $business = $this->requireBusiness($this->resolveBusiness($request));
        $senders = $this->senderOptions($business);
        $reserved = SmsReservedCodes::all();

        return view('Admin.sms.settings.auto_responders.create', compact('business', 'senders', 'reserved'));
    }

    public function store(Request $request): RedirectResponse
    {
        $business = $this->requireBusiness($this->resolveBusiness($request));
        $data = $this->validated($request, $business);

        $rule = $this->service->create($business->id, $data, auth()->id());

        return redirect()->route('admin.sms.settings.auto-responders.index', $this->redirectParams($business))
            ->with('success', "قانون پاسخ‌گوی خودکار «{$rule->name}» با موفقیت افزوده شد.");
    }

    public function edit(Request $request, Setting $rule): View
    {
        $business = $this->resolveBusiness($request);
        $this->guardRule($rule, $business);

        $business = $rule->business;
        $senders = $this->senderOptions($business);
        $reserved = SmsReservedCodes::all();

        return view('Admin.sms.settings.auto_responders.edit', compact('rule', 'business', 'senders', 'reserved'));
    }

    public function update(Request $request, Setting $rule): RedirectResponse
    {
        $business = $this->resolveBusiness($request);
        $this->guardRule($rule, $business);

        $ruleBusiness = $rule->business;
        $data = $this->validated($request, $ruleBusiness, $rule->id);
        $this->service->update($rule, $data, auth()->id());

        return redirect()->route('admin.sms.settings.auto-responders.index', $this->redirectParams($ruleBusiness))
            ->with('success', "قانون «{$rule->name}» با موفقیت ویرایش شد.");
    }

    public function destroy(Request $request, Setting $rule): RedirectResponse
    {
        $business = $this->resolveBusiness($request);
        $this->guardRule($rule, $business);

        $this->service->delete($rule);

        return redirect()->route('admin.sms.settings.auto-responders.index', $this->redirectParams($business))
            ->with('success', 'قانون پاسخ‌گوی خودکار حذف شد.');
    }

    public function toggleStatus(Request $request, Setting $rule): RedirectResponse
    {
        $business = $this->resolveBusiness($request);
        $this->guardRule($rule, $business);

        $errors = $this->service->toggle($rule);
        if ($errors) {
            return back()->withErrors(['toggle' => $errors]);
        }

        return back()->with('success', $rule->is_active ? 'قانون فعال شد.' : 'قانون غیرفعال شد.');
    }
}
