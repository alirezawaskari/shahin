<?php

namespace App\Helpers;

use App\Models\GhorfeOnlineList;
use App\Services\V1\Auth\AdminAuthService;

class FilterHelper {

    /**
     * The query-string key the «کسب و کار» filter posts under. The picker partial
     * (Admin.filter.businessSelect) writes it, selectedGhorfe() reads it back — nothing else
     * should hard-code the string.
     */
    public const GHORFE_REQUEST_KEY = 'ghorfe_online_list';

    /**
     * `users.level` of لاله's own staff. They are the only accounts the business filter is
     * offered to, and the only ones whose picked business is honoured server-side — the
     * check is repeated in selectedGhorfe() so a hand-written query string buys nothing.
     */
    private const STAFF_LEVELS = [ 'admin', 'superAdmin' ];

    /** Request-attribute key the resolved business is memoised under for the rest of the request. */
    private const SELECTED_CACHE_KEY = 'filter_helper.selected_ghorfe';

    /**
     * Summary of filterWithDosentHaveGhorfeOnline
     * @param mixed $query
     * @param mixed $relation_type
     * @param mixed $morph_variable
     */
    public static function filterWithDosentHaveGhorfeOnline($query , $relation_type , $morph_variable)
    {
        switch($relation_type)
        {
            case('belongsToMany'):
                $query = $query->whereDoesntHave('ghorfeOnlineLists');
                break;
            case('morphTo'):
                $query = $query->Where($morph_variable.'_type', '!=' , 'App\Models\GhorfeOnlineList');
                break;
            case('belongsTo'):
                $query = $query->where( 'ghorfe_online_list_id', null );
                break;
        }
        return $query;
    }
    /**
     * Summary of filterWithHaveGhorfeOnline
     * @param mixed $query
     * @param mixed $relation_type
     * @param mixed $morph_variable
     * @param \App\Models\GhorfeOnlineList|null $ghorfe_online_list the business to scope to;
     *        the signed-in user's own one when not given (the tenancy case)
     */
    public static function filterWithHaveGhorfeOnline($query , $relation_type , $morph_variable , $ghorfe_online_list = null)
    {
        $ghorfe_online_list = $ghorfe_online_list ?: ghorfe();
        if (!$ghorfe_online_list) {
            return $query;
        }
        switch($relation_type)
        {
            case('belongsToMany'):
                $query = $query->whereHas('ghorfeOnlineLists', function ($q) use ($ghorfe_online_list) {
                    // qualify the key: whereHas on a belongsToMany joins the pivot table,
                    // so an unqualified `id` is ambiguous between it and ghorfe_online_lists.
                    $q->whereKey($ghorfe_online_list->id);
                });
                break;
            case('morphTo'):
                $query = $query->Where($morph_variable.'_id', $ghorfe_online_list->id)->Where($morph_variable.'_type', 'App\Models\GhorfeOnlineList');
                break;
            // the mirror of the belongsTo case above: a model that names its business in a
            // ghorfe_online_list_id column of its own (employees)
            case('belongsTo'):
                $query = $query->where('ghorfe_online_list_id', $ghorfe_online_list->id);
                break;
        }
        return $query;
    }
    /**
     * Summary of getDataByCheckGhorfeExistOrNot
     * @param mixed $query
     * @param mixed $relation_type
     * @param mixed $morph_variable
     */
    public static function getDataByCheckGhorfeExistOrNot($query , $relation_type , $morph_variable)
    {
        if(ghorfe())
        {
            return self::filterWithHaveGhorfeOnline($query , $relation_type , $morph_variable);
        }

        // The «کسب و کار» list filter. Checked before the level branches below so that a
        // picked business narrows the list for both staff levels; when nothing is picked
        // selectedGhorfe() is null and every branch behaves exactly as it did before.
        $selected_ghorfe = self::selectedGhorfe();
        if ($selected_ghorfe)
        {
            return self::filterWithHaveGhorfeOnline($query , $relation_type , $morph_variable , $selected_ghorfe);
        }

        if(auth()->user() && auth()->user()->level == 'admin')
        {
            return self::filterWithDosentHaveGhorfeOnline($query , $relation_type , $morph_variable);
        }
        elseif(auth()->user() && auth()->user()->level == 'superAdmin')
        {
            return $query;
        }
        return null;
    }

    /**
     * May the signed-in user filter lists by business?
     *
     * Only لاله's own staff, and only while they are not pinned to a business themselves:
     * tenancy is checked FIRST, exactly as it is in getDataByCheckGhorfeExistOrNot(), because
     * `admin` / `superAdmin` is a permission bundle a business admin can hold too. Handing
     * one of those the picker would have let them read every other business's rows.
     *
     * @return bool
     */
    public static function canFilterByGhorfe(): bool
    {
        $user = auth()->user();

        return $user && in_array($user->level, self::STAFF_LEVELS, true) && !ghorfe();
    }

    /**
     * The businesses the picker offers — active ones only, empty for anybody who may not
     * filter at all (which is what makes the partial render nothing for them).
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public static function ghorfeFilterOptions()
    {
        if (!self::canFilterByGhorfe()) {
            return collect();
        }

        return GhorfeOnlineList::active()->orderBy('title')->get(['id', 'title']);
    }

    /**
     * The business picked on the current request, or null — no pick, not allowed to pick, or
     * an id that is not an active business (a stale bookmark, a hand-written query string).
     * Null always means "leave the list alone", never "show nothing".
     *
     * Memoised on the request: the helper runs once per scopeFilter() and a single page can
     * call several of them.
     *
     * @return \App\Models\GhorfeOnlineList|null
     */
    public static function selectedGhorfe()
    {
        $attributes = request()->attributes;

        if ($attributes->has(self::SELECTED_CACHE_KEY)) {
            return $attributes->get(self::SELECTED_CACHE_KEY);
        }

        $selected = self::resolveSelectedGhorfe();
        $attributes->set(self::SELECTED_CACHE_KEY, $selected);

        return $selected;
    }

    /**
     * @return \App\Models\GhorfeOnlineList|null
     */
    private static function resolveSelectedGhorfe()
    {
        if (!self::canFilterByGhorfe()) {
            return null;
        }

        $id = request(self::GHORFE_REQUEST_KEY);

        // 'all' is the picker's own "no business" option; the rest guards against arrays and
        // the empty strings a plain GET form submits.
        if (!is_scalar($id) || trim((string) $id) === '' || $id === 'all') {
            return null;
        }

        return GhorfeOnlineList::active()->find($id);
    }
}
