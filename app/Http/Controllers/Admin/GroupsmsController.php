<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AdvertisingBalance;
use App\Models\GhorfeOnlineList;
use App\Models\Groupsms;
use App\Models\OtpBalance;
use App\Models\SenderNumber;
use App\Models\Service;
use App\Models\SmsMessage;
use App\Models\SmsMessageTemplate;
use App\Services\Sms\SingleSmsSendService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GroupsmsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user?->isSuperAdmin() ?? false;
        $business = ghorfe() ?? GhorfeOnlineList::first();

        if ($isSuperAdmin && $request->filled('business_id')) {
            $business = GhorfeOnlineList::find($request->input('business_id')) ?: $business;
        }

        $all_businesses = $isSuperAdmin ? GhorfeOnlineList::orderBy('title')->get() : collect();

        $query = Groupsms::with(['group', 'services', 'phone_list', 'smsMessage'])->latest();

        if (!$isSuperAdmin && $business) {
            $query->where('ghorfe_online_list_id', $business->id);
        } elseif ($isSuperAdmin && $request->filled('business_id')) {
            $query->where('ghorfe_online_list_id', $business->id);
        }

        $groupsms = $query->get();

        $canViewConfidential = \App\Services\Sms\SmsConfidentialityGuard::canViewConfidential($user);

        return view('Admin.groupsms.all', compact('groupsms', 'business', 'all_businesses', 'canViewConfidential'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $services = Service::filter()->get();
        $business = ghorfe() ?? GhorfeOnlineList::first();
        $user = auth()->user();

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
            ->when($business, fn($q) => $q->where('ghorfe_online_list_id', $business->id))
            ->with('group')
            ->latest()
            ->get();

        // Filter helper options
        $roles = class_exists(\App\Models\Role::class) && \Illuminate\Support\Facades\Schema::hasTable('roles') ? \App\Models\Role::all() : collect();
        $businesses = \App\Helpers\FilterHelper::ghorfeFilterOptions();
        $categories = class_exists(\App\Models\Category::class) && \Illuminate\Support\Facades\Schema::hasTable('categories') ? \App\Models\Category::whereNull('parent_id')->orWhere('depth', 1)->get() : collect();
        $provinces = class_exists(\App\Models\Province::class) && \Illuminate\Support\Facades\Schema::hasTable('provinces') ? \App\Models\Province::all() : collect();

        // Recent campaigns
        $recentCampaigns = collect();
        if ($business) {
            $recentCampaigns = \App\Models\SmsCampaign::where('ghorfe_online_list_id', $business->id)
                ->with(['senderNumber', 'template'])
                ->latest()
                ->take(10)
                ->get();
        }

        $userTier = app(\App\Services\Sms\SmsAccessTierService::class)->effectiveTier($user);
        $regulatoryRulesText = app(\App\Services\SmsSettings\SmsCmsContentService::class)->regulatoryRulesText();
        $groups = \App\Models\SmsMessageGroup::whereNull('parent_id')
            ->when($business, fn($q) => $q->where('ghorfe_online_list_id', $business->id))
            ->with(['subgroups' => function ($q) use ($business) {
                $q->when($business, fn($sq) => $sq->where('ghorfe_online_list_id', $business->id));
            }])
            ->orderBy('name')
            ->get();

        return view('Admin.groupsms.create', compact(
            'services',
            'business',
            'user',
            'userTier',
            'advertisingNumbers',
            'templates',
            'roles',
            'businesses',
            'categories',
            'provinces',
            'recentCampaigns',
            'regulatoryRulesText',
            'groups'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'message' => 'required',
            'service_id' => 'required',
            'send_at' => 'required',
            'flag' => 'required',
        ]);

        $inputs = $request->all();
        unset($inputs['service_id']);
        unset($inputs['date']);

        $business = ghorfe() ?? GhorfeOnlineList::first();
        $groupId = $request->input('sms_message_group_id') ?: null;
        $classification = $request->input('classification') ?: 'regular';
        if ($groupId) {
            $grp = \App\Models\SmsMessageGroup::find($groupId);
            if ($grp && $grp->isConfidential()) {
                $classification = 'classified';
            }
        }

        $groupsms = Groupsms::create(array_merge($inputs, [
            'send_at' => Carbon::createFromTimestamp($inputs['send_at'] / 1000)->toDateString(),
            'type' => 'groupsService',
            'flag' => true,
            'ghorfe_online_list_id' => $business?->id,
            'sms_message_group_id' => $groupId,
            'classification' => $classification,
        ]));

        if ($request->input('service_id')[0] != 'all') {
            $groupsms->services()->sync($request->input("service_id"));
        }

        return redirect(route('groupsms.index'))->with([
            'create' => 'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Groupsms $groupsms
     * @return \Illuminate\Http\Response
     */
    public function show(Groupsms $groupsm)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Groupsms $groupsms
     * @return \Illuminate\Http\Response
     */
    public function edit(Groupsms $groupsm)
    {
        $services = Service::filter()->get();
        return view('Admin.groupsms.edit', compact('groupsm', 'services'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Groupsms $groupsms
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Groupsms $groupsm)
    {
        $this->validate($request, [
            'title' => 'required',
            'message' => 'required',
            'service_id' => 'required',
        ]);

        $inputs = $request->all();
        unset($inputs['service_id']);
        unset($inputs['date']);

        if (strpos($request->input('send_at'), '-')) {
            $groupsm->update($inputs);
        } else {
            $groupsm->update(array_merge($inputs, ['send_at' => Carbon::createFromTimestamp($inputs['send_at'] / 1000)->toDateString()]));
        }

        if ($request->input('service_id')[0] == 'all') {
            $groupsm->services()->detach();
        } else {
            $groupsm->services()->sync($request->input("service_id"));
        }

        return redirect(route('groupsms.index'))->with([
            'update' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Groupsms $groupsms
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Groupsms $groupsm)
    {
        $groupsm->delete();

        return redirect(route('groupsms.index'))->with([
            'delete' => 'success'
        ]);
    }


    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function single()
    {
        $business = ghorfe() ?? GhorfeOnlineList::first();

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

        // Active SMS templates
        $templates = SmsMessageTemplate::active()
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
        }

        $regulatoryRulesText = app(\App\Services\SmsSettings\SmsCmsContentService::class)->regulatoryRulesText();
        $groups = \App\Models\SmsMessageGroup::whereNull('parent_id')->with('subgroups')->orderBy('name')->get();

        return view('Admin.groupsms.single', compact(
            'business',
            'otpSender',
            'otpBalance',
            'advertisingNumbers',
            'templates',
            'recentMessages',
            'regulatoryRulesText',
            'groups'
        ));
    }


    /**
     * @param Groupsms $groupsms
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function singleEdit(Groupsms $groupsms)
    {
        return view('Admin.groupsms.singleUpdate', compact('groupsms'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function singleStore(Request $request, SingleSmsSendService $singleSmsSendService)
    {
        $phone = $request->input('recipient_phone') ?: $request->input('phones');
        $message = $request->input('message');

        if (!$phone || !$message) {
            return back()->withInput()->withErrors(['message' => 'شماره گیرنده و متن پیام الزامی است.']);
        }

        try {
            $serviceType = $request->input('service_type', 'normal');
            $senderNumberId = $request->input('sender_number_id');
            $business = ghorfe() ?? GhorfeOnlineList::first();

            $sms = $singleSmsSendService->send([
                'recipient_phone' => $phone,
                'content' => $message,
                'service_type' => $serviceType,
                'sender_number_id' => $senderNumberId,
                'user_id' => $request->input('user_id'),
                'sms_message_group_id' => $request->input('sms_message_group_id'),
                'classification' => $request->input('classification'),
            ], $business, auth()->user());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'پیامک با موفقیت ارسال شد.',
                    'sms' => $sms,
                ]);
            }

            return redirect(route('groupsms.index'))->with([
                'create' => 'success',
                'success' => 'پیامک با موفقیت ارسال شد.',
            ]);
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sendConsent(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'message' => 'required',
            'phones' => 'required',
        ]);

        $phones = $request->input('phones');
        $message = $request->input('message');
        Helper::sms($phones , $message);
        //save
        $business = ghorfe() ?? GhorfeOnlineList::first();
        $inputs = $request->all();
        $groupsms = Groupsms::create(array_merge($inputs, [
            'type' => 'single',
            'status' => 2,
            'flag' => true,
            'ghorfe_online_list_id' => $business?->id,
            'classification' => 'regular',
        ]));

        return redirect(route('groupsms.index'))->with([
            'create' => 'success'
        ]);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function groupsmsPhone(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'message' => 'required',
            'phone_list_id' => 'required',
            'send_at' => 'required',
        ]);

        $inputs = $request->all();
        unset($inputs['date2']);
        unset($inputs['phone_list_id']);

        $business = ghorfe() ?? GhorfeOnlineList::first();
        $groupId = $request->input('sms_message_group_id') ?: null;
        $classification = $request->input('classification') ?: 'regular';
        if ($groupId) {
            $grp = \App\Models\SmsMessageGroup::find($groupId);
            if ($grp && $grp->isConfidential()) {
                $classification = 'classified';
            }
        }
        $inputs['ghorfe_online_list_id'] = $business?->id;
        $inputs['sms_message_group_id'] = $groupId;
        $inputs['classification'] = $classification;

        if ($request->input('phone_list_id') == 'default') {
            Groupsms::create(array_merge($inputs, ['send_at' => Carbon::createFromTimestamp($inputs['send_at'] / 1000)->toDateString(), 'type' => 'groupsPhone']));
        } else {
            Groupsms::create(array_merge($inputs, ['send_at' => Carbon::createFromTimestamp($inputs['send_at'] / 1000)->toDateString(), 'phone_list_id' => $request->input('phone_list_id'), 'type' => 'groupsPhone']));
        }

        return redirect(route('groupsms.index'))->with([
            'create' => 'success'
        ]);

    }

    /**
     * @param Request $request
     * @param Groupsms $groupsms
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function groupsmsPhoneUpdate(Request $request, Groupsms $groupsms)
    {
        $this->validate($request, [
            'title' => 'required',
            'message' => 'required',
            'phone_list_id' => 'required',
            'send_at' => 'required',
        ]);

        $inputs = $request->all();
        unset($inputs['date']);
        unset($inputs['phone_list_id']);


        if (strpos($request->input('send_at'), '-')) {
            if ($request->input('phone_list_id') == 'default') {
                $groupsms->update(array_merge(['phone_list_id' => null]));
            } else {
                $groupsms->update(array_merge($inputs, ['phone_list_id' => $request->input('phone_list_id')]));
            }
        } else {
            if ($request->input('phone_list_id') == 'default') {
                $groupsms->update(array_merge($inputs, ['send_at' => Carbon::createFromTimestamp($inputs['send_at'] / 1000)->toDateString() ,'phone_list_id' => null]));
            } else {
                $groupsms->update(array_merge($inputs, ['send_at' => Carbon::createFromTimestamp($inputs['send_at'] / 1000)->toDateString(), 'phone_list_id' => $request->input('phone_list_id')]));
            }
        }

        return redirect(route('groupsms.index'))->with([
            'update' => 'success'
        ]);
    }
}
