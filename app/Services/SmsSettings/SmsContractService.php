<?php

namespace App\Services\SmsSettings;

use App\Exceptions\ContractMediaException;
use App\Helpers\MediaLibraryHelper;
use App\Models\Contract;
use App\Models\ContractFolder;
use App\Models\ContractHistory;
use App\Models\ContractUsage;
use App\Models\GhorfeOnlineList;
use App\Services\V1\Admin\ContractMediaService;
use App\Services\V1\Admin\ContractResolver;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * SMS side of the contract module. The contracts, their files and their assignments are the
 * central ones (Contract, ContractUsage, ContractResolver). This class only adds the two SMS
 * specifics: the panel names (sms_otp, sms_advertising) and the client files of a business.
 *
 * A panel's general contract is the central location-wide usage (usable NULL at that location).
 * A business's own contract is the central usage with the business as owner.
 */
class SmsContractService
{
    public function __construct(
        protected ContractMediaService $contractMedia,
        protected ContractResolver $resolver,
        protected SmsContractFilesService $files
    ) {
    }

    /** The general contract of the OTP panel (location-wide usage). */
    public function getOtpContract(): ?ContractUsage
    {
        return $this->resolver->forLocation('sms_otp');
    }

    /** The general contract of the advertising panel (location-wide usage). */
    public function getGlobalAdvertisingContract(): ?ContractUsage
    {
        return $this->resolver->forLocation('sms_advertising');
    }

    /**
     * Staff files that apply to a usage: the usage's own (legacy) files, the contract's, and the
     * staff files kept on the business before the central module. Nothing is copied; all are read.
     */
    public function staffFilesFor(?ContractUsage $usage): Collection
    {
        if (! $usage) {
            return collect();
        }

        $own = $usage->exists ? $usage->getMedia('staff_attachments') : collect();
        $fromContract = $usage->contract ? $usage->contract->getMedia('staff_attachments') : collect();
        $legacy = $usage->usable instanceof GhorfeOnlineList
            ? $usage->usable->getMedia('sms_contract_attachments_staff')
            : collect();

        return $own->merge($fromContract)->merge($legacy)->values();
    }

    /**
     * Files of one contract for the SMS contract tables and the view modal: the staff files,
     * then the business's client files, each with its review state and the approve / reject URLs
     * (SMS routes, same guard as this page).
     *
     * $location is kept for the views, which pass the panel; the client files are the business's own.
     *
     * @return array<int, array<string, mixed>>
     */
    public function attachmentItems(?ContractUsage $usage, ?GhorfeOnlineList $business, string $location, bool $canReview = false): array
    {
        $items = [];

        foreach ($this->staffFilesFor($usage) as $media) {
            $items[] = [
                'name' => $media->file_name,
                'url' => $media->getUrl(),
                'type' => 'staff',
                'label' => 'پیوست مدیریت',
                'status' => null,
                'reason' => null,
                'approve_url' => null,
                'reject_url' => null,
            ];
        }

        foreach ($this->clientFilesFor($business) as $media) {
            $items[] = [
                'name' => $media->file_name,
                'url' => $media->getUrl(),
                'type' => 'client',
                'label' => 'پیوست کاربر',
                'status' => $this->files->statusOf($media),
                'reason' => (string) $media->getCustomProperty('rejected_reason', ''),
                // Review actions only where the caller may review (super admin, staff list view).
                'approve_url' => $canReview ? route('admin.sms.contract.files.approve', $media->id) : null,
                'reject_url' => $canReview ? route('admin.sms.contract.files.reject', $media->id) : null,
            ];
        }

        return $items;
    }

    /**
     * Client files of a business (up to 5). Empty for no business.
     *
     * @return Collection<int, Media>
     */
    public function clientFilesFor(?GhorfeOnlineList $business): Collection
    {
        if (! $business) {
            return collect();
        }

        return $this->files->clientFiles($business);
    }

    /**
     * Get the specific OTP contract usage for a single business.
     */
    public function getBusinessOtpContract(GhorfeOnlineList $business): ?ContractUsage
    {
        return $this->resolver->forOwner(GhorfeOnlineList::class, $business->id, 'sms_otp');
    }

    /**
     * Get the advertising contract usage for a single business.
     */
    public function getBusinessAdvertisingContract(GhorfeOnlineList $business): ?ContractUsage
    {
        return $this->resolver->forOwner(GhorfeOnlineList::class, $business->id, 'sms_advertising');
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
            ->with(['contract.folder', 'contract.media', 'media'])
            ->get()
            ->keyBy('usable_id');

        $otpUsages = ContractUsage::query()
            ->location('sms_otp')
            ->where('usable_type', GhorfeOnlineList::class)
            ->whereIn('usable_id', $businessIds)
            ->active()
            ->with(['contract.folder', 'contract.media', 'media'])
            ->get()
            ->keyBy('usable_id');

        foreach ($paginator->items() as $business) {
            $business->advertising_contract_usage = $advUsages->get($business->id);
            $business->otp_contract_usage = $otpUsages->get($business->id);
        }

        return $paginator;
    }

