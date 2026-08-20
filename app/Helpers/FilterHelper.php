<?php

namespace App\Helpers;

use App\Services\V1\Auth\AdminAuthService;

class FilterHelper {

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
     */
    public static function filterWithHaveGhorfeOnline($query , $relation_type , $morph_variable)
    {
        $ghorfe_online_list = ghorfe();
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
        elseif(auth()->user() && auth()->user()->level == 'admin')
        {
            return self::filterWithDosentHaveGhorfeOnline($query , $relation_type , $morph_variable);
        }
        elseif(auth()->user() && auth()->user()->level == 'superAdmin')
        {
            return $query;
        }
        return null;
    }
}
