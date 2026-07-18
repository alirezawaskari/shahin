<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use App\Helpers\Helper;
use App\Http\Resources\UserCollection;
use App\Models\Category;
use App\Models\CategoryPermission;
use App\Models\Employee;
use App\Models\Gallery;
use App\Models\GhorfeOnlineList;
use App\Models\Permission;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\ShahinPackage;
use App\Models\Template;
use App\Models\Theme;
use App\Models\User;
use App\Services\Settings\SettingsService;
use App\Services\V1\Admin\GalleryService;
use Illuminate\Http\Request;

class GhorfeOnlineListController extends AdminController {
    /**
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    */
    protected $settings;

    public function __construct(SettingsService $settings)
    {
        $this->settings = $settings;
    }

    public function getGhofeCategories($id)
    {
        $ghorfeOnlineList = GhorfeOnlineList::with('ghorfeCategories')->where('id', $id)->first();

        return view( 'ghorfeAdmin.ghorfe.index', compact( ['ghorfeOnlineList'] ) );
    }

    public function ghofeCategoryDetail($id)
    {
        $categoryPermission = Permission::with('category')->where('cat_id', $id)->get();
        return view( 'ghorfeAdmin.ghorfe.categories', compact( 'categoryPermission' ) );
    }

    public function index() {
        if ( auth()->user()->hasPermission( 'ghorfe_online_list' ) ) {
            $ghorfeOnlineLists = GhorfeOnlineList::latest()->get();
            return view( 'Admin.ghorfeOnlineList.all', compact( 'ghorfeOnlineLists' ) );
        }
        return abort( 401 );
    }

    /**
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    */

    public function create() {
        if ( auth()->user()->hasPermission( 'ghorfe_online_list_create' ) ) {
            $categories = CategoryPermission::all();
            $packages=ShahinPackage::with('categoryPermission')->get();
            $templates = Template::with('menus')->get();
            $employees = Employee::whereStatus( true )->get();
            return view( 'Admin.ghorfeOnlineList.create', compact( 'templates','employees' , 'categories' ,'packages' ) );
        }

        return abort( 401 );
    }

