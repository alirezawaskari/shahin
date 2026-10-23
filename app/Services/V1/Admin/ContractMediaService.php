<?php

namespace App\Services\V1\Admin;

use App\Exceptions\ContractMediaException;
use App\Models\Contract;
use App\Models\ContractUsage;
use App\Models\GhorfeOnlineList;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Staff files of the contract bank (collection staff_attachments on Contract), shared by the
 * central contract pages and the SMS contract pages, so the cap, the types and the lock are
 * enforced in one place.
 *
 *  - Up to 5 staff files per contract. Changeable while the contract is free.
 *  - Locked once the contract is assigned to a business that has signed it (a signature record
 *    exists for that business). A general (location-wide) contract is never locked: every
 *    signature keeps its own snapshot of the text, so editing the template cannot change it.
 *
 * Client files and their approve / reject state live on the SMS side: SmsContractFilesService.
 */
class ContractMediaService
{
    public const STAFF_LIMIT = 5;

    public const EXTENSIONS = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

    /** Kilobytes, as Laravel's `max:` rule expects. 10 MB per file. */
    public const MAX_KILOBYTES = 10240;

    /** Laravel validation rules for one uploaded file. Controllers use these too. */
    public static function fileRules(): array
    {
        return ['file', 'mimes:' . implode(',', self::EXTENSIONS), 'max:' . self::MAX_KILOBYTES];
    }

    /** True when a business that has signed this contract is using it (location-wide usages never lock). */
    public function isStaffLocked(Contract $contract): bool
    {
        $businessIds = ContractUsage::query()
            ->where('contract_id', $contract->id)
            ->where('usable_type', GhorfeOnlineList::class)
            ->whereNotNull('usable_id')
            ->pluck('usable_id');

        if ($businessIds->isEmpty()) {
            return false;
        }

        return GhorfeOnlineList::query()
            ->whereIn('id', $businessIds)
            ->get()
            ->contains(fn (GhorfeOnlineList $business) => $business->hasSmsSignatureRecord());
    }

    /**
     * Adds staff files to a contract. All or nothing: a batch that would pass the cap or
     * contains a bad file adds nothing.
     *
     * @param  UploadedFile[]  $files
     */
    public function addStaffFiles(Contract $contract, array $files): int
    {
        if (empty($files)) {
            return 0;
        }

        if ($this->isStaffLocked($contract)) {
            throw new ContractMediaException('locked', 'فایل‌های مدیریت این قرارداد پس از امضا قابل تغییر نیستند.');
        }

        $this->assertWithinLimit($contract->getMedia('staff_attachments')->count(), count($files), self::STAFF_LIMIT, 'فایل مدیریت');
        array_map([$this, 'assertFile'], $files);

        DB::transaction(function () use ($contract, $files) {
            foreach ($files as $file) {
                $contract->addMedia($file)->toMediaCollection('staff_attachments');
            }
        });

        return count($files);
    }

    /** Removes one staff file. Refused once the contract is locked. */
    public function removeStaffFile(Contract $contract, string $mediaId): void
    {
        if ($this->isStaffLocked($contract)) {
            throw new ContractMediaException('locked', 'فایل‌های این قرارداد پس از امضا قابل حذف نیستند.');
        }

        $media = $contract->getMedia('staff_attachments')->firstWhere('id', $mediaId);
        if (! $media) {
            throw new ContractMediaException('not_found', 'فایل مورد نظر یافت نشد.');
        }

        $media->delete();
    }

    private function assertWithinLimit(int $existing, int $incoming, int $limit, string $label): void
    {
        if ($existing + $incoming > $limit) {
            throw new ContractMediaException('cap', "حداکثر {$limit} {$label} مجاز است.");
        }
    }

    /** Server-side check of every upload, whatever the form sent. */
    public function assertFile(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new ContractMediaException('invalid', 'فایل ارسالی معتبر نیست.');
        }

        if (! in_array(strtolower($file->getClientOriginalExtension()), self::EXTENSIONS, true)) {
            throw new ContractMediaException('mime', 'فرمت فایل‌ها باید pdf, doc, docx, jpg, jpeg یا png باشد.');
        }

        if ($file->getSize() > self::MAX_KILOBYTES * 1024) {
            throw new ContractMediaException('size', 'حجم هر فایل نباید بیش از ۱۰ مگابایت باشد.');
        }
    }
}
