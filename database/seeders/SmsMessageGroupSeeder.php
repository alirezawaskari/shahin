<?php

namespace Database\Seeders;

use App\Models\SmsMessageGroup;
use Illuminate\Database\Seeder;

class SmsMessageGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'name' => 'مالی',
                'slug' => 'mali',
                'subgroups' => [
                    ['name' => 'واریز پیشپرداخت', 'slug' => 'variz-pish-pardakht'],
                    ['name' => 'پرداخت فاکتور', 'slug' => 'pardakht-factor'],
                ],
            ],
            [
                'name' => 'حقوقی',
                'slug' => 'hoghoghi',
                'subgroups' => [
                    ['name' => 'اخطار', 'slug' => 'ekhtar'],
                    ['name' => 'ارجاع به حقوقی', 'slug' => 'erja-be-hoghoghi'],
                ],
            ],
            [
                'name' => 'پشتیبانی',
                'slug' => 'poshtibani',
                'subgroups' => [
                    ['name' => 'عدم پاسخگویی به تماس', 'slug' => 'adam-pasokhgooei-be-tamas'],
                ],
            ],
            [
                'name' => 'اجرایی',
                'slug' => 'ejraei',
                'subgroups' => [
                    ['name' => 'نوبتدهی', 'slug' => 'nobat-dehi'],
                    ['name' => 'تأیید جلسه', 'slug' => 'taeed-jalase'],
                ],
            ],
            [
                'name' => 'فروش',
                'slug' => 'foroush',
                'subgroups' => [
                    ['name' => 'پیگیری مشتری', 'slug' => 'peygiri-moshtari'],
                ],
            ],
        ];

        foreach ($groups as $groupData) {
            $parent = SmsMessageGroup::firstOrCreate(
                ['slug' => $groupData['slug']],
                [
                    'name' => $groupData['name'],
                    'parent_id' => null,
                ]
            );

            // In case it already existed without name update
            $parent->update(['name' => $groupData['name']]);

            foreach ($groupData['subgroups'] as $subData) {
                SmsMessageGroup::firstOrCreate(
                    [
                        'parent_id' => $parent->id,
                        'slug' => $subData['slug'],
                    ],
                    [
                        'name' => $subData['name'],
                    ]
                );
            }
        }
    }
}
