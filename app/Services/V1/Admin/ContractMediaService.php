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

    /**
     * Persian `uploaded` message for every media field, with the server's real per-file limit. PHP drops a
     * file larger than upload_max_filesize and reports it as failed; Laravel's `file` rule then fails with
     * this message instead of the English default.
     *
     * @param  string[]  $fields  e.g. ['staff_files']; the wildcard key is added for each.
     * @return array<string, string>
     */
    public static function uploadMessages(array $fields): array
    {
        $messages = [];
        foreach ($fields as $field) {
            $messages[$field . '.*.uploaded'] = self::uploadFailedMessage();
        }

        return $messages;
    }

    /**
     * Data attributes for the shared file picker (resources/views/Admin/contract/partials/file-picker.blade.php):
     * the per-file and per-request limits the server really enforces, read from php.ini.
     */
    public static function pickerAttributes(int $maxFiles = self::STAFF_LIMIT, int $existingFiles = 0): string
    {
        return sprintf(
            'data-max-files="%d" data-max-file-bytes="%d" data-upload-max-bytes="%d" data-post-max-bytes="%d" data-existing-files="%d"',
            $maxFiles,
            self::MAX_KILOBYTES * 1024,
            self::iniBytes(ini_get('upload_max_filesize')),
            self::iniBytes(ini_get('post_max_size')),
            max(0, $existingFiles),
        );
    }

    public static function uploadFailedMessage(): string
    {
        return 'بارگذاری فایل ناموفق بود؛ حجم فایل از حد مجاز سرور (' . self::serverFileLimitLabel() . ') بیشتر است.';
    }

    /** The server's per-file limit from php.ini, as Persian text, e.g. «۲ مگابایت». */
    public static function serverFileLimitLabel(): string
    {
        $megabytes = intdiv(self::iniBytes(ini_get('upload_max_filesize')), 1024 * 1024);

        return strtr((string) $megabytes, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']) . ' مگابایت';
    }

    /** A php.ini size value ("2M", "256M", "1G", "512") in bytes. */
    public static function iniBytes(string|false $value): int
    {
        $value = trim((string) $value);
        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }

    /**
     * The files of one media input as they reached the server. Anything that is not a file is refused as a
     * validation error. Nothing is dropped, so a failed upload can never reach the media services as
     * "no file".
     *
     * Keys are kept (the replace[<media id>] form posts one file per key).
     *
     * @return array<array-key, UploadedFile>
     */
    public static function uploadedFilesOf(mixed $input): array
    {
        if ($input === null || $input === []) {
            return [];
        }

        if (! is_array($input)) {
            throw new ContractMediaException('invalid', 'فایل ارسالی معتبر نیست.');
        }

        foreach ($input as $file) {
            if (! $file instanceof UploadedFile) {
                throw new ContractMediaException('invalid', 'فایل ارسالی معتبر نیست.');
            }
        }

        return $input;
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
            $tooLarge = in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);

            throw new ContractMediaException('invalid', $tooLarge ? self::uploadFailedMessage() : 'بارگذاری فایل ناموفق بود. فایل را دوباره انتخاب کنید.');
        }

        if (! in_array(strtolower($file->getClientOriginalExtension()), self::EXTENSIONS, true)) {
            throw new ContractMediaException('mime', 'فرمت فایل‌ها باید pdf, doc, docx, jpg, jpeg یا png باشد.');
        }

        if ($file->getSize() > self::MAX_KILOBYTES * 1024) {
            throw new ContractMediaException('size', 'حجم هر فایل نباید بیش از ۱۰ مگابایت باشد.');
        }
    }
}
