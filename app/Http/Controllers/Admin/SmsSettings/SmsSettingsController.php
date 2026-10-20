<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Http\Controllers\Controller;
use App\Models\ContractUsage;
use App\Models\GhorfeOnlineList;
use App\Models\Setting;
use App\Models\SmsTemplate;
use App\Services\SmsSettings\SmsContractService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsSettingsController extends Controller
{
    public function __construct(
        protected SmsContractService $contractService
    ) {
    }

    /**
     * Display SMS Settings Hub overview dashboard.
     */
    public function index(Request $request): View
    {
        $business = ghorfe();

        if (!$business && (auth()->user()?->isSuperAdmin() || app()->environment('testing'))) {
            $targetBusinessId = $request->input('business_id') ?: $request->input('business');
            $business = $targetBusinessId ? GhorfeOnlineList::find($targetBusinessId) : null;
        }

        $all_businesses = auth()->user()?->isSuperAdmin() ? GhorfeOnlineList::select('id', 'title')->get() : collect();

        $stats = [
            'panels_count' => Setting::where('key', 'sms_panel')->count(),
            'active_panels_count' => Setting::where('key', 'sms_panel')->where('status', 'active')->count(),
            'senders_count' => $business ? $business->senderNumbers()->count() : Setting::where('key', 'sms_sender_number')->count(),
            'otp_sender' => Setting::otp()->first(),
            'adv_senders_count' => $business
                ? $business->senderNumbers()->where('type', 'advertising')->count()
                : Setting::advertising()->count(),
            'templates_count' => SmsTemplate::when($business, fn($q) => $q->where('ghorfe_online_list_id', $business->id))->count(),
            'active_templates_count' => SmsTemplate::active()->when($business, fn($q) => $q->where('ghorfe_online_list_id', $business->id))->count(),
            'groups_count' => Setting::where('key', 'sms_message_group')->when($business, fn($q) => $q->where('settingable_id', $business->id))->get()->filter(fn($s) => empty($s->parent_id))->count(),
            'subgroups_count' => Setting::where('key', 'sms_message_group')->when($business, fn($q) => $q->where('settingable_id', $business->id))->get()->filter(fn($s) => !empty($s->parent_id))->count(),
            'otp_contract' => $this->contractService->getOtpContract(),
            'business_adv_contract' => $business ? $this->contractService->getBusinessAdvertisingContract($business) : null,
            'total_adv_contracts' => ContractUsage::where('location', 'sms_advertising')->active()->count(),
        ];

        return view('Admin.sms.settings.index', compact('stats', 'business', 'all_businesses'));
    }
}
