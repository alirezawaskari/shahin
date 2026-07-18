<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TotalFactorItemContractSettlement extends Model implements HasMedia
{
    use InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    /** Touch-to-migrate: legacy single-image columns -> Spatie single-file collections. */
    protected function legacyMediaMap(): array
    {
        return [
            'img_request_contract_settlement' => 'img_request_contract_settlement',
            'img_warranty_delivery_receipt'   => 'img_warranty_delivery_receipt',
        ];
    }

    /**
     * Spatie collections for the two settlement images (single text columns
     * shadowed by the accessors below, with legacy-column fallback).
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('img_request_contract_settlement')->useDisk('public')->singleFile();
        $this->addMediaCollection('img_warranty_delivery_receipt')->useDisk('public')->singleFile();
    }

    /** Settlement-request image url (Spatie first, legacy column fallback). */
    public function getImgRequestContractSettlementAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'img_request_contract_settlement')
            ?: ($this->getRawOriginal('img_request_contract_settlement') ? asset($this->getRawOriginal('img_request_contract_settlement')) : '');
    }

    /** Warranty delivery receipt image url (Spatie first, legacy column fallback). */
    public function getImgWarrantyDeliveryReceiptAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'img_warranty_delivery_receipt')
            ?: ($this->getRawOriginal('img_warranty_delivery_receipt') ? asset($this->getRawOriginal('img_warranty_delivery_receipt')) : '');
    }

    /**
     * @var array
     */

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function totalFactorItem()
    {
        return $this->belongsTo(TotalFactorItem::class , 'item_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function applicant()
    {
        return $this->belongsTo(User::class , 'applicant_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mali()
    {
        return $this->belongsTo(User::class , 'mali_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function superadmin()
    {
        return $this->belongsTo(User::class , 'superadmin_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deliverer()
    {
        return $this->belongsTo(User::class , 'deliverer_id');
    }
}
