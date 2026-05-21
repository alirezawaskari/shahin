<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AddIconMenuSeeder extends Seeder
{
    public function run()
    {
        // reset
        DB::table('permissions')->update([
            'link' => null,
            'isMenu' => null,
        ]);

        $blade = File::get(resource_path('views/Admin/layouts/sidemenu.blade.php'));

        $lines = preg_split('/\n/', $blade);

        $stack = [];
        $activePermissions = [];
        $results = [];

        foreach ($lines as $line) {

            // شروع permission
            if (preg_match('/@permission\([\'"](.*?)[\'"]\)/', $line, $m)) {
                $perms = explode('|', $m[1]);
                array_push($stack, $perms);
            }

            // پایان permission
            if (str_contains($line, '@endpermission')) {
                array_pop($stack);
            }

            // گرفتن span name
            $categoryName = null;
            if (preg_match('/<span>(.*?)<\/span>/', $line, $spanMatch)) {
                $categoryName = trim($spanMatch[1]);
            }

            // گرفتن route
            if (preg_match('/route\([\'"](.*?)[\'"]\)/', $line, $routeMatch)) {
                $route = $routeMatch[1];
                $activePermissions = [];

                foreach ($stack as $group) {
                    $activePermissions = array_merge($activePermissions, $group);
                }

                foreach ($activePermissions as $perm) {
                    $perm = trim($perm);

                    if (!isset($results[$perm])) {
                        $results[$perm] = [
                            'route' => $route,
                            'category' => $categoryName,
                            'isMenu' => 1
                        ];
                    }
                }
            }
        }

        // اتصال به category_permissions
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
