<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FactorItemProduct extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function factor_item()
    {
        return $this->belongsTo(FactorItem::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class , 'factor_item_service_tariff_id');
    }

    public function attribute_item()
    {
        return $this->belongsTo(AttributeItem::class);
    }

    public function productAttribute()
    {
        return $this->belongsTo(ProductAttribute::class);
    }

    public function scopeFilter($query)
    {
        $category = request('category');
        $title = request('title');
        $product_code = request('product_code');
        $diversity_code = request('diversity_code');
        $seller_code = request('seller_code');
        $center = auth()->user()->center_id;

            if (isset($center) && $center != '') {
                $query->whereHas('productAttribute', function ($query) use ($center) {
                    $query->where('productattributeable_id', $center);
                });
            }

            if (isset($category) && trim($category) != 'all') {
                $query->whereHas('productAttribute', function ($query) use ($category) {
                    $query->whereHas('product', function ($query) use ($category) {
                        $query->whereHas('categories', function ($query) use ($category) {
                            $query->where('category_id', $category);
                        });
                    });
                });
            }

            if (isset($startDate) && trim($startDate) != '' && isset($endDate) && trim($endDate) != '') {
                $query->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
            }

            if (isset($title) && trim($title) != '') {
                $query->whereHas('productAttribute', function ($query) use ($title) {
                    $query->whereHas('product', function ($query) use ($title) {
                        $query->where('name', 'LIKE', '%' . $title . '%');
                    });
                });
            }

            if (isset($product_code) && $product_code != '') {
                $query->whereHas('productAttribute', function ($query) use ($product_code) {
                    $query->whereHas('product', function ($query) use ($product_code) {
                        $query->where('id', $product_code);
                    });
                });
            }

            if (isset($diversity_code) && $diversity_code != '') {
                $query->whereHas('productAttribute', function ($query) use ($diversity_code) {
                    $query->where('id', $diversity_code);
                });
            }

            if (isset($seller_code) && $seller_code != '') {
                $query->whereHas('productAttribute', function ($query) use ($seller_code) {
                    $query->where('seller_code', $seller_code);
                });
            }

        return $query;
    }
}
