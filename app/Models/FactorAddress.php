<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The shipping address frozen onto a product order.
 *
 * This is a COPY of one of the user's `addresses` rows, taken at the moment the admin
 * selected it. Editing or deleting that source address later never touches this row, which
 * is what keeps the address on an already-shipped order correct - see the
 * create_factor_addresses_table migration.
 */
class FactorAddress extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function factor()
    {
        return $this->belongsTo(Factor::class);
    }

    /**
     * The user address this snapshot was copied from. May be missing (soft deleted) - the
     * snapshot is the source of truth, this is provenance only.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Build the snapshot payload for one of the user's addresses. Geo names are resolved
     * here, once, so the stored row renders without joining the geo tables again.
     *
     * @param Address $address
     * @return array
     */
    public static function snapshotOf(Address $address): array
    {
        $area = $address->area_id ? Area::find($address->area_id) : null;

        return [
            'address_id' => $address->id,
            'province_id' => $address->province_id,
            'province_name' => optional(Province::find($address->province_id))->name,
            'city_id' => $address->city_id,
            'city_name' => optional(City::find($address->city_id))->name,
            'area_id' => $address->area_id,
            'area_name' => optional($area)->name,
            'address' => $address->address,
            'plaque' => $address->plaque,
            'unit' => $address->unit,
            'postal_code' => $address->postal_code,
            'recipient_name' => $address->recipient_name,
            'recipient_family' => $address->recipient_family,
            'recipient_mobile' => $address->recipient_mobile,
            'lat' => $address->lat,
            'lon' => $address->lon,
        ];
    }

    /**
     * One line suitable for a table cell / packing slip.
     *
     * @return string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->province_name,
            $this->city_name,
            $this->area_name ? 'منطقه ' . $this->area_name : null,
            $this->address,
            $this->plaque ? 'پلاک ' . $this->plaque : null,
            $this->unit ? 'واحد ' . $this->unit : null,
            $this->postal_code ? 'کدپستی ' . $this->postal_code : null,
        ]);

        return implode('، ', $parts);
    }

    /**
     * @return string
     */
    public function getRecipientAttribute(): string
    {
        return trim($this->recipient_name . ' ' . $this->recipient_family);
    }
}
