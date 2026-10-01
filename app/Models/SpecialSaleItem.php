<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** One product inside a «شگفت‌انگیز» group, with its discount percentage. */
class SpecialSaleItem extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'special_sale_group_id' => 'integer',
        'product_id'            => 'integer',
        'percentage'            => 'decimal:2',
    ];

    public function group()
    {
        return $this->belongsTo(SpecialSaleGroup::class, 'special_sale_group_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
