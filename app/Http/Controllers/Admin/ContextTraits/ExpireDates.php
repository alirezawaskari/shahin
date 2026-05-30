<?php

namespace App\Http\Controllers\Admin\ContextTraits;

use App\Helpers\Helper;
use App\Models\Context;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

trait ExpireDates
{
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
        Cache::forget('expire_dates');
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
        $with = [
            'update' => 'success',
            'active_tab'=> 'official_info'
        ];

        return redirect()->back()->with($with);
    }

}
