<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            // SERVICE MENUS
            ['id' => 1, 'title' => 'خدمات', 'link' => '/services', 'sort' => 1, 'icon' => null, 'type' => 'service'],
            ['id' => 2, 'title' => 'همکاران', 'link' => '/colleagues', 'sort' => 2, 'icon' => null, 'type' => 'service'],
            ['id' => 3, 'title' => 'کالاها', 'link' => '/products', 'sort' => 3, 'icon' => '/assets/images/data/menu/Menu-2.png', 'type' => 'product'],
            ['id' => 4, 'title' => 'وبلاگ', 'link' => '/blogs', 'sort' => 4, 'icon' => '/assets/images/data/menu/Menu-3.png', 'type' => 'service'],
            ['id' => 5, 'title' => 'درباره ما', 'link' => '/about-us', 'sort' => 5, 'icon' => '/assets/images/data/menu/Menu-4.png', 'type' => 'service'],
            ['id' => 6, 'title' => 'تماس باما', 'link' => '/contact-us', 'sort' => 6, 'icon' => '/assets/images/data/menu/Menu-5.png', 'type' => 'service'],

            // PRODUCT MENUS
            ['id' => 11, 'title' => 'دسته بندی ها', 'link' => '/category', 'sort' => 1, 'icon' => '/assets/images/data/menu/Menu-1.png', 'type' => 'product'],
            ['id' => 7, 'title' => 'تخفیفات', 'link' => '/products', 'sort' => 2, 'icon' => '/assets/images/data/menu/Menu-2.png', 'type' => 'product'],
            ['id' => 8, 'title' => 'وبلاگ', 'link' => '/blogs', 'sort' => 3, 'icon' => '/assets/images/data/menu/Menu-3.png', 'type' => 'product'],
            ['id' => 9, 'title' => 'درباره ما', 'link' => '/about-us', 'sort' => 4, 'icon' => '/assets/images/data/menu/Menu-4.png', 'type' => 'product'],
            ['id' => 10, 'title' => 'تماس باما', 'link' => '/contact-us', 'sort' => 5, 'icon' => '/assets/images/data/menu/Menu-5.png', 'type' => 'product'],
        ];

        $productTemplates = DB::table('templates')
            ->where('type', 'product')
            ->get();

        $serviceTemplates = DB::table('templates')
            ->where('type', 'service')
            ->get();

        $pivot = [];

        foreach ($menus as $menu) {

            DB::table('menus')->updateOrInsert(
                ['id' => $menu['id']],
                [
                    'title' => $menu['title'],
                    'link' => $menu['link'],
                    'sort' => $menu['sort'],
                    'icon' => $menu['icon'] ?? '/assets/images/default.png',
                ]
            );

            $templates = $menu['type'] === 'product'
                ? $productTemplates
                : $serviceTemplates;

            foreach ($templates as $template) {
                $pivot[] = [
                    'menu_id' => $menu['id'],
                    'template_id' => $template->id,
                ];
            }
        }

        DB::table('menu_template')->upsert(
            $pivot,
            ['menu_id', 'template_id']
        );
    }
}