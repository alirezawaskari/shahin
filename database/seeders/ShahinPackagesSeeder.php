<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;


class ShahinPackagesSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = DB::table('category_permissions')->get();

        DB::table('shahin_packages')->insert([
            [
                'name' => 'پکیج عمومی',
                'slug' => 'general-package',
                'type' => 'general',
                'description' => 'پکیج عمومی برای استفاده پایه',
                'price' => 100000.00,
                'duration_days' => 30,
                'features' => json_encode([
                    'دسترسی پایه',
                    'پشتیبانی معمولی',
                ]),
                'is_active' => true,
                'image' => 'packages/general.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'پکیج فروشگاه',
                'slug' => 'store-package',
                'type' => 'store',
                'description' => 'مخصوص فروشگاه‌ها',
                'price' => 250000.00,
                'duration_days' => 60,
                'features' => json_encode([
                    'مدیریت محصولات',
                    'گزارش فروش',
                ]),
                'is_active' => true,
                'image' => 'packages/store.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'پکیج داروخانه',
                'slug' => 'medicine-package',
                'type' => 'medicine',
                'description' => 'مخصوص داروخانه‌ها',
                'price' => 300000.00,
                'duration_days' => 90,
                'features' => json_encode([
                    'مدیریت داروها',
                    'انبارداری پیشرفته',
                ]),
                'is_active' => true,
                'image' => 'packages/medicine.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $packages = DB::table('shahin_packages')->get();

        foreach ($packages as $package) {
            foreach ($permissions as $permission) {
                DB::table('category_permission_shahin_package')->insert([
                    'category_permission_id' => $permission->id,
                    'shahin_package_id' => $package->id,
                ]);
            }
        }
    }
}
