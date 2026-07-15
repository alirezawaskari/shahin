<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Center extends Model {
    use SoftDeletes;
    protected $dates = [ 'deleted_at' ];

    protected $fillable = [ 'centerable_type', 'centerable_id', 'center_request_id', 'creator_representation_id', 'title', 'address', 'contract_number', 'contract_start_date', 'contract_file',
    'pay_type', 'purchase_type', 'status', 'is_show', 'lat', 'lon', 'has_subset', 'type', 'fullName',
    'background_image', 'description', 'province_id', 'city_id', 'image', 'center_category_id', 'user_id' , 'phone' ];

    const TYPE = [ 'seller' => 'فروشنده', 'producer' => 'تولید کننده', 'supplier' => 'تامین کننده', 'خدمات دهنده'=>'service_provider' ];
    /**
    * @var array
    */
    protected $guarded = [];

    public $timestamps = false;

    public function service() {
        return $this->belongsTo( Service::class );
    }

    public function products() {
        return $this->belongsToMany( Product::class );
    }

    public function center_request() {
        return $this->belongsTo( CenterRequest::class );
    }
    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */

    public function creatorRpresentation() {
        return $this->belongsTo( Representation::class, 'creator_representation_id' );
    }
    /**
    * @return \Illuminate\Database\Eloquent\Relations\MorphMany
    */

    public function percentageAllocationEmployees() {
        return $this->morphMany( PercentageAllocationEmployee::class, 'percentage_allocation_employeeable' );
    }
    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */

    public function representations() {
        return $this->belongsToMany( Representation::class );
    }

    public function users() {
        return $this->hasMany( User::class );
    }

    public function center_category() {
        return $this->belongsTo( CenterCategory::class, 'center_category_id' );
    }

    public function tickets() {
        return $this->hasMany( Ticket::class, 'ticketable_id' );
    }

    public function province() {
        return $this->belongsTo( Province::class, 'province_id' );
    }

    public function centerable() {
        return $this->morphTo();
    }

    public function purchaseInvoice() {
        return $this->hasMany( PurchaseInvoice::class );
    }

    public function scopeFilter( $query ) {
        $title = request( 'title' );
        $service = request( 'service' );
        $category = request( 'category' );
        $status = request( 'status' );

        if(auth()->user()->admin_representation_id)
        {
            $query->where('creator_representation_id' , auth()->user()->admin_representation_id);
        }
        if ( isset( $title ) && trim( $title ) != '' ) {
            $query->where( 'title', 'LIKE', '%' . $title . '%' );
        }

        if ( isset( $status ) && trim( $status ) != 'all' ) {
            $query->where( 'status', $status );
        }

        if ( isset( $category ) && trim( $category ) != 'all' ) {
            $query->whereHas('categories', function ($query) use ($category) {
                $query->where('centerable_id',$category);
            });
        }

        if ( isset( $service ) && trim( $service ) != 'all' ) {
            $query->whereHas('services', function ($query) use ($service) {
                $query->where('centerable_id',$service);
            });
        }

        return $query;
    }

    public static function status( $status ) {
        switch( $status ) {
            case 0:
            return 'غیر فعال';
            break;
            case 1:
            return 'فعال';
            break;
            default:
            return 'فعال';
        }
    }

    public function serviceTariffs() {
        return $this->belongsToMany( ServiceTariff::class );
    }

    public function galleries() {
        return $this->morphMany( Gallery::class, 'galleryable' );
    }

    public static function centerById( $id, $field ) {
        $center = Center::find( $id );
        if ( $center ) return $center->$field;
        return null;
    }
    /**
    * Get all of the categories that are assigned this center.
    */

    public function categories() {
        return $this->morphedByMany( Category::class, 'centerable' );
    }

    /**
    * Get all of the services that are assigned this center.
    */

    public function services() {
        return $this->morphedByMany( Service::class, 'centerable', 'centerables' );
    }
    /**
    * @param \App\Models\FactorItem $factor_item
    * @param mixed $type
    * @return void
    */
    public static function setUserPerformance( FactorItem $factor_item, $factor_item_service_tariff, $price , $purchase_price , $date) {
        $description = $factor_item_service_tariff->serviceTariff->name.' واریز از محل تخصیص سهم از تعرفه';
        $user_performance_sessions_done = $factor_item_service_tariff->sessions->where( 'status', 1 );
        $superadmin_center_user = User::where('level' , 'admin')->where('center_id' , $factor_item->center_id)->whereHas('roles',function ($query){
            $query->where('name','superadmin_center');
        })->first();

        if(isset($superadmin_center_user->employee))
        {
            $user_performance = UserPerformance::store( null , $superadmin_center_user, $description, $price, $purchase_price, $factor_item , $date);
            //set user performance sessions done
            UserPerformance::setSessionsDone($user_performance , $user_performance_sessions_done);
        }
    }
}
