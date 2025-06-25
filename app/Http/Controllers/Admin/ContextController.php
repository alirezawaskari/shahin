<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Admin\ContextTraits\ExpireDates;
use App\Http\Controllers\Admin\ContextTraits\SiteRules;
use App\Http\Controllers\Controller;
use App\Models\Context;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ContextController extends Controller
{
    use SiteRules;
    use ExpireDates;
    /**
     *array list of domain expire date key
     */
    const DOMAINS_EXPIRE_DATE_KEYS = array(
        'domain_expire_date_lalecard.ir',
        'domain_expire_date_lalecard.com',
        'domain_expire_date_ghorfe-online.ir',
        'domain_expire_date_lalehkart.ir',
        'domain_expire_date_lalekart.ir',
        'domain_expire_date_lalemarket.ir',
        'domain_expire_date_qorfe-online.ir',
        'domain_expire_date_lalebank.com',
    );

    /**
     *array lis of other expire date keys
     */
    const OTHER_EXPIRE_DATE_KEYS = array(
        'vps_expire_date',
        'plesk_expire_date',
    );

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function settings()
    {
        $this->generateSettingsKeys();
        $expire_dates=self::fetchExpireDates();
        $company_info= Setting::select('address','call','email','shaba_number')->first();

        return view('Admin.settings.index', compact('expire_dates','company_info'));
    }


    /**
     *generate Settings Keys
     */
    private function generateSettingsKeys(): void
    {
        $keys1 = self::DOMAINS_EXPIRE_DATE_KEYS;
        $keys2 = self::OTHER_EXPIRE_DATE_KEYS;
        $keys = array_merge($keys1, $keys2);
        foreach ($keys as $key) {
            $context = Context::where('key', $key)->first();
            if (is_null($context)) {
                Context::create(
                    ['key' => $key, 'value' => Carbon::now()]
                );
            }
        }
    }
}
