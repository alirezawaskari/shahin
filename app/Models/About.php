<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * The "درباره ما" page of ONE business (or of a representation / the main lalecard site —
 * see scopeFilter). One row per owner; the API reads it for the about-us frontend.
 *
 * The five numbered content boxes and their four images belonged to the old lalecard site
 * and are gone from the admin: the page is now a fixed set of named sections (hero,
 * business info, virtual tour, branches, specialists, "why us", goals, faq), each with its
 * own column or relation. `content` / `content2` survive because the legacy
 * site.about view still renders them.
 */
class About extends Model implements HasMedia {
    use SoftDeletes, InteractsWithMedia;

    protected $dates = [ 'deleted_at' ];

    /** Longest a virtual-tour caption may be (mirrored in the view and in validation). */
    const TOUR_DESCRIPTION_LIMIT = 500;

    /**
     * Images of the page. Spatie single-file collections, NOT columns — they must stay out
     * of $fillable, mass-assigning them throws "Unknown column".
     */
    const PAGE_IMAGES = ['main_image', 'tour_poster', 'goals_image'];

    protected $fillable = [ 'aboutable_type', 'aboutable_id', 'title', 'content', 'content2',
    // درباره ما page — see 2026_07_30_100000_add_about_us_fields_to_abouts_table
    'intro_text',
    'established_year', 'contact_phone', 'address', 'working_days', 'working_hours',
    'tour_title', 'tour_video_link', 'tour_description',
    'branches_title', 'staff_title',
    'why_title', 'years_experience', 'satisfied_customers', 'average_rating',
    'stat_experience_label', 'stat_customers_label', 'stat_rating_label',
    'goals_title', 'goals_text' ];

    protected $casts = [
        'years_experience'    => 'integer',
        'satisfied_customers' => 'integer',
        'average_rating'      => 'float',
    ];

    /**
     * Hero image, virtual-tour still, goals illustration — plus the tour video.
     *
     * tour_video is registered apart from PAGE_IMAGES because it is a video file: the
     * image-only upload widget and the image validation must never pick it up.
     */
    public function registerMediaCollections(): void
    {
        foreach (self::PAGE_IMAGES as $collection) {
            $this->addMediaCollection($collection)->useDisk('public')->singleFile();
        }
        $this->addMediaCollection('tour_video')->useDisk('public')->singleFile();
    }

    /** Url of the page hero image, or '' when there is none / the file is missing. */
    public function getMainImageAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'main_image');
    }

    /** Url of the still shown before the virtual tour plays. */
    public function getTourPosterAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'tour_poster');
    }

    /** Url of the goals / vision illustration. */
    public function getGoalsImageAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'goals_image');
    }

    /** Url of the uploaded virtual-tour video (tour_video_link is the external alternative). */
    public function getTourVideoAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'tour_video');
    }

    /** The business or representation that owns the page; null means the main site. */
    public function aboutable()
    {
        return $this->morphTo();
    }

    /** Convenience accessor used by the about form's business-only sections. */
    public function getGhorfeOnlineAttribute(): ?GhorfeOnlineList
    {
        $owner = $this->aboutable;

        return $owner instanceof GhorfeOnlineList ? $owner : null;
    }

    /** Convenience accessor used when rendering representation-owned pages. */
    public function getRepresentationAttribute(): ?Representation
    {
        $owner = $this->aboutable;

        return $owner instanceof Representation ? $owner : null;
    }

    /**
     * Name of the business this page is about — the page title is derived from it, never
     * typed ("درباره کلینیک دندانپزشکی دیجیتال"), so renaming the business renames the page.
     */
    public function getBusinessNameAttribute(): string
    {
        if ( $this->ghorfeOnline ) {
            return (string) $this->ghorfeOnline->title;
        }

        if ( $this->representation ) {
            return (string) $this->representation->fa_name;
        }

        return 'لاله کارت';
    }

    /** Heading of the stats block; falls back to "چرا {کسب و کار}؟". */
    public function getWhyHeadingAttribute(): string
    {
        return $this->why_title ?: 'چرا ' . $this->business_name . '؟';
    }

    /**
     * Branch list, specialist staff and FAQ hang off the BUSINESS, not off this row —
     * they are shared with the branch and partner frontends. Null for a representation /
     * main-site page, which has no business and therefore none of those sections.
     */
    public function branches()
    {
        return $this->ghorfeOnline ? $this->ghorfeOnline->centers : collect();
    }

    /** @see branches() */
    public function specialists()
    {
        return $this->ghorfeOnline ? $this->ghorfeOnline->employees : collect();
    }

    /**
     * FAQs of this page's owner. `faqs.ghorfe_online_list_id` is null for the main site,
     * which is exactly the value this row carries then — so the same scope serves both.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function faqsQuery()
    {
        // whereNull(center_id): a branch question also has a null ghorfe_online_list_id, so
        // without this the MAIN-SITE page would adopt (and, on save, delete) every branch faq.
        return Faq::query()
            ->whereNull( 'center_id' )
            ->where( 'ghorfe_online_list_id', $this->ghorfeOnline?->id );
    }

    /**
    * Rows the current admin is allowed to see.
    *
    * Ownership is exclusive: a row morphs to a ghorfe or a representation; a null morph
    * belongs to the main lalecard site.
    *
    * - superadmin  -> everything, whoever owns it
    * - ghorfe user -> only its own ghorfe's rows (ghorfe() reads the ghorfe_online_list_user pivot)
    * - representation admin -> only its own representation's rows
    * - anyone else (لاله staff) -> the main-site rows only
    *
    * The previous body was dead code and would have fataled if ever called: it read an
    * undefined $ghorfeOnlineList and whereHas'd a `sliderable` relation About does not have.
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function scopeFilter( $query ) {
        $user = auth()->user();

        // Site-side / unauthenticated callers get the main-site rows, never a tenant's.
        if ( !$user ) {
            return $query->whereNull( 'aboutable_type' )->whereNull( 'aboutable_id' );
        }

        if ( $user->hasRole( 'superadmin' ) ) {
            return $query;
        }

        // ghorfe() resolves the logged-in user's ghorfe via the pivot; null when they have none.
        if ( $ghorfe = ghorfe() ) {
            return $query->whereMorphedTo( 'aboutable', $ghorfe );
        }

        // admin_representation_id is the representation the user *manages* (representation_id
        // is the one they merely belong to) — same column the slider/user lists scope on.
        if ( $user->admin_representation_id ) {
            return $query->whereMorphedTo( 'aboutable', $user->admin_representation );
        }

        return $query->whereNull( 'aboutable_type' )->whereNull( 'aboutable_id' );
    }
}
