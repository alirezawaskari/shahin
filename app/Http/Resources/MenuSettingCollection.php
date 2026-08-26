<?php

namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Route;


class MenuSettingCollection extends ResourceCollection
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($item) {

                // Sub-menus that are switched on but cannot be reached — the one thing that
                // silently breaks the sidebar — are surfaced on the list.
                $brokenLinks = $item->permission
                    ->where('isMenu', true)
                    ->filter(fn($permission) => blank($permission->link) || ! Route::has($permission->link))
                    ->count();

                return [
                    'sort_by' => "updated_at",
                    'id' => $item->id,
                    'name' => $item->name,
                    'icon' => $item->icon,
                    'position' => $item->position,
                    'is_active' => (bool) $item->is_active,
                    'permissions_count' => $item->permission_count ?? $item->permission->count(),
                    'menu_items_count' => $item->permission->where('isMenu', true)->count(),
                    'broken_links' => $brokenLinks,
                    'updated_at' => Helper::jalaliDate($item->updated_at) ?? '-',
                ];
            }),
        ];
    }

}
