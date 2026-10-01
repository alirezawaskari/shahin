<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A business's shipping option (ارسال سریع، پست پیشتاز، ...), managed in the business edit
 * page's «تنظیمات مالی» tab and attached to products from the product form. The table is
 * created by the product API, which serves these to the storefront.
 */
class ShippingMethod extends Model
{
    protected $fillable = ['ghorfe_online_list_id', 'title', 'description', 'price'];

    protected $casts = [
        'price' => 'integer',
    ];

    public function ghorfeOnlineList()
    {
        return $this->belongsTo(GhorfeOnlineList::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_shipping_method');
    }
}
