<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\GhorfeOnlineList;
use App\Models\Groupsms;
use App\Models\Service;
use App\Models\Setting;
use App\Models\SmsTemplate;
use App\Services\Sms\SingleSmsSendService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GroupsmsController extends Controller
{
    /**
     * The legacy list page is retired from the UI: every redirect lands on the SMS reports page
     * of the user's tier (same choice as the sidebar «گزارش های پیامک» item).
     */
    protected function reportsRouteName(): string
    {
        return app(\App\Services\Sms\SmsAccessTierService::class)->canUseOtp(auth()->user())
            ? 'admin.sms.reports.otp.index'
            : 'admin.sms.reports.advertising.index';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user?->isSuperAdmin() ?? false;
        $business = ghorfe();

        if ($isSuperAdmin) {
            if ($request->filled('business_id') && $request->input('business_id') !== 'all') {
                $business = GhorfeOnlineList::find($request->input('business_id'));
            } else {
                $business = null;
            }
        } elseif (!$business) {
            $business = GhorfeOnlineList::first();
        }

        $all_businesses = $isSuperAdmin ? GhorfeOnlineList::orderBy('title')->get() : collect();

        $query = Groupsms::with(['services', 'phone_list'])->latest();

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
        return redirect()->route('admin.sms.bulk.index');

        $services = Service::filter()->get();
        $business = ghorfe() ?? GhorfeOnlineList::first();
        $user = auth()->user();

        // Advertising sender numbers assigned to this business with balances
        $advertisingNumbers = collect();
        if ($business) {
            $balance = (float) ($business->advertising_balance ?? 0);
            $advertisingNumbers = $business->senderNumbers()
                ->where('type', 'advertising')
                ->where('status', 'active')
                ->get()
                ->map(function ($sender) use ($balance) {
                    $sender->current_balance = $balance;
                    return $sender;
                });
        }

        if ($advertisingNumbers->isEmpty()) {
            $defaultSender = Setting::where('key', 'sms_sender_number')->first()
                ?? Setting::where('key', 'iranSms_linenumber')->first();
            if ($defaultSender) {
                $defaultSender->current_balance = (float) ($business?->advertising_balance ?? 0);
                $advertisingNumbers = collect([$defaultSender]);
            }
        }

        // Active SMS templates (scoped to business)
        $templates = SmsTemplate::active()
            ->when($business, fn($q) => $q->where('ghorfe_online_list_id', $business->id))
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
            $recentCampaigns = Groupsms::campaigns()
                ->where('ghorfe_online_list_id', $business->id)
                ->latest()
                ->take(10)
                ->get();
        }

        $userTier = app(\App\Services\Sms\SmsAccessTierService::class)->effectiveTier($user);
        $regulatoryRulesText = app(\App\Services\SmsSettings\SmsCmsContentService::class)->regulatoryRulesText();
        $groups = Setting::promotional()
            ->when($business, fn($q) => $q->where('settingable_id', $business->id))
            ->get()
            ->filter(fn($s) => empty($s->parent_id))
            ->sortBy('name')
            ->values();

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
        if (!Setting::isPromotionalId($groupId)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'sms_message_group_id' => 'دسته‌بندی موضوعی انتخاب‌شده برای پیامک تبلیغاتی مجاز نیست.',
            ]);
        }
        $classification = 'regular';

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

        return redirect(route($this->reportsRouteName()))->with([
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

        return redirect(route($this->reportsRouteName()))->with([
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

        return redirect(route($this->reportsRouteName()))->with([
            'delete' => 'success'
        ]);
    }


    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function single()
    {
        return redirect()->route('admin.sms.single.index');
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

            return redirect(route($this->reportsRouteName()))->with([
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

        return redirect(route($this->reportsRouteName()))->with([
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
        if (!Setting::isPromotionalId($groupId)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'sms_message_group_id' => 'دسته‌بندی موضوعی انتخاب‌شده برای پیامک تبلیغاتی مجاز نیست.',
            ]);
        }
        $classification = 'regular';
        $inputs['ghorfe_online_list_id'] = $business?->id;
        $inputs['sms_message_group_id'] = $groupId;
        $inputs['classification'] = $classification;

        if ($request->input('phone_list_id') == 'default') {
            Groupsms::create(array_merge($inputs, ['send_at' => Carbon::createFromTimestamp($inputs['send_at'] / 1000)->toDateString(), 'type' => 'groupsPhone']));
        } else {
            Groupsms::create(array_merge($inputs, ['send_at' => Carbon::createFromTimestamp($inputs['send_at'] / 1000)->toDateString(), 'phone_list_id' => $request->input('phone_list_id'), 'type' => 'groupsPhone']));
        }

        return redirect(route($this->reportsRouteName()))->with([
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

        return redirect(route($this->reportsRouteName()))->with([
            'update' => 'success'
        ]);
    }
}