    /**
     * Save an SMS contract (OTP or Advertising), through the central models:
     *  - a general contract of the panel = one location-wide usage (replaces the previous one);
     *  - a business's contract = one usage per owner per location (replaces the previous one);
     *  - a bank-only template is saved without any usage.
     * Contracts already in use are never edited here.
     */
    public function saveContract(
        string $serviceType,
        array $data,
        ?GhorfeOnlineList $business = null,
        ?UploadedFile $file = null,
        ?string $filePath = null,
        ?array $staffAttachments = null
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
                'business_id' => ['باید حداقل یکی از دو گزینه «ذخیره به‌عنوان الگوی قرارداد» یا «انتساب به کسب‌وکار» انتخاب شود.'],
            ]);
        }

        if (!$templateOnly && $serviceType === 'advertising' && !$ownerId && !$isGlobalTemplate) {
            throw ValidationException::withMessages([
                'business_id' => ['انتخاب کسب‌وکار برای انتساب قرارداد پیامک تبلیغاتی الزامی است.'],
            ]);
        }

        try {
            $staffFiles = array_values(ContractMediaService::uploadedFilesOf($staffAttachments));
        } catch (ContractMediaException $e) {
            throw ValidationException::withMessages(['staff_attachments' => [$e->getMessage()]]);
        }
        // Refused before anything is created, so a bad batch leaves no template copy or rows behind.
        $this->assertStaffFiles($staffFiles);

        return DB::transaction(function () use ($location, $ownerType, $ownerId, $data, $file, $filePath, $staffFiles, $templateOnly) {
            $contractId = null;

            if (($data['mode'] ?? 'new') === 'existing' && !empty($data['contract_id'])) {
                $contractId = (int) $data['contract_id'];
                $contract = Contract::findOrFail($contractId);

                // A template is used to make a contract, it never carries business files. With staff
                // files, the business gets its own contract made from the template; the template stays as it is.
                if ($staffFiles) {
                    $contract = $this->makeDedicatedContract($contract);
                    $contractId = $contract->id;
                }
            } else {
                // A folder is applied only when the user explicitly picks one.
                $folder = null;
                if (!empty($data['contract_folder_id'])) {
                    $folder = ContractFolder::find($data['contract_folder_id']);
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

            // Staff files belong to the contract itself (max 5, enforced by the media service).
            if ($staffFiles) {
                try {
                    $this->contractMedia->addStaffFiles($contract, $staffFiles);
                } catch (ContractMediaException $e) {
                    throw ValidationException::withMessages(['staff_attachments' => [$e->getMessage()]]);
                }
            }

            if ($templateOnly) {
                return $contract;
            }

            // One usage per owner (or per location for the general contract) and location.
            $this->detachPrevious($location, $ownerType, $ownerId);

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
     * Staff files of an assignment: at most 5, each a valid file. Throws a validation error before
     * any write, the same rules the media service applies when the files are attached.
     *
     * @param  array<int, UploadedFile>  $files
     */
    protected function assertStaffFiles(array $files): void
    {
        if (count($files) > ContractMediaService::STAFF_LIMIT) {
            throw ValidationException::withMessages(['staff_attachments' => ['حداکثر ۵ فایل پیوست مجاز است.']]);
        }

        try {
            foreach ($files as $file) {
                $this->contractMedia->assertFile($file);
            }
        } catch (ContractMediaException $e) {
            throw ValidationException::withMessages(['staff_attachments' => [$e->getMessage()]]);
        }
    }

    /**
     * A new contract made from a template, for one business's staff files: the template's title,
     * type, status, content and original file, no folder. The template itself is not changed.
     */
    protected function makeDedicatedContract(Contract $template): Contract
    {
        $contract = Contract::create([
            'title' => $template->title,
            'type' => $template->type,
            'status' => $template->status,
            'content' => $template->content,
            'contract_folder_id' => null,
        ]);

        $original = $template->getFirstMedia('file');
        if ($original) {
            $original->copy($contract, 'file');
        }

        if (Auth::id()) {
            ContractHistory::create([
                'contract_id' => $contract->id,
                'user_id' => Auth::id(),
                'file' => $contract->file,
            ]);
        }

        return $contract;
    }

    /**
     * Detach / delete a contract usage.
     */
    public function detachUsage(ContractUsage $usage): bool
    {
        return (bool) $usage->forceDelete();
    }

    /**
     * Remove previous usages for the given location and owner (the general one when no owner).
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
