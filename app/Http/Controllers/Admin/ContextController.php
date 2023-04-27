<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Admin\ContextTraits\SiteRules;
use App\Http\Controllers\Controller;
use App\Models\Context;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ContextController extends Controller
{
    use SiteRules;

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
        return view('Admin.settings.index', compact('expire_dates'));
    }

    /**
     * fetch expire dates
     * @return array
     */
    public static function fetchExpireDates():array
    {
        $expire_dates = Context::whereIn('key', array_merge(self::DOMAINS_EXPIRE_DATE_KEYS, self::OTHER_EXPIRE_DATE_KEYS))->get();
       return  Context::convertFetchedContextToKeyValue($expire_dates);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function updateExpireDates(Request $request)
    {
        $requests = array(
            'domain_expire_date_lalecard.ir' => $request->domain_expire_date_lalecard_ir,
            'domain_expire_date_lalecard.com' => $request->domain_expire_date_lalecard_com,
            'domain_expire_date_ghorfe-online.ir' => $request->domain_expire_date_ghorfe_online_ir,
            'domain_expire_date_lalehkart.ir' => $request->domain_expire_date_lalehkart_ir,
            'domain_expire_date_lalekart.ir' => $request->domain_expire_date_lalekart_ir,
            'domain_expire_date_lalemarket.ir' => $request->domain_expire_date_lalemarket_ir,
            'domain_expire_date_qorfe-online.ir' => $request->domain_expire_date_qorfe_online_ir,
            'domain_expire_date_lalebank.com' => $request->domain_expire_date_lalebank_com,
            'vps_expire_date'=>$request->vps_expire_date,
            'plesk_expire_date'=>$request->plesk_expire_date,
        );
        foreach ($requests as $key => $value) {
            $data[] = array('key' => $key, 'value' => Helper::vertaToMiladi($value));
        }
        Context::updateContext($data);
        alert()->success(' ', 'عملیات موفق ')->autoclose(3000)->persistent('باشه');
        return redirect()->back();
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
