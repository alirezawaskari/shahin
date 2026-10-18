<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The independent relation that binds a contract to where it is used.
 *
 * A contract is never copied into an invoice, a service or an employee record —
 * one row here says «this contract applies to this owner, in this location», so a
 * single contract can be active for registration invoices, purchase invoices and
 * the employees section at the same time, and one contract can serve many
 * services and many roles.
 *
 * `usable_type` + `usable_id` both NULL means the contract applies to the whole
 * location (used by product purchase invoices, which have no per-owner screen).
 */
class ContractUsage extends Model
{
    use SoftDeletes;

    /**
     * Usage locations (محل استفاده). Adding a location is one entry here —
     * no schema change and no controller rewrite.
     */
    const LOCATIONS = [
        'registration_invoice' => 'فاکتور ثبت‌نام',
        'purchase_invoice' => 'فاکتور خرید',
        'employee' => 'همکاران',
        'sms_otp' => 'قرارداد عمومی پیامک خدماتی (OTP)',
        'sms_advertising' => 'قرارداد پیامک تبلیغاتی کسب‌وکار',
    ];

    /**
     * Which owner types each location accepts. Drives the association UI and
     * validation, so a new location declares its owners in one place.
     */
    const LOCATION_OWNERS = [
        'registration_invoice' => [Service::class],
        'purchase_invoice' => [Service::class, Product::class, null],
        'employee' => [Role::class],
        'sms_otp' => [GhorfeOnlineList::class, null],
        'sms_advertising' => [GhorfeOnlineList::class, null],
    ];

    protected $fillable = [
        'contract_id',
        'usable_type',
        'usable_id',
        'location',
        'contract_number',
        'contract_date',
        'is_active',
        'position',
    ];

    protected $casts = [
        'contract_id' => 'integer',
        'usable_id' => 'integer',
        'contract_date' => 'date',
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function usable()
    {
        return $this->morphTo();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeLocation($query, string $location)
    {
        return $query->where('location', $location);
    }

    /**
     * Usages that apply to the whole location rather than a single owner.
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('usable_type')->whereNull('usable_id');
    }

    public function scopeFor($query, string $ownerType, $ownerId)
    {
        return $query->where('usable_type', $ownerType)->where('usable_id', $ownerId);
    }

    public function getLocationLabelAttribute(): string
    {
        return self::LOCATIONS[$this->location] ?? $this->location;
    }

    /**
     * «خدمت: کارت هدیه» / «نقش: کارشناس فروش» / «همه موارد» — used by the contract
     * list to show where a contract is in use.
     */
    public function getOwnerLabelAttribute(): string
    {
        if (! $this->usable_type) {
            return 'همه موارد';
        }

        $owner = $this->usable;

        if (! $owner) {
            return 'حذف شده';
        }

        $prefix = match ($this->usable_type) {
            Service::class => 'خدمت',
            Role::class => 'نقش',
            Product::class => 'محصول',
            GhorfeOnlineList::class => 'کسب‌وکار',
            default => 'مورد',
        };

        $name = $owner->display_name ?? $owner->title ?? $owner->name ?? ('#' . $owner->getKey());

        return $prefix . ': ' . $name;
    }

    public static function isValidLocation(?string $location): bool
    {
        return $location !== null && array_key_exists($location, self::LOCATIONS);
    }
}
