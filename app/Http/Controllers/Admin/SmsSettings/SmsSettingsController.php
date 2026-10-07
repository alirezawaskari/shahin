<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Http\Controllers\Controller;
use App\Models\ContractUsage;
use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
use App\Models\SmsMessageGroup;
use App\Models\SmsMessageTemplate;
use App\Models\SmsPanel;
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

        $stats = [
            'panels_count' => SmsPanel::count(),
            'active_panels_count' => SmsPanel::where('status', 'active')->count(),
            'senders_count' => $business ? $business->senderNumbers()->count() : SenderNumber::count(),
            'otp_sender' => SenderNumber::where('type', 'otp')->first(),
            'adv_senders_count' => $business
                ? $business->senderNumbers()->where('type', 'advertising')->count()
                : SenderNumber::where('type', 'advertising')->count(),
            'templates_count' => SmsMessageTemplate::count(),
            'active_templates_count' => SmsMessageTemplate::where('status', 'active')->count(),
            'groups_count' => SmsMessageGroup::whereNull('parent_id')->count(),
            'subgroups_count' => SmsMessageGroup::whereNotNull('parent_id')->count(),
            'otp_contract' => $this->contractService->getOtpContract(),
            'business_adv_contract' => $business ? $this->contractService->getBusinessAdvertisingContract($business) : null,
            'total_adv_contracts' => ContractUsage::where('location', 'sms_advertising')->active()->count(),
        ];

        return view('Admin.sms.settings.index', compact('stats', 'business'));
    }
}
