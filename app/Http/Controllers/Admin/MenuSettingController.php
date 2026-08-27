<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\MenuSettingCollection;
use App\Models\CategoryPermission;
use App\Models\Permission;
use App\Models\Role;
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
     * The "ایجاد منو جدید" form. A menu here is a CategoryPermission — the top-level group
     * the sidebar renders, with the sub-menus of the edit screen hanging off it.
     */
    public function create()
    {
        if (! auth()->user()->hasPermission('menu_setting_edit')) {
            return abort(401);
        }

        return view('Admin.menuSetting.create');
    }

    /**
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        if (! auth()->user()->hasPermission('menu_setting_edit')) {
            return abort(401);
        }

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:128', Rule::unique('category_permissions', 'name')->whereNull('deleted_at')],
            'icon'      => ['nullable', 'string', 'max:255'],
            'position'  => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.unique' => 'منویی با این عنوان از قبل وجود دارد.',
        ]);

        $menuSetting = CategoryPermission::create([
            'name'      => $data['name'],
            'icon'      => $data['icon'] ?? null,
            'position'  => $data['position'] ?? 0,
            'is_active' => (int) $data['is_active'],
        ]);

        MenuService::flush();

        // Straight to the edit screen: a menu with no sub-menus does not appear in the
        // sidebar yet, so adding them is the obvious next step.
        return redirect()->route('menuSetting.edit', $menuSetting->id)->with([
            'update' => 'success',
        ]);
    }

    /**
     * Add a sub-menu to an existing menu.
     *
     * Unlike updateSubMenu(), the English `name` is accepted here — it is being created,
     * not renamed. It becomes the Laratrust permission key, so it is validated as a plain
     * snake_case identifier and must be unique.
     *
     * @return RedirectResponse
     */
    public function storeSubMenu(Request $request, $menuSettingId)
    {
        if (! auth()->user()->hasPermission('menu_setting_edit')) {
            return abort(401);
        }

        $menuSetting = CategoryPermission::findOrFail($menuSettingId);

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255', 'regex:/^[A-Za-z][A-Za-z0-9_.-]*$/', Rule::unique('permissions', 'name')],
            'display_name' => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:255'],
            'position'     => ['nullable', 'integer', 'min:0'],
            'is_active'    => ['required', 'boolean'],
            'link'         => [
                Rule::requiredIf(fn() => (bool) $request->boolean('is_active')),
                'nullable',
                'string',
                fn($attribute, $value, $fail) => $this->validateMenuLink($value, $fail),
            ],
        ], [
            'name.regex'    => 'نام انگلیسی باید با حرف شروع شود و فقط شامل حروف انگلیسی، عدد، _ ، . و - باشد.',
            'name.unique'   => 'دسترسی با این نام انگلیسی از قبل وجود دارد.',
            'link.required' => 'برای نمایش این زیرمنو باید یک لینک معتبر وارد کنید.',
        ]);

        $permission = Permission::create([
            'name'         => $data['name'],
            'display_name' => $data['display_name'],
            'description'  => $data['description'] ?? null,
            'link'         => $data['link'] ?? null,
            'cat_id'       => $menuSetting->id,
            // Default to the end of the group rather than the top.
            'position'     => $data['position'] ?? ((int) Permission::where('cat_id', $menuSetting->id)->max('position') + 1),
            'isMenu'       => (int) $data['is_active'],
        ]);

        // A brand new permission belongs to no role, so whoever just created it could not
        // pass an @permission() check against it. Grant it to superadmin — the role that is
        // meant to hold everything — and leave the rest to the نقش ها screen.
        $superadmin = Role::where('name', 'superadmin')->first();

        if ($superadmin) {
            $superadmin->permissions()->syncWithoutDetaching([$permission->id]);
        }

        MenuService::flush();

        return redirect()->route('menuSetting.edit', $menuSetting->id)->with([
            'update' => 'success',
        ]);
    }

    /**
     * The one rule that keeps the sidebar working: a link has to be a route name that can
     * actually be turned into a URL. Shared by the create and update paths.
     */
    private function validateMenuLink($value, $fail): void
    {
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
            // sidebar applies, so the form can never accept a link the menu would then drop.
            'link'         => [
                Rule::requiredIf(fn() => (bool) $request->boolean('is_active')),
                'nullable',
                'string',
                fn($attribute, $value, $fail) => $this->validateMenuLink($value, $fail),
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
