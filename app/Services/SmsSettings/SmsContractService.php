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
     * Get the active global OTP contract usage and contract.
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
     * Get all businesses with their advertising contract status.
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

        // Load active advertising contract usage for each business
        $businessIds = $paginator->pluck('id')->toArray();
        $usages = ContractUsage::query()
            ->location('sms_advertising')
            ->where('usable_type', GhorfeOnlineList::class)
            ->whereIn('usable_id', $businessIds)
            ->active()
            ->with(['contract.folder', 'contract.media'])
            ->get()
            ->keyBy('usable_id');

        foreach ($paginator->items() as $business) {
            $business->advertising_contract_usage = $usages->get($business->id);
        }

        return $paginator;
    }

    /**
     * Save an SMS contract (Global OTP or Per-Business Advertising).
     *
     * @param string $serviceType 'otp' or 'advertising'
     * @param array $data Form data
     * @param GhorfeOnlineList|null $business
     * @param UploadedFile|null $file
     * @param string|null $filePath
     * @return ContractUsage
     */
    public function saveContract(
        string $serviceType,
        array $data,
        ?GhorfeOnlineList $business = null,
        ?UploadedFile $file = null,
        ?string $filePath = null
    ): ContractUsage {
        $location = ($serviceType === 'otp') ? 'sms_otp' : 'sms_advertising';
        $ownerType = ($serviceType === 'otp') ? null : GhorfeOnlineList::class;
        $ownerId = ($serviceType === 'otp') ? null : $business?->id;

        if ($serviceType === 'advertising' && !$ownerId && empty($data['is_global_advertising'])) {
            throw ValidationException::withMessages([
                'business_id' => ['انتخاب کسب‌وکار برای قرارداد پیامک تبلیغاتی الزامی است.'],
            ]);
        }

        return DB::transaction(function () use ($location, $ownerType, $ownerId, $data, $file, $filePath) {
            $contractId = null;

            if (($data['mode'] ?? 'new') === 'existing' && !empty($data['contract_id'])) {
                $contractId = (int) $data['contract_id'];
                $contract = Contract::findOrFail($contractId);

                // If edits to existing contract are submitted:
                if (!empty($data['edit_title'])) {
                    $contract->update([
                        'title' => $data['edit_title'],
                        'content' => $data['edit_content'] ?? $contract->content,
                    ]);
                }
            } else {
                // Create new Contract in contract bank
                $folder = !empty($data['contract_folder_id'])
                    ? ContractFolder::find($data['contract_folder_id'])
                    : $this->getOrCreateSmsFolder();

                $contract = Contract::create([
                    'title' => $data['title'] ?? ('قرارداد پیامک ' . ($location === 'sms_otp' ? 'خدماتی' : 'تبلیغاتی')),
                    'type' => $data['type'] ?? 1,
                    'status' => $data['status'] ?? 1,
                    'content' => $data['content'] ?? null,
                    'contract_folder_id' => $folder?->id,
                ]);

                $contractId = $contract->id;
            }

            // Handle file upload
            if ($file instanceof UploadedFile) {
                $contract->addMedia($file)
                    ->toMediaCollection('file');
            } elseif (!empty($filePath)) {
                MediaLibraryHelper::attachTo($contract, $filePath, 'file');
            }

            // Record history
            if (Auth::id()) {
                ContractHistory::create([
                    'contract_id' => $contract->id,
                    'user_id' => Auth::id(),
                    'file' => $contract->file,
                ]);
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