    /**
    * @param Request $request
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    * @throws \Illuminate\Validation\ValidationException
    */

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('ghorfe_online_list_create')) {
            return abort(401);
        }

        $this->validate($request, [
            'title' => 'required|string',
            'en_title' => 'required|string',
            'user_id' => 'required',
            'type_work' => 'required',
            'call' => 'required',
            'contract_start_date' => 'required',
            'contract_expiration_date' => 'required',
            'address' => 'required|string',
            'domain_active' => 'required',
            'shahin_package_id' => 'required|exists:shahin_packages,id',
            'template_id' => 'required|exists:templates,id',
        ]);

        $inputs = $request->only([
            'title',
            'en_title',
            'user_id',
            'type_work',
            'call',
            'address',
            'domain_active',
            'shahin_package_id',
            'template_id',
            'status'
        ]);

        $inputs['contract_start_date'] = Helper::vertaToMiladi($request->contract_start_date);
        $inputs['contract_expiration_date'] = Helper::vertaToMiladi($request->contract_expiration_date);

        $ghorfeOnlineList = GhorfeOnlineList::create($inputs);
        $ghorfeOnlineList->about()->create();

        // Spatie: attach the logo from its temp media id (logo_path).
        if ($request->logo_path) {
            \App\Helpers\MediaLibraryHelper::attachTo($ghorfeOnlineList, $request->logo_path, 'logo');
        }

        if (auth()->user()->hasPermission('ghorfe_online_list_categories')) {
            $ghorfeOnlineList->ghorfeCategories()->sync($request->categories);
        }

        // sms code
        $codes = collect(explode(',', $request->sms_code))
            ->map(fn($item) => Helper::numberConverter(trim($item)))
            ->toArray();

        $ghorfeOnlineList->update([
            'sms_code' => $codes,
        ]);

        return redirect(route('ghorfeOnlineList.index'))->with([
            'create' => 'success'
        ]);
    }

    /**
    * @param GhorfeOnlineList $ghorfeOnlineList
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    */
    public function edit(GhorfeOnlineList $ghorfeOnlineList)
    {
        $user = auth()->user();

        // ✅ Check permission first
        if (!$user->hasPermission('ghorfe_online_list_site_content') &&
            !$user->hasPermission('ghorfe_online_list_official_info')) {
            return abort(401);
        }

        // ✅ If admin, check domain
        if ($user->level == 'ghorfeAdmin') {
            if (ghorfe() !== $ghorfeOnlineList) {
                return abort(401);
            }
        }

        // ✅ Shared data (no duplication)
        $employees = Employee::whereStatus(true)->get();
        $packages = ShahinPackage::with('categoryPermission')->get();
        $categories = Category::where('depth', 1)->get();
        $themes = Theme::where('type', 'default')->get();
        $templates = Template::with('menus')->get();

        $company_info = [
            'address' => $this->settings->get('address'),
            'phone' => $this->settings->get('phone'),
            'email' => $this->settings->get('email'),
            'shaba_number' => $this->settings->get('shaba_number'),
            'card_number' => $this->settings->get('card_number'),
            'card_holder' => $this->settings->get('card_holder'),
            'tax_free_shaba_number' => $this->settings->get('tax_free_shaba_number'),
            'tax_free_card_number' => $this->settings->get('tax_free_card_number'),
            'tax_free_card_holder' => $this->settings->get('tax_free_card_holder'),
            'sms_panel' => $this->settings->get('sms_panel'),
            'iranSms_linenumber' => $this->settings->get('iranSms_linenumber'),
            'iranSms_apikey' => $this->settings->get('iranSms_apikey'),
        ];

        $ghorfe_domain_active = request()->getHost() === $ghorfeOnlineList->domain_active;

        return view('Admin.ghorfeOnlineList.edit', compact(
            'templates',
            'themes',
            'ghorfe_domain_active',
            'categories',
            'ghorfeOnlineList',
            'packages',
            'employees',
            'company_info'
        ));
    }
    /**
     * Spatie replacement for the legacy getGallery/galleryUpdate pair. Saves the ghorfe's
     * per-ghorfe data-mining slot gallery (gallery_desktop + gallery_mobile) via attachTo.
     * The category/service dropdowns are kept and tag the gallery through the media's
     * parent_id custom property.
     */
    public function gallerySave(Request $request, GhorfeOnlineList $ghorfeOnlineList)
    {
        $parentId = $request->service_item ?: ($request->service ?: $request->category);

        $this->syncGallerySlots($ghorfeOnlineList, 'gallery_desktop', $request->input('deskImage', []), $parentId);
        $this->syncGallerySlots($ghorfeOnlineList, 'gallery_mobile', $request->input('mobileImage', []), $parentId);

        return redirect()
            ->route('ghorfeOnlineList.edit', $ghorfeOnlineList->slug)
            ->with([
                'update' => 'success',
                'active_tab' => 'gallery_content'
            ]);
    }

    /**
     * Attach/replace/rename the slot-based Spatie gallery of the ghorfe. Each slot's
     * metadata (the former galleries-table columns) is preserved as media custom
     * properties, so the legacy galleries table is no longer needed here.
     */
    private function syncGallerySlots(GhorfeOnlineList $ghorfeOnlineList, string $collection, array $items, $parentId = null): void
    {
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $properties = [
                'index'      => (int) $index,
                'title'      => $item['file_title'] ?? null,
                'title_en'   => $item['title_en'] ?? null,
                'title_ar'   => $item['title_ar'] ?? null,
                'title_turk' => $item['title_turk'] ?? null,
                'parent_id'  => $item['parent_id'] ?? $parentId,
            ];

            $existing = $ghorfeOnlineList->gallerySlot($collection, (int) $index);

            if (!empty($item['file'])) {
                // new upload for this slot -> attachTo() replaces the media in this slot
                // (matched by the 'index' custom property) and deletes the old file natively.
                \App\Helpers\MediaLibraryHelper::attachTo($ghorfeOnlineList, $item['file'], $collection, $properties);
            } elseif ($existing) {
                // metadata-only update (titles etc.) for the existing image
                foreach ($properties as $key => $value) {
                    $existing->setCustomProperty($key, $value);
                }
                $existing->save();
            }
        }
    }

    /**
    * @param Request $request
    * @param GhorfeOnlineList $ghorfeOnlineList
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    * @throws \Illuminate\Validation\ValidationException
    */

    public function officialInfoUpdate( Request $request, GhorfeOnlineList $ghorfeOnlineList ) {

        if ( auth()->user()->hasPermission( 'ghorfe_online_list_official_info' ) ) {
            $this->validate( request(), [
                'user_id' => 'required',
                'type_work' => 'required',
                'contract_start_date' => 'required',
                'contract_expiration_date' => 'required',
            ] );
            $inputs = $request->all();

            unset( $inputs[ 'sms_code' ] );
            $inputs[ 'contract_start_date' ] = isset( $inputs[ 'contract_start_date' ] ) ? Helper::vertaToMiladi( $inputs[ 'contract_start_date' ] ) : '';
            $inputs[ 'contract_expiration_date' ] = isset( $inputs[ 'contract_expiration_date' ] ) ? Helper::vertaToMiladi( $inputs[ 'contract_expiration_date' ] ) : '';

            $ghorfeOnlineList->update( $inputs );

            $codes = [];
            $array = explode( ',', $request->input( 'sms_code' ) );
            foreach ( $array as $item ) {
                array_push( $codes, Helper::numberConverter( $item ) );
            }
            $ghorfeOnlineList->update( [
                'sms_code' => $codes,
            ] );

            return redirect()
                ->route('ghorfeOnlineList.edit', $ghorfeOnlineList->slug)
                ->with([
                    'update' => 'success',
                    'active_tab' => 'text_content'
                ]);
        }
        return abort( 401 );
    }

    public function packageUpdate(Request $request, GhorfeOnlineList $ghorfeOnlineList)
    {
        if (!auth()->user()->hasPermission('ghorfe_online_list_package_update')) {
            abort(403);
        }

        $request->validate([
            'package_id' => 'required|exists:shahin_packages,id',
        ]);

        $ghorfeOnlineList->update([
            'shahin_package_id' => $request->package_id,
        ]);

        return redirect()->back()->with('success', 'پکیج با موفقیت ثبت شد');
    }

    /**
    * @param Request $request
    * @param GhorfeOnlineList $ghorfeOnlineList
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    * @throws \Illuminate\Validation\ValidationException
    */
    public function contentUpdate(Request $request, GhorfeOnlineList $ghorfeOnlineList)
    {
        if (!auth()->user()->hasPermission('ghorfe_online_list_site_content')) {
            abort(401);
        }

        $request->validate([
            'postal_code' => 'integer',
            'email' => 'email',
        ]);

        // فقط فیلدهای مجاز (جلوگیری از ورود logo و داده‌های ناخواسته)
        $inputs = $request->only([
            'theme_id',
            'user_id',
            'template_id',
            'title',
            'en_title',
            'title_ar',
            'title_turk',
            'slug',
            'type_work',
            'call',
            'address',
            'address_en',
            'address_ar',
            'address_turk',
            'lat',
            'lon',
            'postal_code',
            'email',
            'description',
            'keywords',
            'status',
            'domain_active'
        ]);

        // footer_tag جداگانه (بدون overwrite کل update)
        if ($request->filled('footer_tag')) {
            $codes = collect(explode(',', $request->footer_tag))
                ->map(fn ($item) => Helper::numberConverter($item))
                ->toArray();

            $inputs['footer_tag'] = $codes;
        }

        // update اصلی
        $ghorfeOnlineList->update($inputs);

        // Spatie: attach any newly uploaded settings image from its temp media id (*_path).
        // Replaces the old Helper::moveImage writes into the char(36) *_id columns.
        foreach (['logo', 'footer_logo', 'fav', 'mohr', 'addvertisment_slider'] as $collection) {
            $pathKey = $collection . '_path';
            if ($request->$pathKey) {
                \App\Helpers\MediaLibraryHelper::attachTo($ghorfeOnlineList, $request->$pathKey, $collection);
            }
        }

        return redirect()
            ->route('ghorfeOnlineList.edit', $ghorfeOnlineList->slug)
            ->with([
                'update' => 'success',
                'active_tab' => 'menu_content'
            ]);
    }
    public function menuUpdate(Request $request, GhorfeOnlineList $ghorfeOnlineList)
    {
        if ($request->template_id)
        {
            $ghorfeOnlineList->template_id = $request->template_id;
            $ghorfeOnlineList->update();
            return redirect()
                ->route('ghorfeOnlineList.edit', $ghorfeOnlineList->slug)
                ->with([
                    'update' => 'success',
                    'active_tab' => 'menu_content'
                ]);
        }else {
            $this->validate($request, [
                'titles.*' => 'required',
                'links.*' => 'required',
            ]);

            $ids = $request->ids ?? [];

            foreach ($request->titles as $index => $title) {

                $menu = $ghorfeOnlineList->menus()
                    ->where('id', $request->ids[$index])
                    ->first();

                if (!$menu) continue;

                $menu->title = $title;
                $menu->link = $request->links[$index];

                $menu->is_active = isset($request->actives[$index]) ? 1 : 0;

                $menu->save();
            }

            return redirect()
                ->route('ghorfeOnlineList.edit', $ghorfeOnlineList->slug)
                ->with([
                    'update' => 'success',
                    'active_tab' => 'theme_content'
                ]);
        }
    }
    public function themeUpdate(Request $request, GhorfeOnlineList $ghorfeOnlineList)
    {
        if (!auth()->user()->hasPermission('ghorfe_online_list_site_content')) {
            return abort(401);
        }

        if ($request->themeId)
        {
            $ghorfeOnlineList->update([
                'theme_id' => $request->themeId
            ]);

            return response()->json(['update' => 'success']);
        }else
        {
            $request->validate([
                'name'            => ['required', 'string', 'max:255'],
                'color_primary'   => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
                'color_secondary' => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            ]);

            $newTheme = Theme::create([
                'name'            => $request->name,
                'color_primary'   => $request->color_primary,
                'color_secondary' => $request->color_secondary,
                'type'            => 'custom',
            ]);

            $ghorfeOnlineList->update([
                'theme_id' => $newTheme->id
            ]);

            return redirect()
                ->route('ghorfeOnlineList.edit', $ghorfeOnlineList->slug)
                ->with([
                    'update' => 'success',
                    'active_tab' => 'category_content'
                ]);
        }
    }

    public function categoriesGhorfe(Request $request, GhorfeOnlineList $ghorfeOnlineList)
    {
        if (!auth()->user()->hasPermission('ghorfe_online_list_categories')) {
            return abort(401);
        }

        // Validate
        $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'exists:category_permissions,id',
        ]);

        // Sync categories
        $ghorfeOnlineList->ghorfeCategories()->sync($request->categories);

        return redirect()->back()->with([
            'update' => 'success'
        ]);

    }

    /**
    * @param GhorfeOnlineList $ghorfeOnlineList
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    */

    public function destroy( GhorfeOnlineList $ghorfeOnlineList ) {
        $ghorfeOnlineList->delete();
        return redirect( route( 'ghorfeOnlineList.index' ) )->with( [
            'delete' => 'success'
        ] );
    }

     /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function showUsersList()
    {
        if ( auth()->user()->hasPermission( 'ghorfe_online_list_users' ) )
        {
            $title = 'فهرست کاربران مغازه دیجیتال';
            $url = 'ghorfeOnlineList/users/filter';
            $user = auth()->user();

            $create = false;
            $delete = false;
            $organ = false;
            $can_manual_login = false;

            if (!$user->isAbleTo('user-detail')){
                abort(403);
            }
            if ($user->isAbleTo('user-create')){
                $create = true;
            }
            if ($user->isAbleTo('user-delete')){
                $delete = true;
            }
            if ($user->isAbleTo('manual-login-user')){
                $can_manual_login = true;
            }

            if ($user->admin_organization_id){
                $organ = true;
            }

            return view('Admin.user.all' , compact('title', 'url' ,'delete' , 'create', 'organ','can_manual_login'));
        }
        return abort( 401 );
    }
    /**
     * @param Request $request
     * @return UserCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterUsers(Request $request)
    {
        $request['has_ghorfe_online'] = true;
        $users = User::filter($request)->latest();

        if ($request->exel == 'on') {
            return (new UsersExport($users))->download('users.xlsx');
        }

        return new UserCollection($users->paginate(20));
    }
}
