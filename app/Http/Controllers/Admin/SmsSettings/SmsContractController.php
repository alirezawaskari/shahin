<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractFolder;
use App\Models\ContractUsage;
use App\Models\GhorfeOnlineList;
use App\Services\SmsSettings\SmsContractService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SmsContractController extends Controller
{
    public function __construct(
        protected SmsContractService $contractService
    ) {
    }

    /**
     * Display SMS contracts management view.
     * Shows global OTP contract and per-business advertising contracts.
     */
    public function index(Request $request): View
    {
        $business = ghorfe();
        $otpContractUsage = $this->contractService->getOtpContract();

        $advertisingContracts = null;
        $businessAdvUsage = null;

        if ($business) {
            $businessAdvUsage = $this->contractService->getBusinessAdvertisingContract($business);
        } else {
            $filters = $request->only(['search', 'per_page']);
            $advertisingContracts = $this->contractService->getAdvertisingContractsList($filters);
        }

        $folders = ContractFolder::options();
        $bankContracts = Contract::latest('id')->take(50)->get();

        return view('Admin.sms.settings.contracts.index', compact(
            'otpContractUsage',
            'businessAdvUsage',
            'advertisingContracts',
            'business',
            'folders',
            'bankContracts'
        ));
    }

    /**
     * Store or attach an SMS contract (OTP global or Advertising per-business).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_type' => 'required|in:otp,advertising',
            'mode' => 'required|in:new,existing',
            'business_id' => 'nullable|exists:ghorfe_online_lists,id',
            'contract_id' => 'required_if:mode,existing|nullable|exists:contracts,id',
            'title' => 'required_if:mode,new|nullable|string|max:191',
            'type' => 'nullable|integer',
            'contract_folder_id' => 'nullable|exists:contract_folders,id',
            'contract_number' => 'nullable|string|max:100',
            'contract_date' => 'nullable|date',
            'content' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'file_path' => 'nullable|string',
        ], [
            'service_type.required' => 'نوع سرویس قرارداد را مشخص کنید.',
            'mode.required' => 'نحوه انتساب قرارداد را مشخص کنید.',
            'contract_id.required_if' => 'قرارداد مورد نظر از بانک قراردادها را انتخاب کنید.',
            'title.required_if' => 'عنوان قرارداد جدید را وارد کنید.',
        ]);

        $serviceType = $validated['service_type'];
        $business = ghorfe();

        // If superAdmin specifies a business for advertising contract:
        if (!$business && !empty($validated['business_id'])) {
            $business = GhorfeOnlineList::find($validated['business_id']);
        }

        try {
            $usage = $this->contractService->saveContract(
                $serviceType,
                $validated,
                $business,
                $request->file('file'),
                $request->input('file_path')
            );
        } catch (ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        }

        $label = ($serviceType === 'otp') ? 'قرارداد سراسری OTP' : 'قرارداد پیامک تبلیغاتی';

        return redirect()->route('admin.sms.settings.contracts.index')
            ->with('success', "{$label} با موفقیت ثبت و فعال گردید.");
    }

    /**
     * Update contract usage details (e.g. contract number or date).
     */
    public function update(Request $request, ContractUsage $contractUsage): RedirectResponse
    {
        $validated = $request->validate([
            'contract_number' => 'nullable|string|max:100',
            'contract_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $contractUsage->update($validated);

        return redirect()->back()->with('success', 'اطلاعات انتساب قرارداد با موفقیت به‌روزرسانی شد.');
    }

    /**
     * Detach contract usage.
     */
    public function destroy(ContractUsage $contractUsage): RedirectResponse
    {
        $this->contractService->detachUsage($contractUsage);

        return redirect()->route('admin.sms.settings.contracts.index')
            ->with('success', 'قرارداد با موفقیت از محل استفاده لغو گردید.');
    }
}
