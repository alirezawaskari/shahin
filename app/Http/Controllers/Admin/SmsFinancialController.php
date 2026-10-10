<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
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

        if (!$business && auth()->user()?->isSuperAdmin() && $request->filled('business_id')) {
            $business = GhorfeOnlineList::find($request->input('business_id'));
        }

        return $business ?? abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');
    }

    /**
     * Per-business OTP balance view:
     * Balance, total charged, total consumed, message count,
     * charge/consumption history, and transaction list.
     */
    public function otpView(Request $request): View|JsonResponse
    {
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
     * Top-up OTP balance for the business.
     * Credits ONLY this business's otp_balances row.
     */
    public function otpTopUp(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'business_id' => 'nullable|exists:ghorfe_online_lists,id',
            'tracking_id' => 'nullable|string|max:100',
        ]);

        $business = $this->resolveBusiness($request);

        if (!$business) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'کسب‌وکار معتبری یافت نشد.'], 404)
                : redirect()->back()->withErrors('کسب‌وکار معتبری یافت نشد.');
        }

        try {
            $transaction = $this->financialService->topUpOtp(
                $business,
                (float) $request->input('amount'),
                auth()->user(),
                $request->input('tracking_id')
            );

            $msg = sprintf(
                'شارژ پیامک اعتبارسنجی (OTP) به مبلغ %s ریال با موفقیت انجام شد. شناسه تراکنش: %s',
                number_format((float) $request->input('amount')),
                $transaction->tracking_id
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'transaction' => $transaction,
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * Per-sender advertising balance view:
     * Balance, total charged, total consumed, message count,
     * charge/consumption history, and transaction list.
     */
    public function advertisingView(Request $request, ?SenderNumber $senderNumber = null): View|JsonResponse
    {
        $business = $this->resolveBusiness($request);

        if (!$business) {
            abort(404, 'هیچ کسب‌وکاری در سیستم تعریف نشده است.');
        }

        if (!$senderNumber && $request->filled('sender_number_id')) {
            $senderNumber = SenderNumber::find($request->input('sender_number_id'));
        }

        $data = $this->financialService->getAdvertisingFinancialData($business, $senderNumber);

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
     * Top-up Advertising balance for a chosen sender number.
     * Credits ONLY the chosen sender's advertising_balances row for that business. Never cross-credits.
     */
    public function advertisingTopUp(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'sender_number_id' => 'required|exists:sender_numbers,id',
            'amount' => 'required|numeric|min:1000',
            'business_id' => 'nullable|exists:ghorfe_online_lists,id',
            'tracking_id' => 'nullable|string|max:100',
        ]);

        $business = $this->resolveBusiness($request);

        if (!$business) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'کسب‌وکار معتبری یافت نشد.'], 404)
                : redirect()->back()->withErrors('کسب‌وکار معتبری یافت نشد.');
        }

        $senderNumber = SenderNumber::findOrFail($request->input('sender_number_id'));

        try {
            $transaction = $this->financialService->topUpAdvertising(
                $business,
                $senderNumber,
                (float) $request->input('amount'),
                auth()->user(),
                $request->input('tracking_id')
            );

            $msg = sprintf(
                'شارژ پیامک تبلیغاتی خط %s به مبلغ %s ریال با موفقیت انجام شد. شناسه تراکنش: %s',
                $senderNumber->number,
                number_format((float) $request->input('amount')),
                $transaction->tracking_id
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'transaction' => $transaction,
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withErrors($e->getMessage());
        }
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
            $status = $this->balanceGuard->checkOtp($business, $cost);
            return response()->json(['success' => true, 'status' => $status]);
        }

        $senderNumberId = $request->input('sender_number_id');
        $senderNumber = SenderNumber::find($senderNumberId);

        if (!$senderNumber) {
            return response()->json(['success' => false, 'message' => 'شماره فرستنده مشخص نشده است.'], 422);
        }

        $status = $this->balanceGuard->checkAdvertising($business, $senderNumber, $cost);
        return response()->json(['success' => true, 'status' => $status]);
    }
}
