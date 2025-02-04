<?php

namespace App\Http\Controllers\Admin;

use App\Models\GhorfeOnlineList;
use App\Models\Service;
use App\Models\Province;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Employee;

class GhorfeOnlineListController extends AdminController {
    /**
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    */

    public function index() {
        if ( auth()->user()->hasPermission( 'ghorfe_online_list' ) ) {
            $ghorfeOnlineLists = GhorfeOnlineList::latest()->get();
            return view( 'Admin.ghorfeOnlineList.all', compact( 'ghorfeOnlineLists' ) );
        }
        $ghorfeOnlineLists = GhorfeOnlineList::latest()->get();
        return view( 'Admin.ghorfeOnlineList.all', compact( 'ghorfeOnlineLists' ) );
        return abort( 401 );
    }

    /**
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    */

    public function create() {
        if ( auth()->user()->hasPermission( 'ghorfe_online_list_create' ) ) {
            $employees = Employee::whereStatus( true )->get();
            return view( 'Admin.ghorfeOnlineList.create', compact( 'employees' ) );
        }

        return abort( 401 );
    }

    /**
    * @param Request $request
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    * @throws \Illuminate\Validation\ValidationException
    */

    public function store( Request $request ) {
        if ( auth()->user()->hasPermission( 'ghorfe_online_list_create' ) ) {
            $this->validate( request(), [
                'title' => 'required|string',
                'en_title' => 'required|string',
                'user_id' => 'required',
                'type_work' => 'required',
                'call' => 'required',
                'contract_start_date' => 'required',
                'contract_expiration_date' => 'required',
                'address' => 'required|string',
                'domain_active' => 'required'
            ] );
            $inputs = $request->all();

            if ( $request->urllogo ) {
                $inputs[ 'logo' ] = Helper::moveImage( $request->urllogo, 'document' );
            }

            unset( $inputs[ 'sms_code' ] );
            unset( $inputs[ 'urllogo' ] );
            $inputs[ 'contract_start_date' ] = isset( $inputs[ 'contract_start_date' ] ) ? Helper::vertaToMiladi( $inputs[ 'contract_start_date' ] ) : '';
            $inputs[ 'contract_expiration_date' ] = isset( $inputs[ 'contract_expiration_date' ] ) ? Helper::vertaToMiladi( $inputs[ 'contract_expiration_date' ] ) : '';

            $ghorfeOnlineList = GhorfeOnlineList::create( $inputs );
            $codes = [];
            $array = explode( ',', $request->input( 'sms_code' ) );
            foreach ( $array as $item ) {
                array_push( $codes, Helper::numberConverter( $item ) );
            }
            $ghorfeOnlineList->update( [
                'sms_code' => $codes,
            ] );

            return redirect( route( 'ghorfeOnlineList.index' ) )->with( [
                'create' => 'success'
            ] );
        }

        return abort( 401 );
    }

    /**
    * @param GhorfeOnlineList $ghorfeOnlineList
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    */

    public function edit( GhorfeOnlineList $ghorfeOnlineList ) {
        if ( auth()->user()->hasPermission( 'ghorfe_online_list_site_content') || auth()->user()->hasPermission( 'ghorfe_online_list_official_info')) {
            $employees = Employee::whereStatus( true )->get();
            return view( 'Admin.ghorfeOnlineList.edit', compact( 'ghorfeOnlineList', 'employees' ) );
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
}
