<?php

namespace App\Models;

use CreatePurchaseInvoiceFactorsTable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PurchaseInvoice extends Model implements HasMedia
{
    use SoftDeletes;
    use InteractsWithMedia;
    use \App\Models\Concerns\AdoptsLegacyMedia;

    /** Touch-to-migrate: legacy single-image columns -> Spatie single-file collections. */
    /** Touch-to-migrate: legacy paths inside the `factor_image` JSON array -> `factor_image` collection. */
    protected function legacyArrayMediaMap(): array
    {
        return ['factor_image' => 'factor_image'];
    }

    protected function legacyMediaMap(): array
    {
        return ['post_image' => 'post_image'];
    }

    /**
     * Spatie collections: `factor_image` (multi — its URLs are appended to the
     * factor_image JSON array column) and `post_image` (singleFile — shadowed by
     * the getPostImageAttribute accessor, with legacy-column fallback).
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('factor_image')->useDisk('public');
        $this->addMediaCollection('post_image')->useDisk('public')->singleFile();
    }

    /** Postage image url (Spatie first, legacy `post_image` column as fallback). */
    public function getPostImageAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'post_image')
            ?: ($this->getRawOriginal('post_image') ? asset($this->getRawOriginal('post_image')) : '');
    }

    /**
     * Gallery urls for the invoice factor images: adopted/new Spatie files (served from
     * /storage) unioned with any legacy `factor_image` path not yet migrated. Views must
     * render this rather than the raw `factor_image` array, so a save's adoption surfaces
     * the /storage copies (the raw column keeps the legacy paths — see arrayMediaUrls()).
     *
     * @return array<string>
     */
    public function factorImageUrls(): array
    {
        return $this->arrayMediaUrls('factor_image', 'factor_image');
    }

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string[]
     */
    protected $casts = [
        'factor_image' => 'array',
        'values' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function center()
    {
        return $this->belongsTo(Center::class, 'center_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function representation()
    {
        return $this->belongsTo(Representation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function product_storeRoom()
    {
        return $this->hasMany(ProductStoreRoom::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseInvoiceInstallments()
    {
        return $this->hasMany(PurchaseInvoiceInstallment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseInvoiceFactors()
    {
        return $this->hasMany(PurchaseInvoiceFactor::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reports()
    {
        return $this->hasMany(ReportPurchaseInvoice::class);
    }

    /**
     * @param $status
     * @return string
     */
    public static function status($status)
    {
        return [
            '0' => 'جدید',
            '1' => 'منتظر تایید مالی',
            '2' => 'منتظر تایید مدیر',
            '3' => 'منتظر تایید مدیر مالی عاملیت',
            '4' => 'منتظر تایید  مدیر عاملیت',
            '5' => 'آماده ارسال',
            '6' => 'منتظر تایید دریافت کالا',
            '7' => 'گزارش خرابی کالا',
            '8' => 'اتمام کار',
            '9' => 'منتظر واریز وجه',
            '10' => 'رد شده',
        ][$status];
    }

    /**
     * @param $status
     * @return string
     */
    public static function getClassByStatus($status)
    {
        return [
            '0' => 'danger',
            '1' => 'danger',
            '2' => 'danger',
            '3' => 'danger',
            '4' => 'danger',
            '5' => 'danger',
            '6' => 'warning',
            '7' => 'warning',
            '8' => 'dark',
            '9' => 'warning',
            '10' => 'dark',
        ][$status];
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $id = request('id');
        $supplier = request('supplier');
        $status = request('status');
        $start_date = request('start_date');
        $end_date = request('end_date');

        if(isset($id) && trim($id) != '')
        {
            $query->where('id' , $id);
        }
        if(isset($supplier) && trim($supplier)!= 'all')
        {
            $query->where('supplier_id' , $supplier);
        }
        if(isset($status) && trim($status) != 'all')
        {
            $query->where('status' , $status);
        }
        if (isset($start_date) && trim($start_date) != '' && isset($end_date) && trim($end_date) != '') {
            $query->whereDate('created_at', '>=' ,$start_date)->whereDate('created_at', '<=' ,$end_date);
        }

        return $query;
    }

    /**
     * @param $type
     * @return string|void
     */
    public static function type($type)
    {
        if ($type == 0) {
            return 'نقدی';
        } elseif ($type == 1) {
            return 'اقساطی';
        }
    }
}
