<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Traits\ResolvesOwner;
use App\Models\Setting;

class SettingController extends AdminController
{
    use ResolvesOwner;

    public function index()
    {
        $settingsKeys = [
            'address',
            'call',
            'email',
            'phone',
            'shaba_number',
            'card_number',
            'card_holder',
            'tax_free_shaba_number',
            'tax_free_card_number',
            'tax_free_card_holder',
            'sms_panel',
        ];

        $settings = collect($settingsKeys)
            ->mapWithKeys(fn ($key) => [$key => settings($key)])
            ->toArray();

        return view('Admin.setting.edit', compact('settings'));
    }

    public function updateCompanyInfo(Request $request)
    {
        $owner = $this->getOwner();

        if (!$owner) {
            abort(404);
        }

        $payload = collect($request->only([
            'address',
            'phone',
            'call',
            'email',
            'shaba_number',
            'card_number',
            'card_holder',
            'tax_free_shaba_number',
            'tax_free_card_number',
            'tax_free_card_holder',
        ]))
            ->filter(fn ($value) => filled($value))
            ->toArray();

        if (!empty($payload)) {
            $this->saveSettings($owner, $payload);
            $this->clearCache($owner);
        }

        alert()->success('', 'عملیات موفق');

        return back();
    }

    public function updateSmsSetting(Request $request)
    {
        $owner = $this->getOwner();

        if (!$owner) {
            abort(404);
        }

        if (filled($request->sms_panel)) {
            $this->saveSettings($owner, [
                'sms_panel' => $request->sms_panel,
            ]);

            $this->clearCache($owner);
        }

        alert()->success('', 'عملیات موفق');

        return back();
    }

    private function saveSettings($owner, array $fields)
    {
        foreach ($fields as $key => $value) {

            $owner->settings()->updateOrCreate(
                [
                    'key' => $key,
                    'settingable_id' => $owner->id,
                    'settingable_type' => $owner::class,
                ],
                [
                    'value' => $value,
                ]
            );
        }
    }

    private function clearCache($owner)
    {
        match (true) {
            $owner instanceof \App\Models\GhorfeOnlineList =>
            Cache::forget('settings_ghorfe_' . $owner->id),

            $owner instanceof \App\Models\Representation =>
            Cache::forget('settings_rep_' . $owner->id),

            default => null,
        };
    }
}
