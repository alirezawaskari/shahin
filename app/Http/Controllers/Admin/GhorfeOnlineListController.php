<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use App\Models\CategoryPermission;
use App\Models\GhorfeOnlineList;
use App\Models\Service;
use App\Models\Province;
use App\Models\Theme;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Http\Resources\UserCollection;
use App\Models\Employee;
use App\Models\User;

class GhorfeOnlineListController extends AdminController {
    /**
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    */

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

            $employees = Employee::whereStatus( true )->get();
            return view( 'Admin.ghorfeOnlineList.create', compact( 'employees' , 'categories' ) );
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
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $inputs = $request->only([
            'title',
            'en_title',
            'user_id',
            'type_work',
            'call',
            'address',
            'domain_active',
            'status'
        ]);

        if ($request->urllogo) {
            $inputs['logo'] = Helper::moveImage($request->urllogo, 'document');
        }

        $inputs['contract_start_date'] = Helper::vertaToMiladi($request->contract_start_date);
        $inputs['contract_expiration_date'] = Helper::vertaToMiladi($request->contract_expiration_date);

        $ghorfeOnlineList = GhorfeOnlineList::create($inputs);

        $ghorfeOnlineList->about()->create();

        // دسته‌بندی‌ها
        $ghorfeOnlineList->ghorfeCategories()->sync($request->categories);

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

    public function edit( GhorfeOnlineList $ghorfeOnlineList ) {
        if ( auth()->user()->hasPermission( 'ghorfe_online_list_site_content') || auth()->user()->hasPermission( 'ghorfe_online_list_official_info')) {
            $employees = Employee::whereStatus( true )->get();
            $categories = CategoryPermission::all();
            return view( 'Admin.ghorfeOnlineList.edit', compact( 'ghorfeOnlineList','categories', 'employees' ) );
        }
        return abort( 401 );
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

            return redirect()->back()->with( [
                'update' => 'success'
            ] );
        }
        return abort( 401 );
    }

    /**
    * @param Request $request
    * @param GhorfeOnlineList $ghorfeOnlineList
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    * @throws \Illuminate\Validation\ValidationException
    */

    public function contentUpdate( Request $request, GhorfeOnlineList $ghorfeOnlineList ) {

        if ( auth()->user()->hasPermission( 'ghorfe_online_list_site_content' ) ) {
            $this->validate( request(), [
                'postal_code' => 'integer',
                'email' => 'email',
            ] );
            $inputs = $request->all();
            if ( $request->urllarge_vertical_image ) {
                $inputs[ 'large_vertical_image' ] = Helper::moveImage( $request->urllarge_vertical_image, 'document' );
            }
            if ( $request->urllarge_horizontal_image ) {
                $inputs[ 'large_horizontal_image' ] = Helper::moveImage( $request->urllarge_horizontal_image, 'document' );
            }
            if ( $request->urlleft_small_horizontal_image ) {
                $inputs[ 'left_small_horizontal_image' ] = Helper::moveImage( $request->urlleft_small_horizontal_image, 'document' );
            }
            if ( $request->urlright_small_horizontal_image ) {
                $inputs[ 'right_small_horizontal_image' ] = Helper::moveImage( $request->urlright_small_horizontal_image, 'document' );
            }
            if ( $request->urllogo ) {
                $inputs[ 'logo' ] = Helper::moveImage( $request->urllogo, 'document' );
            }
            if ( $request->urlfooter_logo ) {
                $inputs[ 'footer_logo' ] = Helper::moveImage( $request->urlfooter_logo, 'document' );
            }
            if ( $request->urlfav ) {
                $inputs[ 'fav' ] = Helper::moveImage( $request->urlfav, 'document' );
            }
            if ( $request->urlmohr ) {
                $inputs[ 'mohr' ] = Helper::moveImage( $request->urlmohr, 'document' );
            }
            if ( $request->urladdvertisment_slider ) {
                $inputs[ 'addvertisment_slider' ] = Helper::moveImage( $request->urladdvertisment_slider, 'document' );
            }
            unset( $inputs[ 'footer_tag' ] );
            unset( $inputs[ 'urllarge_vertical_image' ] );
            unset( $inputs[ 'urllarge_horizontal_image' ] );
            unset( $inputs[ 'urlleft_small_horizontal_image' ] );
            unset( $inputs[ 'urlright_small_horizontal_image' ] );
            unset( $inputs[ 'urllogo' ] );
            unset( $inputs[ 'urlfooter_logo' ] );
            unset( $inputs[ 'urlfav' ] );
            unset( $inputs[ 'urlmohr' ] );
            unset( $inputs[ 'urladdvertisment_slider' ] );

            $ghorfeOnlineList->update( $inputs );

            $codes = [];
            $array = explode( ',', $request->input( 'footer_tag' ) );
            foreach ( $array as $item ) {
                array_push( $codes, Helper::numberConverter( $item ) );
            }
            $ghorfeOnlineList->update( [
                'footer_tag' => $codes,
            ] );

            return redirect()->back()->with( [
                'update' => 'success'
            ] );
        }
        return abort( 401 );
    }

    public function menuUpdate(Request $request, GhorfeOnlineList $ghorfeOnlineList)
    {
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

            if (!empty($ids[$index])) {
                Menu::where('id', $ids[$index])
                    ->where('ghorfe_online_list_id', $ghorfeOnlineList->id)
                    ->update($data);
            } else {
                Menu::create($data);
            }
        }

        $existingIds = $ghorfeOnlineList->menus->pluck('id')->toArray();
        $submittedIds = array_filter($request->ids);

        $deletedIds = array_diff($existingIds, $submittedIds);

        Menu::whereIn('id', $deletedIds)
            ->where('ghorfe_online_list_id', $ghorfeOnlineList->id)
            ->delete();
    }
    public function themeUpdate(Request $request, GhorfeOnlineList $ghorfeOnlineList)
    {
        if (!auth()->user()->hasPermission('ghorfe_online_list_site_content')) {
            return abort(401);
        }

        $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'color_primary'   => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'color_secondary' => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        $theme = $ghorfeOnlineList->theme;

        if (!$theme || $theme->type === 'default') {
            $newTheme = Theme::create([
                'name'            => $request->name,
                'color_primary'   => $request->color_primary,
                'color_secondary' => $request->color_secondary,
                'type'            => 'custom',
            ]);

            $ghorfeOnlineList->update([
                'theme_id' => $newTheme->id
            ]);

            return redirect()->back()->with(['create' => 'success']);
        }

        $theme->update([
            'name'            => $request->name,
            'color_primary'   => $request->color_primary,
            'color_secondary' => $request->color_secondary,
        ]);

        return redirect()->back()->with(['update' => 'success']);
    }

    public function categoriesGhorfe(Request $request, GhorfeOnlineList $ghorfeOnlineList)
    {
        if (!auth()->user()->hasPermission('ghorfe_online_list_site_content')) {
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
