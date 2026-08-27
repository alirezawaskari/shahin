<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\MenuSettingCollection;
use App\Models\CategoryPermission;
use App\Models\Permission;
use App\Services\V1\Admin\MenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class MenuSettingController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        if (auth()->user()->hasPermission('menu_setting'))
        {
            return view( 'Admin.menuSetting.all');
        }
        return abort(401);
    }

    /**
     * The list behind /dashboard/menuSetting, in the same order as the sidebar.
     *
     * @return \App\Http\Resources\MenuSettingCollection
     */
    public function filter(Request $request)
    {
        $menuSetting = CategoryPermission::with('permission')
            ->withCount('permission')
            ->when(filled($request->title), function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->title . '%');
            })
            ->orderBy('position')
            ->orderBy('name');

        return new MenuSettingCollection($menuSetting->paginate(20));
    }

    /**
     * Save one sub-menu row from the menu edit page.
     *
     * The English `name` is deliberately absent from everything below — it is the
     * Laratrust key that roles and @permission() checks are matched on, so it must never
     * be rewritten from this screen.
     */
    public function updateSubMenu(Request $request)
    {
        if (! auth()->user()->hasPermission('menu_setting_edit')) {
            return response()->json(['message' => 'امکان ویرایش برای شما وجود ندارد.'], 403);
        }

        $data = $request->validate([
            'id'           => ['required', Rule::exists('permissions', 'id')],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:255'],
            'position'     => ['nullable', 'integer', 'min:0'],
            'is_active'    => ['required', 'boolean'],
            // A sub-menu that is switched on has to be reachable. This is the same test the
            // sidebar applies, so the form can never accept a link the menu would then drop:
            // it rejects unknown names and routes that cannot be built without parameters.
            'link'         => [
                Rule::requiredIf(fn() => (bool) $request->boolean('is_active')),
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (blank($value)) {
                        return;
                    }

                    if (! Route::has($value)) {
                        $fail('لینک وارد شده معتبر نیست: مسیری با نام «' . $value . '» وجود ندارد.');
                        return;
                    }

                    if (! MenuService::linkToUrl($value)) {
                        $fail('مسیر «' . $value . '» برای ساخته شدن به پارامتر نیاز دارد و نمی‌تواند لینک منو باشد.');
                    }
                },
            ],
        ], [
            'link.required' => 'برای نمایش این زیرمنو باید یک لینک معتبر وارد کنید.',
        ]);

        $permission = Permission::findOrFail($data['id']);

        $permission->update([
            'display_name' => $data['display_name'] ?? null,
            'description'  => $data['description'] ?? null,
            'link'         => $data['link'] ?? null,
            'position'     => $data['position'] ?? 0,
            // isMenu is the switch the sidebar actually reads. (permissions.is_active is a
            // leftover column nothing queries — the form used to show it and save isMenu,
            // so the select never reflected what had been saved.)
            'isMenu'       => (int) $data['is_active'],
        ]);

        MenuService::flush();

        return response()->json([
            'update'  => 'success',
            'message' => 'ذخیره شد',
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        if (auth()->user()->hasPermission('menu_setting_edit'))
        {
            $menuSetting = CategoryPermission::with(['permission' => function ($query) {
                $query->orderBy('position')->orderBy('id');
            }])->findOrFail($id);

            // Offered to the لینک field as a datalist. Only routes that can actually be
            // turned into a menu URL are listed — a name needing a {parameter} is no use
            // here, and the validator would reject it anyway.
            $routeNames = collect(Route::getRoutes())
                ->map(fn($route) => $route->getName())
                ->filter()
                ->unique()
                ->filter(fn($name) => MenuService::linkToUrl($name) !== null)
                ->sort()
                ->values();

            return view('Admin.menuSetting.edit' , compact('menuSetting', 'routeNames'));
        }
        return abort(401);
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->hasPermission('menu_setting_edit')) {
            return redirect()->back()->with([
                'failByMessage' => 'امکان ویرایش برای شما وجود ندارد.',
            ]);
        }

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:128'],
            'icon'      => ['nullable', 'string', 'max:255'],
            'position'  => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        // The model is $guarded = [], so the request must never be passed through whole.
        $categoryPermission = CategoryPermission::findOrFail($id);
        $categoryPermission->update([
            'name'      => $data['name'],
            'icon'      => $data['icon'] ?? $categoryPermission->icon,
            'position'  => $data['position'] ?? 0,
            'is_active' => (int) $data['is_active'],
        ]);

        MenuService::flush();

        return redirect()->back()->with([
            'update' => 'success',
        ]);
    }
}
