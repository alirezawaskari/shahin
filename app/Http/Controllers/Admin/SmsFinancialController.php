<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GhorfeOnlineList;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Exceptions\Sms\SmsPaymentUnavailableException;
use App\Services\Sms\SmsTopUpPaymentService;
use App\Services\Sms\BalanceGuard;
use App\Services\Sms\SmsFinancialService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsFinancialController extends Controller
{
    public function __construct(
        protected SmsFinancialService $financialService,
        protected BalanceGuard $balanceGuard
    ) {
    }

    /**
     * Resolve the current business context.
     */
    protected function resolveBusiness(Request $request): ?GhorfeOnlineList
    {
        $business = ghorfe();

        if (!$business && auth()->user()?->isSuperAdmin()) {
            $businessId = $request->input('business_id') ?: $request->input('business');
            if ($businessId) {
                $business = GhorfeOnlineList::find($businessId);
            }
            if (!$business && $request->filled('sender_number_id')) {
                $sender = Setting::find($request->input('sender_number_id'));
                if ($sender && $sender->settingable instanceof GhorfeOnlineList) {
                    $business = $sender->settingable;
                }
            }
            if (!$business) {
                $business = GhorfeOnlineList::first();
            }
        }

        return $business ?? abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');
    }

    /**
     * superAdmin with no current business and no ?business_id= gets sent to the
     * cross-business report instead of a 403. Other users are unaffected.
     */
    protected function superAdminWithoutBusiness(Request $request): bool
    {
        return !ghorfe()
            && auth()->user()?->isSuperAdmin()
            && !$request->filled('business_id')
            && !$request->filled('business');
    }

    /**
     * Per-business OTP balance view:
     * Balance, total charged, total consumed, message count,
     * charge/consumption history, and transaction list.
     */
    public function otpView(Request $request): View|JsonResponse|RedirectResponse
    {

        if ($this->superAdminWithoutBusiness($request)) {
            $firstBusinessId = GhorfeOnlineList::query()->value('id');
            if ($firstBusinessId) {
                return redirect()->route('admin.sms.financial.otp.index', ['business_id' => $firstBusinessId]);
            }
            return redirect()->route('admin.sms.reports.superadmin.index');
        }

        $business = $this->resolveBusiness($request);

        if (!$business) {
            abort(404, 'هیچ کسب‌وکاری در سیستم تعریف نشده است.');
        }

        $data = $this->financialService->getOtpFinancialData($business);
        $balanceStatus = $this->balanceGuard->checkOtp($business);

        $businesses = auth()->user()?->isSuperAdmin()
            ? GhorfeOnlineList::query()->select('id', 'title')->orderBy('title')->get()
            : collect();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => array_merge($data, ['guard' => $balanceStatus]),
            ]);
        }

        return view('Admin.sms.financial.otp', array_merge($data, [
            'guard' => $balanceStatus,
            'all_businesses' => $businesses,
        ]));
    }

    /**
     * Start an online payment for the OTP balance of the business.
     * Nothing is credited here: the balance is credited by SmsTopUpPaymentService
     * only after the payment module reports a verified success.
     */
    public function otpTopUp(Request $request): RedirectResponse|JsonResponse
    {
        abort_unless(app(\App\Services\Sms\SmsAccessTierService::class)->canUseOtp(auth()->user()), 403, 'دسترسی به سرویس پیامک اعتبارسنجی (OTP) برای سطح دسترسی شما مجاز نیست.');

        return $this->startTopUpPayment($request, 'otp');
    }

    /**
     * Shared initiator for both balance types. Client-supplied tracking ids are ignored.
     */
    protected function startTopUpPayment(Request $request, string $creditType): RedirectResponse|JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'business_id' => 'nullable|exists:ghorfe_online_lists,id',
        ]);

        $business = $this->resolveBusiness($request);

        if (!$business) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'کسب‌وکار معتبری یافت نشد.'], 404)
                : redirect()->back()->withErrors('کسب‌وکار معتبری یافت نشد.');
        }

        // Return target is derived server-side from the referring page, only if same-host.
        $previous = url()->previous();
        $returnUrl = parse_url($previous, PHP_URL_HOST) === $request->getHost() ? $previous : null;

        try {
            [$payment, $gatewayUrl] = app(SmsTopUpPaymentService::class)->initiate(
                $business,
                $creditType,
                (float) $request->input('amount'),
                auth()->user(),
                $returnUrl
            );
        } catch (DomainException $e) {
            $status = $e instanceof SmsPaymentUnavailableException ? 503 : 422;
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], $status);
            }
            return redirect()->back()->withErrors($e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect_url' => $gatewayUrl,
                'payment_id' => $payment->id,
            ]);
        }

        return redirect()->away($gatewayUrl);
    }

    /**
     * Landing page after the payment module has handled the bank callback.
     * Read-only: shows the stored outcome and sends the user back to where they came from.
     */
    public function topUpResult(Request $request, $payment): RedirectResponse
    {
        $payment = $payment instanceof SmsLog ? $payment : SmsLog::findOrFail($payment);
        $user = auth()->user();
        abort_unless(
            $user && ($user->isSuperAdmin() || (ghorfe() && (int) ghorfe()->id === (int) $payment->ghorfe_online_list_id)),
            403
        );

        $fallback = $payment->credit_type === 'otp'
            ? route('admin.sms.financial.otp.index', ['business_id' => $payment->ghorfe_online_list_id])
            : route('admin.sms.financial.advertising.index', ['business_id' => $payment->ghorfe_online_list_id]);
        $target = $payment->return_url ?: $fallback;

        if ($payment->isPaid()) {
            return redirect($target)->with('success', sprintf(
                'پرداخت موفق بود و %s ریال به موجودی شما افزوده شد. شناسه رهگیری: %s',
                number_format((float) $payment->amount),
                $payment->ref_number ?: $payment->id
            ));
        }

        if ($payment->isPending()) {
            return redirect($target)->with('success', 'پرداخت شما در حال بررسی است؛ پس از تایید بانک موجودی افزایش می‌یابد.');
        }

        return redirect($target)->withErrors('پرداخت ناموفق بود یا لغو شد و مبلغی به موجودی شما افزوده نشد.');
    }

    /**
     * Advertising panel balance view (one balance shared by all advertising lines):
     * Balance, total charged, total consumed, message count,
     * charge/consumption history, and transaction list.
     */
    public function advertisingView(Request $request): View|JsonResponse|RedirectResponse
    {
        if ($this->superAdminWithoutBusiness($request)) {
            $firstBusinessId = GhorfeOnlineList::query()->value('id');
            if ($firstBusinessId) {
                return redirect()->route('admin.sms.financial.advertising.index', ['business_id' => $firstBusinessId]);
            }
            return redirect()->route('admin.sms.reports.superadmin.index');
        }

        $business = $this->resolveBusiness($request);

        if (!$business) {
            abort(404, 'هیچ کسب‌وکاری در سیستم تعریف نشده است.');
        }

        $data = $this->financialService->getAdvertisingFinancialData($business);

        $businesses = auth()->user()?->isSuperAdmin()
            ? GhorfeOnlineList::query()->select('id', 'title')->orderBy('title')->get()
            : collect();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        }

        return view('Admin.sms.financial.advertising', array_merge($data, [
            'all_businesses' => $businesses,
        ]));
    }

    /**
     * Start an online payment for the Advertising panel balance (one balance per business).
     * Credits nothing until the payment module reports a verified success.
     */
    public function advertisingTopUp(Request $request): RedirectResponse|JsonResponse
    {
        return $this->startTopUpPayment($request, 'advertising');
    }

    /**
     * Check balance sufficiency via BalanceGuard API (useful for frontend pre-checks).
     */
    public function checkBalance(Request $request): JsonResponse
    {
        $business = $this->resolveBusiness($request);

        if (!$business) {
            return response()->json(['success' => false, 'message' => 'کسب‌وکار یافت نشد.'], 404);
        }

        $type = $request->input('type', 'otp');
        $cost = (float) $request->input('cost', 0);

        if ($type === 'otp') {
            abort_unless(app(\App\Services\Sms\SmsAccessTierService::class)->canUseOtp(auth()->user()), 403, 'دسترسی به سرویس پیامک اعتبارسنجی (OTP) برای سطح دسترسی شما مجاز نیست.');

            $status = $this->balanceGuard->checkOtp($business, $cost);
            return response()->json(['success' => true, 'status' => $status]);
        }

        $status = $this->balanceGuard->checkAdvertising($business, $cost);
        return response()->json(['success' => true, 'status' => $status]);
    }
}
