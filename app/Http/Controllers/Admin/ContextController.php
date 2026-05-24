<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Context;
use App\Services\Settings\SettingsService;
use App\Traits\ResolvesOwner;
use App\Http\Controllers\Admin\ContextTraits\ExpireDates;
use App\Http\Controllers\Admin\ContextTraits\SiteRules;

class ContextController extends Controller
{
    use SiteRules, ExpireDates, ResolvesOwner;
    protected $settings;

    public function __construct(SettingsService $settings)
    {
        $this->settings = $settings;
    }

    const DOMAINS_EXPIRE_DATE_KEYS = [
        'domain_expire_date_lalecard.ir',
        'domain_expire_date_lalecard.com',
        'domain_expire_date_ghorfe-online.ir',
        'domain_expire_date_lalehkart.ir',
        'domain_expire_date_lalekart.ir',
        'domain_expire_date_lalemarket.ir',
        'domain_expire_date_qorfe-online.ir',
        'domain_expire_date_lalebank.com',
    ];

    const OTHER_EXPIRE_DATE_KEYS = [
        'vps_expire_date',
        'plesk_expire_date',
    ];

    /*
    |-----------------------------------
    | SETTINGS PAGE
    |-----------------------------------
    */
    public function settings()
    {
        $company_info = [
            'address' => $this->settings->get('address'),
            'phone' => $this->settings->get('phone'),
            'email' => $this->settings->get('email'),
            'shaba_number' => $this->settings->get('shaba_number'),
            'card_number' => $this->settings->get('card_number'),
            'card_holder' => $this->settings->get('card_holder'),
            'tax_free_shaba_number' => $this->settings->get('tax_free_shaba_number'),
            'tax_free_card_number' => $this->settings->get('tax_free_card_number'),
            'tax_free_card_holder' => $this->settings->get('tax_free_card_holder'),
            'sms_panel' => $this->settings->get('sms_panel'),
        ];

        return view('Admin.settings.index', compact('company_info'));
    }

    /*
    |-----------------------------------
    | MAP SETTINGS
    |-----------------------------------
    */
    private function mapCompanyInfo(): array
    {
        $keys = [
            'address',
            'call',
            'email',
            'shaba_number',
            'card_number',
            'card_holder',
            'tax_free_shaba_number',
            'tax_free_card_number',
            'tax_free_card_holder',
        ];

        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->settings->get($key);
        }

        return $result;
    }
    /*
    |-----------------------------------
    | GENERATE CONTEXT KEYS
    |-----------------------------------
    */
    private function generateSettingsKeys(): void
    {
        $keys = array_merge(
            self::DOMAINS_EXPIRE_DATE_KEYS,
            self::OTHER_EXPIRE_DATE_KEYS
        );

        $now = now();

        $rows = array_map(fn ($key) => [
            'key' => $key,
            'value' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ], $keys);

        Context::upsert($rows, ['key'], ['value', 'updated_at']);
    }
}
