<?php

namespace App\Services\SmsSettings;

use App\Exceptions\ContractMediaException;
use App\Models\GhorfeOnlineList;
use App\Models\Media;
use App\Services\V1\Admin\ContractMediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

/**
 * Client files of an SMS business (collection sms_contract_attachments_client on GhorfeOnlineList):
 * up to 5, uploaded while signing and never removed. Staff approve or reject each one. A rejected
 * file can be re-uploaded by the client, and only that one.
 *
 * Rejecting a file never changes the signing record and never closes the SMS gate.
 * Per-file state lives in the Spatie custom properties: approval_status, rejected_reason,
 * reviewed_by, reviewed_at, superseded (an old file replaced by a re-upload).
 */
class SmsContractFilesService
{
    public const COLLECTION = 'sms_contract_attachments_client';

    public const CLIENT_LIMIT = 5;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const REASON_MAX_LENGTH = 500;

    public function __construct(
        protected ContractMediaService $media
    ) {
    }

    /**
     * Client files still in force (superseded re-uploads are kept for the record, not shown as current).
     * Read straight from the table: a model's media relation is cached, and a review or a re-upload
     * must see the state the last write left.
     */
    public function clientFiles(GhorfeOnlineList $business): Collection
    {
        return Media::query()
            ->where('model_type', GhorfeOnlineList::class)
            ->where('model_id', $business->id)
            ->where('collection_name', self::COLLECTION)
            ->orderBy('id')
            ->get()
            ->reject(fn (Media $m) => (bool) $m->getCustomProperty('superseded'))
            ->values();
    }

    /** Status of a client file (pending when nothing was recorded, e.g. legacy files). */
    public function statusOf(Media $media): string
    {
        return (string) ($media->getCustomProperty('approval_status') ?: self::STATUS_PENDING);
    }

    /**
     * Stores the client files of a signing. Called once, inside the signing transaction.
     *
     * @param  UploadedFile[]  $files
     */
    public function storeClientFiles(GhorfeOnlineList $business, array $files): int
    {
        if (empty($files)) {
            return 0;
        }

        $existing = $this->clientFiles($business)->count();
        if ($existing + count($files) > self::CLIENT_LIMIT) {
            throw new ContractMediaException('cap', 'حداکثر ' . self::CLIENT_LIMIT . ' فایل پیوست مجاز است.');
        }

        array_map([$this->media, 'assertFile'], $files);

        foreach ($files as $file) {
            $business->addMedia($file)
                ->withCustomProperties($this->initialProperties())
                ->toMediaCollection(self::COLLECTION);
        }

        return count($files);
    }

    public function approve(Media $media, ?int $reviewerId): void
    {
        $this->assertClientFile($media);

        $media->setCustomProperty('approval_status', self::STATUS_APPROVED);
        $media->setCustomProperty('rejected_reason', null);
        $this->stampReview($media, $reviewerId);
        $media->save();
    }

    /** Reject needs a reason. The signing record and the SMS gate stay as they are. */
    public function reject(Media $media, ?int $reviewerId, string $reason): void
    {
        $this->assertClientFile($media);

        $reason = trim($reason);
        if ($reason === '') {
            throw new ContractMediaException('reason_required', 'دلیل رد فایل الزامی است.');
        }
        if (mb_strlen($reason) > self::REASON_MAX_LENGTH) {
            throw new ContractMediaException('reason_too_long', 'دلیل رد فایل نباید بیش از ۵۰۰ نویسه باشد.');
        }

        $media->setCustomProperty('approval_status', self::STATUS_REJECTED);
        $media->setCustomProperty('rejected_reason', $reason);
        $this->stampReview($media, $reviewerId);
        $media->save();
    }

    /**
     * The client re-uploads ONLY the files staff rejected. Each new file replaces one rejected file.
     * The old one is kept for the record, marked superseded.
     *
     * @param  array<int, UploadedFile>  $replacements  rejected media id => new file
     */
    public function reuploadRejected(GhorfeOnlineList $business, array $replacements): int
    {
        if (empty($replacements)) {
            return 0;
        }

        $rejected = $this->clientFiles($business)
            ->filter(fn (Media $m) => $this->statusOf($m) === self::STATUS_REJECTED)
            ->keyBy('id');

        foreach ($replacements as $mediaId => $file) {
            if (! $rejected->has((string) $mediaId)) {
                throw new ContractMediaException('not_rejected', 'فقط فایل‌هایی که رد شده‌اند قابل بارگذاری مجدد هستند.');
            }
            $this->media->assertFile($file);
        }

        foreach ($replacements as $mediaId => $file) {
            $old = $rejected->get((string) $mediaId);

            $business->addMedia($file)
                ->withCustomProperties($this->initialProperties() + ['replaces_media_id' => $old->id])
                ->toMediaCollection(self::COLLECTION);

            $old->setCustomProperty('superseded', true);
            $old->save();
        }

        return count($replacements);
    }

    private function assertClientFile(Media $media): void
    {
        if ($media->model_type !== GhorfeOnlineList::class || $media->collection_name !== self::COLLECTION) {
            throw new ContractMediaException('not_client_file', 'این فایل، پیوست امضاکننده نیست.');
        }
    }

    private function initialProperties(): array
    {
        return ['approval_status' => self::STATUS_PENDING, 'rejected_reason' => null, 'superseded' => false];
    }

    private function stampReview(Media $media, ?int $reviewerId): void
    {
        $media->setCustomProperty('reviewed_by', $reviewerId);
        $media->setCustomProperty('reviewed_at', now()->toDateTimeString());
    }
}
