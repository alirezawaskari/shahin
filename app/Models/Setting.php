<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $guarded = [];

    /*
    |-----------------------------------
    | GLOBAL FILLABLE (اگر واقعاً لازم داری)
    |-----------------------------------
    */
    protected $fillable = [
        'key',
        'value',
    ];

    /*
    |-----------------------------------
    | MORPH RELATION
    |-----------------------------------
    */
    public function settingable()
    {
        return $this->morphTo();
    }

    /*
    |-----------------------------------
    | GLOBAL SETTINGS CACHE
    |-----------------------------------
    */
    public static function getGlobalCached()
    {
        return Cache::rememberForever('settings_global', function () {
            return self::whereNull('settingable_id')
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /*
    |-----------------------------------
    | COMPANY SETTINGS CACHE
    |-----------------------------------
    */
    public static function getCompanyCached($companyId)
    {
        return Cache::rememberForever("settings_company_{$companyId}", function () use ($companyId) {
            return self::where('settingable_type', \App\Models\GhorfeOnlineList::class)
                ->where('settingable_id', $companyId)
                ->pluck('value', 'key')
                ->toArray();
        });
    }
}
