<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use App\Models\Category;
use App\Models\CategoryPermission;
use App\Models\Gallery;
use App\Models\GhorfeOnlineList;
use App\Models\Permission;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\ShahinPackage;
use App\Models\Template;
use App\Models\Theme;
use App\Services\Settings\SettingsService;
use App\Services\V1\Auth\AuthService;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Http\Resources\UserCollection;
use App\Models\Employee;
use App\Models\User;

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

        if ($request->urllogo) {
            $inputs['logo'] = Helper::moveImage($request->urllogo, 'document');
        }

        $inputs['contract_start_date'] = Helper::vertaToMiladi($request->contract_start_date);
        $inputs['contract_expiration_date'] = Helper::vertaToMiladi($request->contract_expiration_date);

        $ghorfeOnlineList = GhorfeOnlineList::create($inputs);
        $ghorfeOnlineList->about()->create();

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
            if (request()->getHost() !== $ghorfeOnlineList->domain_active) {
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
    public function getGallery(Request $request)
    {
        $category_id = $request->category_id;
        $service_id = $request->service_id;

        $galleries = Gallery::with('galleryable')
            ->where(function ($q) use ($category_id, $service_id) {

                if ($category_id) {
                    $q->orWhere('parent_id', $category_id);
                }

                if ($service_id) {
                    $q->orWhere('parent_id', $service_id);
                }
            })
            ->get();

        return view('components.gallery-form', [
            'galleries' => $galleries
        ]);
    }

    public function galleryUpdate(Request $request, GhorfeOnlineList $ghorfeOnlineList)
    {
        $categoryId = $request->category;
        $targetClass = null;
        $targetId = null;

        if ($request->service_item) {
            $targetClass = ServiceItem::class;
            $targetId = $request->service_item;
        }

        if (!$targetClass && $request->service) {
            $targetClass = Service::class;
            $targetId = $request->service;
        }

        if (!$targetClass && $categoryId) {
            $targetClass = Category::class;
            $targetId = $categoryId;
        }

        if (!$targetClass || !$targetId) {
            return redirect()
                ->route('ghorfeOnlineList.edit', $ghorfeOnlineList->slug)
                ->withErrors([
                    'target' => 'حداقل باید یک سرویس، زیرسرویس یا دسته انتخاب شود'
                ])
                ->withInput()
                ->with('active_tab', 'gallery_content');
        }

        $manager = new \Intervention\Image\ImageManager(
            new \Intervention\Image\Drivers\Gd\Driver()
        );

        $folder = public_path('uploads/gallery');

        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $processImages = function ($images, $type, $typeValue) use (
            $manager,
            $folder,
            $targetClass,
            $targetId,
            $categoryId,
            $ghorfeOnlineList
        ) {
            foreach ($images as $index => $item) {

                if (empty($item['file'])) continue;

                $image = $manager->read($item['file']);

                if ($type === 'desk') {
                    $image = $index === 4
                        ? $image->cover(900, 375)
                        : $image->cover(300, 176);
                } else {
                    $image = $index === 4
                        ? $image->cover(175, 420)
                        : $image->cover(115, 190);
                }

                $filename = time().'_'.$type.'_'.$index.'_'.uniqid().'.jpg';
                $path = $folder.'/'.$filename;

                $image->toJpeg(80)->save($path);

                // -------------------------
                // KEY برای جلوگیری از تکرار
                // -------------------------
                $key = $type.'_'.$index;

                // -------------------------
                // FIND EXISTING
                // -------------------------
                $existing = Gallery::where('galleryable_id', $targetId)
                    ->where('galleryable_type', $targetClass)
                    ->where('type', $typeValue)
                    ->where('position_index', $key)
                    ->first();

                if ($existing) {

                    // UPDATE
                    $existing->update([
                        'title' => $item['file_title'] ?? '',
                        'image' => 'uploads/gallery/'.$filename,
                    ]);

                    $gallery = $existing;

                } else {

                    // CREATE
                    $gallery = Gallery::create([
                        'galleryable_id' => $targetId,
                        'galleryable_type' => $targetClass,
                        'title' => $item['file_title'] ?? '',
                        'image' => 'uploads/gallery/'.$filename,
                        'type' => $typeValue,
                        'category_id' => $categoryId,
                        'position_index' => $key,
                    ]);
                }

                // -------------------------
                // LINK TABLE
                // -------------------------
                $ghorfeOnlineList->galleries()->updateOrCreate(
                    [
                        'parent_id' => $gallery->id,
                    ],
                    [
                        'type' => $typeValue,
                        'image' => 'uploads/gallery/'.$filename,
                    ]
                );
            }
        };

        // -------------------------
        // PROCESS DESKTOP
        // -------------------------
        $processImages($request->deskImage ?? [], 'desk', 1);

        // -------------------------
        // PROCESS MOBILE
        // -------------------------
        $processImages($request->mobileImage ?? [], 'mobile', 0);

        return redirect()
            ->route('ghorfeOnlineList.edit', $ghorfeOnlineList->slug)
            ->with([
                'update' => 'success',
                'active_tab' => 'gallery_content'
            ]);
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
            'fav_id',
            'logo_id',
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

        // تصاویر (فقط file واقعی، نه url/hidden)
        if ($request->file('logo_file')) {
            $inputs['logo_id'] = Helper::moveImage($request->file('logo_file'), 'document');
        }

        if ($request->file('footer_logo_file')) {
            $inputs['footer_logo_id'] = Helper::moveImage($request->file('footer_logo_file'), 'document');
        }

        if ($request->file('fav_file')) {
            $inputs['fav_id'] = Helper::moveImage($request->file('fav_file'), 'document');
        }

        if ($request->file('mohr_file')) {
            $inputs['mohr_id'] = Helper::moveImage($request->file('mohr_file'), 'document');
        }

        if ($request->file('addvertisment_slider_file')) {
            $inputs['addvertisment_slider_id'] = Helper::moveImage(
                $request->file('addvertisment_slider_file'),
                'document'
            );
        }

        // footer_tag جداگانه (بدون overwrite کل update)
        if ($request->filled('footer_tag')) {
            $codes = collect(explode(',', $request->footer_tag))
                ->map(fn ($item) => Helper::numberConverter($item))
                ->toArray();

            $inputs['footer_tag'] = $codes;
        }

        // update اصلی
        $ghorfeOnlineList->update($inputs);

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
                $data = [
                    'title' => $title,
                    'link' => $request->links[$index],
                    'ghorfe_online_list_id' => $ghorfeOnlineList->id,
                ];
                $ghorfeOnlineList->menus()->updateOrCreate($data);
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
