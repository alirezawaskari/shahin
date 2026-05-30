<?php

namespace App\Helpers;

use App\Services\V1\Auth\AuthService;

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
        $ghorfe_online_list = (new AuthService)->getGhorfe();
        switch($relation_type)
        {
            case('belongsToMany'):
                $query = $query->whereHas('ghorfeOnlineLists', function ($q) use ($ghorfe_online_list) {
                    $q->where('id', $ghorfe_online_list->id);
                });
                break;
            case('morphTo'):
                $query = $query->Where($morph_variable.'_id', $ghorfe_online_list->id)->Where($morph_variable.'_type', 'App\Models\GhorfeOnlineList');
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
        if((new AuthService)->getGhorfe())
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
