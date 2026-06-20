<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

class SettingsMigrationSeeder extends Seeder
{
    public function run()
    {
        $rows = DB::table('settings_copy')->get();

        foreach ($rows as $row) {

            $data = (array) $row;

            foreach ($data as $key => $value) {

                if (in_array($key, [
                    'id',
                    'created_at',
                    'updated_at',
                ])) {
                    continue;
                }

                if (is_null($value) || $value === '') {
                    continue;
                }

                Setting::updateOrCreate(
                    [
                        'key' => $key,
                        'settingable_id' => $row->ghorfe_online_list_id,
                        'settingable_type' => 'App\Models\Ghorfe',
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }
        }
    }
}
