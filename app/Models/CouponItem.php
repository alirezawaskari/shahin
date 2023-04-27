<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CouponItem extends Model
{
    /**
     * @var string[]
     */
    protected $fillable=['coupon_id','couponitemable_id','couponitemable_type','center_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function couponitemable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * @param $product_attribute_id
     * @return int
     */
    public static function hasCouponByProductAttributeId($product_attribute_id)
    {
        $able_to_edit=1;
        $coupon_item = CouponItem::where('couponitemable_id',$product_attribute_id)->
            where('couponitemable_type',ProductAttribute::class)->first();
        if(isset($coupon_item->coupon)){
            $coupon = $coupon_item->coupon ;
            if($coupon->status == 1){
                $now_date = today()->format('Y-m-d');
                $now_date_carbon = Carbon::createFromFormat('Y-m-d', $now_date);
                $start_at_carbon = Carbon::createFromFormat('Y-m-d', $coupon->start_at);
                $able_to_edit = $start_at_carbon->gt($now_date_carbon);
                if($able_to_edit == 'true') $able_to_edit = 1 ; else $able_to_edit = 0;
                return $able_to_edit;
            }
            else $able_to_edit = 0;
        }
      return $able_to_edit;
    }

}
