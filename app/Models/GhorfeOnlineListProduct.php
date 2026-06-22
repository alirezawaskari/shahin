<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GhorfeOnlineListProduct extends Model
{
    protected $table = 'ghorfe_online_list_product';
    protected $fillable = [
        'user_id',
        'product_id',
        'ghorfe_online_list_id',
        'price',
        'stock',
        'purchase_type',
        'pay_type',
        'tariff_id',
        'guarantee',
    ];
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
