<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FixPermissionLinksSeeder extends Seeder
{
    public function run()
    {
        DB::table('permissions')->update([
            'link' => null,
            'isMenu' => null,
        ]);

        $blade = File::get(resource_path('views/Admin/layouts/sidem.blade.php'));
        $lines = preg_split('/\n/', $blade);

        $permissionStack = [];
        $categoryStack = [];

        $results = [];

        foreach ($lines as $line) {

            // =========================
            // permission start
            // =========================
            if (preg_match('/@permission\([\'"](.*?)[\'"]\)/', $line, $m)) {
                $perms = explode('|', $m[1]);
                array_push($permissionStack, $perms);
            }

            // =========================
            // permission end
            // =========================
            if (str_contains($line, '@endpermission')) {
                array_pop($permissionStack);
            }

            // =========================
            // category (menu title)
            // =========================
            if (preg_match('/<span>(.*?)<\/span>/', $line, $spanMatch)) {
                $category = trim($spanMatch[1]);
                array_push($categoryStack, $category);
            }

            // وقتی </li> بسته میشه category رو pop کن
            if (str_contains($line, '</li>') && count($categoryStack)) {
                array_pop($categoryStack);
            }

            if (preg_match('/route\([\'"](.*?)[\'"]\)/', $line, $routeMatch)) {

                $route = $routeMatch[1];

                // فقط permission مستقیم
                $currentPermissions = end($permissionStack);

                // اگر این route داخل permission مستقیم نیست → رد کن
                if (!$currentPermissions) {
                    continue;
                }

                $currentCategory = end($categoryStack);

                foreach ($currentPermissions as $perm) {

                    $perm = trim($perm);

                    if (!isset($results[$perm])) {
                        $results[$perm] = [
                            'route' => $route,
                            'category' => $currentCategory,
                            'isMenu' => 1
                        ];
                    }
                }
            }
        }

        // =========================
        // save to DB
        // =========================
        foreach ($results as $perm => $data) {

            DB::table('permissions')
                ->where('name', $perm)
                ->update([
                    'link' => $data['route'] ?? '#',
                    'isMenu' => $data['isMenu'] ?? 0,
                ]);
        }
    }
}
