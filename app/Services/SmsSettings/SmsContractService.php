<?php

namespace App\Services\SmsSettings;

use App\Helpers\MediaLibraryHelper;
use App\Models\Contract;
use App\Models\ContractFolder;
use App\Models\ContractHistory;
use App\Models\ContractUsage;
use App\Models\GhorfeOnlineList;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SmsContractService
{
    /**
     * Get or create the dedicated folder for SMS contracts in the contract bank.
     */
    public function getOrCreateSmsFolder(): ContractFolder
    {
        return ContractFolder::firstOrCreate(
            ['title' => 'قراردادهای پیامک'],
            [
                'description' => 'پوشه پیش‌فرض قراردادها و تعهدنامه‌های سرویس پیامک',
                'position' => 1,
                'is_active' => true,
            ]
        );
    }

    /**
     * Get the active global OTP contract usage (template/master contract).
     */
    public function getOtpContract(): ?ContractUsage
    {
        return ContractUsage::query()
            ->location('sms_otp')
            ->global()
            ->active()
            ->with(['contract.folder', 'contract.media'])
            ->first();
    }

    /**
     * Get the active global Advertising contract usage (template/master contract).
     */
    public function getGlobalAdvertisingContract(): ?ContractUsage
    {
        return ContractUsage::query()
            ->location('sms_advertising')
            ->global()
            ->active()
            ->with(['contract.folder', 'contract.media'])
            ->first();
    }

    /**
     * Get the specific OTP contract usage for a single business.
     */
    public function getBusinessOtpContract(GhorfeOnlineList $business): ?ContractUsage
    {
        return ContractUsage::query()
            ->location('sms_otp')
            ->for(GhorfeOnlineList::class, $business->id)
            ->active()
            ->with(['contract.folder', 'contract.media'])
            ->first();
    }

    /**
     * Get the advertising contract usage for a single business.
     */
    public function getBusinessAdvertisingContract(GhorfeOnlineList $business): ?ContractUsage
    {
        return ContractUsage::query()
            ->location('sms_advertising')
            ->for(GhorfeOnlineList::class, $business->id)
            ->active()
            ->with(['contract.folder', 'contract.media'])
            ->first();
    }

    /**
     * Get all businesses with their OTP and Advertising contract status.
     */
    public function getAdvertisingContractsList(array $filters = []): LengthAwarePaginator
    {
        $query = GhorfeOnlineList::query()->latest('id');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($filters['per_page'] ?? 15);

        $businessIds = $paginator->pluck('id')->toArray();

        $advUsages = ContractUsage::query()
            ->location('sms_advertising')
            ->where('usable_type', GhorfeOnlineList::class)
            ->whereIn('usable_id', $businessIds)
            ->active()
            ->with(['contract.folder', 'contract.media'])
            ->get()
            ->keyBy('usable_id');

        $otpUsages = ContractUsage::query()
            ->location('sms_otp')
            ->where('usable_type', GhorfeOnlineList::class)
            ->whereIn('usable_id', $businessIds)
            ->active()
            ->with(['contract.folder', 'contract.media'])
            ->get()
            ->keyBy('usable_id');

        foreach ($paginator->items() as $business) {
            $business->advertising_contract_usage = $advUsages->get($business->id);
            $business->otp_contract_usage = $otpUsages->get($business->id);
        }

        return $paginator;
    }

    /**
     * Save an SMS contract or template (OTP or Advertising).
     *
     * Legal immutability rule: Existing contracts are NEVER mutated in place.
     * Super Admin can:
     * 1) Create a template in the contract bank (and optionally set it as the active global default for the panel).
     * 2) Assign a template from the bank to a business (or set as panel default).
     * 3) Create a custom one-off contract and assign it directly to a business (without saving it to the template folder unless `save_as_template` is checked).
     */
    public function saveContract(
        string $serviceType,
        array $data,
        ?GhorfeOnlineList $business = null,
        ?UploadedFile $file = null,
        ?string $filePath = null
    ): ContractUsage|Contract {
        $location = ($serviceType === 'otp') ? 'sms_otp' : 'sms_advertising';
        $isGlobalTemplate = !empty($data['is_global_template'])
            || !empty($data['is_global_advertising'])
            || (!$business && $serviceType === 'otp' && !array_key_exists('is_global_template', $data) && !array_key_exists('save_as_template', $data) && !array_key_exists('template_only', $data));
        $saveAsTemplate = !empty($data['save_as_template']) || $isGlobalTemplate;
        $templateOnly = !empty($data['template_only']) || ($saveAsTemplate && !$business && !$isGlobalTemplate);

        $ownerType = $isGlobalTemplate ? null : ($business ? GhorfeOnlineList::class : null);
        $ownerId = $isGlobalTemplate ? null : $business?->id;

        if (!$saveAsTemplate && !$ownerId && !$isGlobalTemplate) {
            throw ValidationException::withMessages([
                'business_id' => ['باید حداقل یکی از دو گزینه «ذخیره به‌عنوان الگو» یا «انتساب به کسب‌وکار» انتخاب شود.'],
            ]);
        }

        if (!$templateOnly && $serviceType === 'advertising' && !$ownerId && !$isGlobalTemplate) {
            throw ValidationException::withMessages([
                'business_id' => ['انتخاب کسب‌وکار برای انتساب قرارداد پیامک تبلیغاتی الزامی است.'],
            ]);
        }

        return DB::transaction(function () use ($location, $ownerType, $ownerId, $data, $file, $filePath, $templateOnly, $saveAsTemplate) {
            $contractId = null;

            if (($data['mode'] ?? 'new') === 'existing' && !empty($data['contract_id'])) {
                $contractId = (int) $data['contract_id'];
                $contract = Contract::findOrFail($contractId);
            } else {
                // Only place in the SMS Templates folder if saving as a reusable template;
                // one-off custom contracts created directly for an assignee stay uncategorised (folder = null).
                $folder = null;
                if ($saveAsTemplate) {
                    $folder = !empty($data['contract_folder_id'])
                        ? ContractFolder::find($data['contract_folder_id'])
                        : $this->getOrCreateSmsFolder();
                }

                $contract = Contract::create([
                    'title' => $data['title'] ?? ('قرارداد پیامک ' . ($location === 'sms_otp' ? 'خدماتی' : 'تبلیغاتی')),
                    'type' => $data['type'] ?? 1,
                    'status' => $data['status'] ?? 1,
                    'content' => $data['content'] ?? null,
                    'contract_folder_id' => $folder?->id,
                ]);

                $contractId = $contract->id;
            }

            // Handle file upload for newly created contract/template
            if (($data['mode'] ?? 'new') !== 'existing') {
                if ($file instanceof UploadedFile) {
                    $contract->addMedia($file)
                        ->toMediaCollection('file');
                } elseif (!empty($filePath)) {
                    MediaLibraryHelper::attachTo($contract, $filePath, 'file');
                }

                if (Auth::id()) {
                    ContractHistory::create([
                        'contract_id' => $contract->id,
                        'user_id' => Auth::id(),
                        'file' => $contract->file,
                    ]);
                }
            }

            if ($templateOnly) {
                return $contract;
            }

            // Detach previous active contract for this location & owner
            $this->detachPrevious($location, $ownerType, $ownerId);

            // Create new ContractUsage
            return ContractUsage::create([
                'contract_id' => $contractId,
                'usable_type' => $ownerType,
                'usable_id' => $ownerId,
                'location' => $location,
                'contract_number' => $data['contract_number'] ?? null,
                'contract_date' => $data['contract_date'] ?? now()->toDateString(),
                'is_active' => true,
                'position' => 0,
            ]);
        });
    }

    /**
     * Detach / delete a contract usage.
     */
    public function detachUsage(ContractUsage $usage): bool
    {
        return (bool) $usage->forceDelete();
    }

    /**
     * Remove previous active usages for the given location and owner.
     */
    protected function detachPrevious(string $location, ?string $ownerType, ?int $ownerId): void
    {
        ContractUsage::query()
            ->location($location)
            ->when($ownerType, fn ($q) => $q->for($ownerType, $ownerId))
            ->when(!$ownerType, fn ($q) => $q->global())
            ->forceDelete();
    }
}
