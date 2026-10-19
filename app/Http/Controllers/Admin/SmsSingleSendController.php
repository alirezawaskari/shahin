<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\Sms\InsufficientBalanceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sms\ForwardMessageRequest;
use App\Http\Requests\Admin\Sms\SendSingleSmsRequest;
use App\Http\Requests\Admin\Sms\StoreContactRequest;
use App\Models\AdvertisingBalance;
use App\Models\GhorfeOnlineList;
use App\Models\OtpBalance;
use App\Models\SenderNumber;
use App\Models\SmsMessage;
use App\Models\SmsMessageGroup;
use App\Models\SmsMessageTemplate;
use App\Models\User;
use App\Services\Sms\SingleSmsSendService;
use App\Services\Sms\SmsAccessTierService;
use App\Services\Sms\SmsConfidentialityGuard;
use App\Services\SmsSettings\SmsCmsContentService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsSingleSendController extends Controller
{
    public function __construct(
        protected SingleSmsSendService $singleSmsSendService,
        protected SmsCmsContentService $cmsContent,
        protected SmsAccessTierService $accessTierService
    ) {
    }

    /**
     * Resolve the current business context with fallback for SuperAdmin.
     */
    protected function resolveBusiness(Request $request): GhorfeOnlineList
    {
        $business = ghorfe();

        if (!$business && (auth()->user()?->isSuperAdmin() || app()->environment('testing'))) {
            $targetBusinessId = $request->input('business_id') ?: $request->input('business');
            $business = $targetBusinessId ? GhorfeOnlineList::find($targetBusinessId) : GhorfeOnlineList::first();
        }

        if (!$business) {
            abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');
        }

        return $business;
    }

    /**
     * Display the Single SMS Send UI.
     */
    public function index(Request $request): View
    {
        $business = $this->resolveBusiness($request);

        // System-wide active OTP sender number (singleton)
        $otpSender = SenderNumber::query()
            ->where('type', 'otp')
            ->where('status', 'active')
            ->first();

        // OTP Balance for current business
        $otpBalance = 0.0;
        if ($business) {
            $otpBalance = (float) (OtpBalance::where('ghorfe_online_list_id', $business->id)->value('balance') ?? 0);
        }

        // Advertising sender numbers assigned to this business with balances
        $advertisingNumbers = collect();
        if ($business) {
            $advertisingNumbers = $business->senderNumbers()
                ->where('sender_numbers.type', 'advertising')
                ->where('sender_numbers.status', 'active')
                ->get()
                ->map(function ($sender) use ($business) {
                    $balance = AdvertisingBalance::where('ghorfe_online_list_id', $business->id)->value('balance') ?? 0;
                    $sender->current_balance = (float) $balance;
                    return $sender;
                });
        }

        // Active SMS templates (scoped to business)
        $templates = SmsMessageTemplate::active()
            ->when(!$this->accessTierService->canUseOtp(auth()->user()),
                fn($q) => $q->where(fn($w) => $w->whereNull('service_type')->orWhere('service_type', '!=', 'otp')))
            ->when($business, fn($q) => $q->where('ghorfe_online_list_id', $business->id))
            ->with('group')
            ->latest()
            ->get();

        // Recent sent messages for reference & forwarding
        $recentMessages = collect();
        if ($business) {
            $recentMessages = SmsMessage::where('ghorfe_online_list_id', $business->id)
                ->with(['senderNumber', 'user'])
                ->latest()
                ->take(15)
                ->get();

            // Confidentiality enforcement (task.md §13): redact the body of
            // any message in a confidential group (مالی/حقوقی) server-side
            // for viewers without view-confidential-sms, consistently with
            // SmsLogController — otherwise the raw text would leak through
            // this screen's table and forward-modal regardless of the UI.
            SmsConfidentialityGuard::redactMany($recentMessages, auth()->user());
        }

        // Lower tiers get no trace of OTP: no sender line, no balance, no option in the form.
        $canUseOtp = $this->accessTierService->canUseOtp(auth()->user());
        if (!$canUseOtp) {
            $otpSender = null;
            $otpBalance = 0.0;
        }

        $regulatoryRulesText = $this->cmsContent->regulatoryRulesText();
        $costPerPage = (float) config('services.sms.cost_per_page', 1200);
        // OTP-capable users keep the full tree (OTP mode unaffected; the form hides non-promotional
        // groups when advertising is selected); everyone else only sees the promotional groups.
        $groups = SmsMessageGroup::query()
            ->when(!$canUseOtp, fn ($q) => $q->promotionalRoots(), fn ($q) => $q->whereNull('parent_id'))
            ->when($business, fn($q) => $q->where('ghorfe_online_list_id', $business->id))
            ->with(['subgroups' => function ($q) use ($business) {
                $q->when($business, fn($sq) => $sq->where('ghorfe_online_list_id', $business->id));
            }])
            ->orderBy('name')
            ->get();

        return view('Admin.sms.single.index', compact(
            'business',
            'otpSender',
            'otpBalance',
            'canUseOtp',
            'advertisingNumbers',
            'templates',
            'recentMessages',
            'regulatoryRulesText',
            'costPerPage',
            'groups'
        ));
    }

    /**
     * Handle single SMS dispatch.
     */
    public function send(SendSingleSmsRequest $request): JsonResponse|RedirectResponse
    {
        $business = $this->resolveBusiness($request);

        if (!$business) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'کسب‌وکار معتبری برای ارسال پیامک یافت نشد.',
                ], 422);
            }
            return redirect()->back()->withErrors(['business' => 'کسب‌وکار معتبری برای ارسال پیامک یافت نشد.']);
        }

        try {
            $message = $this->singleSmsSendService->send($request->validated(), $business);

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'پیامک با موفقیت ارسال شد.',
                    'tracking_id' => $message->tracking_id,
                    'sms' => $message,
                ]);
            }

            return redirect()->back()->with('success', 'پیامک با موفقیت ارسال شد. شناسه رهگیری: ' . $message->tracking_id);
        } catch (InsufficientBalanceException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'error_type' => 'insufficient_balance',
                    'message' => $e->getMessage(),
                    'current_balance' => $e->currentBalance,
                    'required_amount' => $e->requiredAmount,
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors(['balance' => $e->getMessage()]);
        } catch (DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Real-time calculation and preview endpoint.
     */
    public function preview(Request $request): JsonResponse
    {
        $business = $this->resolveBusiness($request);
        $text = (string) $request->input('message', '');
        $serviceType = (string) $request->input('service_type', 'advertising');
        abort_if($serviceType === 'otp' && !$this->accessTierService->canUseOtp(auth()->user()), 403, 'دسترسی به سرویس پیامک اعتبارسنجی (OTP) برای سطح دسترسی شما مجاز نیست.');
        $userId = $request->input('user_id');
        $senderNumberId = $request->input('sender_number_id');

        $recipient = $userId ? User::find($userId) : null;
        $resolvedText = $this->singleSmsSendService->resolvePlaceholders($text, $request->input('context', []), $recipient);

        $segmentation = $this->singleSmsSendService->calculateSegmentation($resolvedText, $serviceType);
        $cost = $this->singleSmsSendService->calculateCost($segmentation['page_count'], $segmentation['is_unicode']);

        $currentBalance = 0.0;
        if ($business) {
            try {
                $senderNumber = $this->singleSmsSendService->resolveSenderNumber($serviceType, $business, $senderNumberId ? (int) $senderNumberId : null);
                $balanceInfo = $this->singleSmsSendService->getBalance($serviceType, $business);
                $currentBalance = $balanceInfo['balance'];
            } catch (\Throwable) {
                // If sender number is not resolved yet, fallback
            }
        }

        return response()->json([
            'status' => 'success',
            'raw_text' => $text,
            'resolved_text' => $segmentation['text'],
            'character_count' => $segmentation['character_count'],
            'page_count' => $segmentation['page_count'],
            'remaining_characters' => $segmentation['remaining_characters'],
            'is_unicode' => $segmentation['is_unicode'],
            'encoding' => $segmentation['encoding'],
            'appended_cancel' => $segmentation['appended_cancel'],
            'cost' => $cost,
            'current_balance' => $currentBalance,
            'balance_after' => max(0, $currentBalance - $cost),
            'has_sufficient_balance' => ($cost > 0) ? ($currentBalance >= $cost) : true,
            'balance_deficit' => max(0, $cost - $currentBalance),
            'is_low_balance' => ($currentBalance < 50000) || ($cost > $currentBalance),
            'business_id' => $business ? $business->id : null,
            'business_name' => $business ? $business->title : null,
            'service_type' => $serviceType,
            'sender_number' => $senderNumber ? $senderNumber->number : null,
            'sender_number_id' => $senderNumber ? $senderNumber->id : null,
        ]);
    }

    /**
     * Contact search endpoint.
     */
    public function searchContacts(Request $request): JsonResponse
    {
        $query = (string) $request->input('q', '');
        $contacts = $this->singleSmsSendService->searchContacts($query);

        return response()->json([
            'status' => 'success',
            'data' => $contacts,
        ]);
    }

    /**
     * Inline contact creation without leaving the page.
     */
    public function storeContact(StoreContactRequest $request): JsonResponse
    {
        $contact = $this->singleSmsSendService->storeContact($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'مخاطب با موفقیت ثبت شد.',
            'contact' => [
                'id' => $contact->id,
                'name' => trim(($contact->name ?? '') . ' ' . ($contact->family ?? '')) ?: 'بدون نام',
                'first_name' => $contact->name,
                'family' => $contact->family,
                'phone' => $contact->phone,
            ],
        ]);
    }

    /**
     * Forward / refer message to another contact.
     */
    public function forward(ForwardMessageRequest $request): JsonResponse|RedirectResponse
    {
        $business = $this->resolveBusiness($request);
        $originalMessage = SmsMessage::findOrFail($request->original_message_id);

        try {
            $newMessage = $this->singleSmsSendService->forwardMessage(
                $originalMessage,
                $request->recipient_phone,
                $request->user_id,
                $request->referrer_id ?? auth()->id(),
                $request->referred_at ? Carbon::parse($request->referred_at) : now(),
                $business
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'پیامک با موفقیت ارجاع داده شد.',
                    'tracking_id' => $newMessage->tracking_id,
                    'sms' => $newMessage,
                ]);
            }

            return redirect()->back()->with('success', 'پیامک با موفقیت ارجاع داده شد. شناسه رهگیری: ' . $newMessage->tracking_id);
        } catch (InsufficientBalanceException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'error_type' => 'insufficient_balance',
                    'message' => $e->getMessage(),
                ], 422);
            }
            return redirect()->back()->withErrors(['balance' => $e->getMessage()]);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
