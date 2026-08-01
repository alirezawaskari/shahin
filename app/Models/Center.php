<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Center extends Model implements HasMedia {
    use SoftDeletes, InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;
    protected $dates = [ 'deleted_at' ];

    /** Touch-to-migrate: legacy single-image columns -> Spatie single-file collections. */
    protected function legacyMediaMap(): array
    {
        return ['contract_file' => 'contract_file', 'image' => 'image', 'background_image' => 'background_image'];
    }

    /**
     * Spatie media collections. Legacy path columns (image, background_image,
     * contract_file) are kept, but reads route through the accessors below.
     * Only `contract_file` is wired in the admin UI today; the rest are declared
     * so the model is fully Spatie-ready for wherever they get used.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->useDisk('public')->singleFile();
        $this->addMediaCollection('background_image')->useDisk('public')->singleFile();
        $this->addMediaCollection('contract_file')->useDisk('public')->singleFile();
        $this->addMediaCollection('gallery')->useDisk('public');
    }

    /** Contract file url (shadows the legacy `contract_file` column; disk-checked). */
    public function getContractFileAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'contract_file')
            ?: ($this->getRawOriginal('contract_file') ? asset($this->getRawOriginal('contract_file')) : '');
    }

    /** Main image url (shadows the legacy `image` column; disk-checked). */
    public function getImageAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image')
            ?: ($this->getRawOriginal('image') ? asset($this->getRawOriginal('image')) : '');
    }

    /** Background image url (shadows the legacy `background_image` column; disk-checked). */
    public function getBackgroundImageAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'background_image')
            ?: ($this->getRawOriginal('background_image') ? asset($this->getRawOriginal('background_image')) : '');
    }

    /** Disk-checked gallery image urls (skips media whose file is missing). */
    public function galleryUrls(string $collection = 'gallery')
    {
        return \App\Helpers\MediaLibraryHelper::existingMedia($this, $collection)
            ->map(fn ($m) => $m->getUrl());
    }

    protected $fillable = [ 'centerable_type', 'centerable_id', 'center_request_id', 'creator_representation_id', 'title', 'address', 'contract_number', 'contract_start_date', 'contract_file',
    'pay_type', 'purchase_type', 'status', 'is_show', 'lat', 'lon', 'has_subset', 'type', 'fullName',
    'background_image', 'description', 'province_id', 'city_id', 'image', 'center_category_id', 'user_id' , 'phone',
    // public branch page — see 2026_07_30_110000_add_branch_profile_fields_to_centers_table
    'label_type', 'intro_text', 'email', 'map_iframe_link', 'working_hours',
    'about_title', 'services_title', 'staff_title', 'plans_title' ];

    /** $fillable wins over the (empty) $guarded below, so new columns must be listed above. */
    protected $casts = [
        'working_hours' => 'array',
    ];

    const TYPE = [ 'seller' => 'فروشنده', 'producer' => 'تولید کننده', 'supplier' => 'تامین کننده', 'خدمات دهنده'=>'service_provider' ];

    /**
    * What this place is called in menus and headings. The customer picks the word, because
    * a chain calls them شعبه and a single-site business calls itself مرکز.
    */
    const LABELS = [ 'branch' => 'شعبه', 'center' => 'مرکز' ];

    /**
    * Weekday keys of the `working_hours` json, in the order the branch page lists them.
    * English keys so the API and the frontend never have to match Persian strings.
    */
    const WEEK_DAYS = [
        'saturday'  => 'شنبه',
        'sunday'    => 'یکشنبه',
        'monday'    => 'دوشنبه',
        'tuesday'   => 'سه شنبه',
        'wednesday' => 'چهارشنبه',
        'thursday'  => 'پنجشنبه',
        'friday'    => 'جمعه',
    ];
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

    public function creatorRepresentation() {
        return $this->belongsTo( Representation::class, 'creator_representation_id' );
    }
    /**
    * @return \Illuminate\Database\Eloquent\Relations\MorphMany
    */

    public function percentageAllocationEmployees() {
        return $this->morphMany( PercentageAllocationEmployee::class, 'percentage_allocation_employeeable' );
    }
    /**
    * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
    */

    public function representations() {
        return $this->morphedByMany( Representation::class, 'owner', 'center_owners' );
    }

    /**
    * Businesses this center is a branch of. The other side of GhorfeOnlineList::centers();
    * a shared center can serve more than one business, hence many-to-many.
    *
    * `owner`, not `centerable`: on THIS model `centerable` already means what the center
    * offers (centerable() below -> Category|Service, and the `centerables` pivot), which is
    * the opposite direction. The `center_owners` pivot answers who owns it.
    *
    * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
    */
    public function ghorfeOnlineLists() {
        return $this->morphedByMany( GhorfeOnlineList::class, 'owner', 'center_owners' )->withPivot( 'sort' );
    }

    /**
    * Staff whose home branch is this center (employees.center_id).
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function employees() {
        return $this->hasMany( Employee::class );
    }

    /**
    * "طرح های مالی فعال برای این شعبه" — the installment / cash / insurance plans shown on
    * the branch page.
    *
    * Reuses the existing `financial_plans_type_items` morph pivot (already carrying Service
    * and Category), so no new table: this just adds Center as a third subject.
    *
    * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
    */
    public function financialPlansTypes() {
        return $this->morphToMany(
            FinancialPlansType::class,
            'financialplanstypeItemable',
            'financial_plans_type_items'
        );
    }

    /**
    * Questions shown in this branch's "سوالات متداول" block.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function faqs() {
        return $this->hasMany( Faq::class );
    }

    /**
    * Visitor star ratings. Includes the un-moderated ones — the admin screen needs them;
    * a frontend must add ->approved().
    *
    * Deliberately unordered: the relation is also used for moderation writes, and an
    * `order by` riding along on an UPDATE/DELETE is needless risk. Callers that display
    * ratings add their own ->latest().
    *
    * @return \Illuminate\Database\Eloquent\Relations\MorphMany
    */
    public function ratings() {
        return $this->morphMany( Rating::class, 'rateable' );
    }

    /** "شعبه" or "مرکز" — the word the customer chose for this place. */
    public function getLabelWordAttribute(): string {
        return self::LABELS[ $this->label_type ] ?? self::LABELS[ 'branch' ];
    }

    /** Heading of the branch page: "شعبه تهران مرکزی". */
    public function getDisplayTitleAttribute(): string {
        return trim( $this->label_word . ' ' . $this->title );
    }

    /**
    * Opening hours of one weekday, always in the same shape so a view never has to guard
    * against a half-filled json (a day the admin never touched, or a legacy null).
    *
    * @return array{open: string, close: string, closed: bool}
    */
    public function workingHoursFor( string $day ): array {
        $stored = $this->working_hours[ $day ] ?? [];

        return [
            'open'   => $stored[ 'open' ] ?? '',
            'close'  => $stored[ 'close' ] ?? '',
            'closed' => (bool) ( $stored[ 'closed' ] ?? false ),
        ];
    }

    /** Average of the APPROVED ratings, rounded to one decimal; null when there are none. */
    public function getAverageRatingAttribute(): ?float {
        $average = $this->ratings()->approved()->avg( 'score' );

        return $average === null ? null : round( (float) $average, 1 );
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
