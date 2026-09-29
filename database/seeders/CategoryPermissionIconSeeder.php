<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryPermissionIconSeeder extends Seeder
{
    public function run()
    {
        $items = [
            ['name' => 'کاربران', 'icon' => 'ti-user'],
            ['name' => 'محتوای سایت', 'icon' => 'ti-file'],
            ['name' => 'تراکنش ها', 'icon' => 'ti-credit-card'],
            ['name' => 'درخواست ها', 'icon' => 'ti-comments'],
            ['name' => 'سایر', 'icon' => 'ti-more'],
            ['name' => 'نمایندگی', 'icon' => 'ti-star'],
            ['name' => 'خدمات', 'icon' => 'ti-medall'],
            ['name' => 'پرونده', 'icon' => 'ti-folder'],
            ['name' => 'محصولات', 'icon' => 'ti-package'],
            ['name' => 'انبار', 'icon' => 'ti-archive'],
            ['name' => 'تیکت', 'icon' => 'ti-ticket'],
            ['name' => 'جلسات', 'icon' => 'ti-calendar'],
            ['name' => 'قرارداد', 'icon' => 'ti-file'],
            ['name' => 'تاریخچه کاربران', 'icon' => 'ti-time'],
            ['name' => 'داشبورد', 'icon' => 'ti-dashboard'],
            ['name' => 'امنیت', 'icon' => 'ti-shield'],
            ['name' => 'پیامک', 'icon' => 'ti-comments-smiley'],
            ['name' => 'بخشودگی', 'icon' => 'ti-tag'],
            ['name' => 'مستاجرین و شرکای تجاری', 'icon' => 'ti-briefcase'],
            ['name' => 'خزانه', 'icon' => 'ti-money'],
            ['name' => 'تنظیمات', 'icon' => 'ti-settings'],
            ['name' => 'تسهیم درصدی درآمد', 'icon' => 'ti-pie-chart'],
            ['name' => 'تسهیم مبلغی همکاران مستاجرین و شرکای تجاری', 'icon' => 'ti-wallet'],
            ['name' => 'مغازه دیجیتال', 'icon' => 'ti-shopping-cart'],
            ['name' => 'سازمان ها', 'icon' => 'ti-wand'],
            ['name' => 'همکاران', 'icon' => 'ti-user'],
            ['name' => 'رزومه', 'icon' => 'ti-bookmark'],
        ];

        foreach ($items as $row) {
            DB::table('category_permissions')->updateOrInsert(
                ['name' => $row['name']],
                ['icon' => $row['icon']]
            );
        }
    }
}
