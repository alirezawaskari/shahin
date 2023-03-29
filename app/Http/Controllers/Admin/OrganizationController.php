<?php

namespace App\Http\Controllers\Admin;

use App\Models\Organization;
use App\Models\Representation;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use App\Events\fastregisterorganCallcenter;
use App\Http\Resources\OrganizationCollection;
use Illuminate\Validation\Rule;

class OrganizationController extends AdminController {
    public function index() {
        $organizations = Organization::latest()->get();
        $delete = auth()->user()->isAbleTo( 'organization-delete' );
        return view( 'Admin.organization.all', compact( 'organizations' ,'delete') );
    }

    public function filter() {
        $organizations = Organization::filter()->whereNull('parent_id')->orderBy( 'updated_at', 'desc' );

        return new OrganizationCollection( $organizations->paginate( 20 ) );
    }

    public function create(Request $request) {
        $services = Service::where('status' , true)->get();
        $parent= Organization::find($request->parent_id);
        return view( 'Admin.organization.create', compact('parent', 'services' ) );
    }

    public function store( Request $request ) {
        $this->validate( request(), [
            'fa_name' => 'required|string',
            'en_name' => 'required|string',
            'parent_id' => array(Rule::exists('organizations','id')),
            'header_description' => 'nullable|string',
            'status' => 'required|string',
            'service_id' => 'required',
        ] );
        $inputs = $request->all();

        if ( $request->urlstamp ) {
            $inputs[ 'stamp' ] = Helper::moveImage($request->urlstamp,'document');
        }
        if ( $request->urlimage ) {
            $inputs[ 'image' ] = Helper::moveImage($request->urlimage,'document');
        }

        $files = $request->urldocument;
        $document = [];
        if ( isset( $files ) ) {
            foreach ( $files as $file ) {
                if ( $file ) {
                    if ( isset( $document ) ) {
                        $url = Helper::moveImage($file,'document');
                        $document[] = $url;
                    }
                }
            }
            $inputs[ 'document' ] = $document;
        }

        $inputs[ 'credit' ] = ( float ) str_replace( ',', '', $request->credit );

        unset( $inputs[ 'service_id' ] );
        unset( $inputs[ 'sms_code' ] );
        unset( $inputs[ 'urlstamp' ] );
        unset( $inputs[ 'urlimage' ] );
        unset( $inputs[ 'urldocument' ] );

        $organization = Organization::create( $inputs );

        $organization->services()->sync( $request->input( 'service_id' ) );

        $codes = [];
        $array = explode( ',', $request->input( 'sms_code' ) );
        foreach ( $array as $item ) {
            array_push( $codes, Helper::numberConverter( $item ) );
        }
        $organization->update( [
            'sms_code' => $codes,
        ] );
        alert()->success('با موفقیت ثبت گردید ', 'عملیات موفق .')->persistent('باشه');
        return redirect( route( 'organization.index' ) )->with( [
            'create' => 'success'
        ] );
    }

    public function edit( Organization $organization ) {
//        $services = Service::status()->get();
        $services = Service::all();
        return view( 'Admin.organization.edit', compact( 'organization', 'services' ) );
    }

    public function update( Request $request, Organization $organization ) {
        $this->validate( request(), [
            'fa_name' => 'required|string',
            'en_name' => 'required|string',
            'header_description' => 'nullable|string',
            'status' => 'required|string',
            'service_id' => 'required',
        ] );
        $inputs = $request->all();
        if ( $request->urlstamp ) {
            Helper::deleteImage($organization->stamp);
            $inputs[ 'stamp' ] = Helper::moveImage($request->urlstamp,'document');
        }
        if ( $request->urlimage ) {
            Helper::deleteImage($organization->image);
            $inputs[ 'image' ] = Helper::moveImage($request->urlimage,'document');
        }

        $files = $request->urldocument;
        $document = $organization->document ? $organization->document : [];
        if ( isset( $files ) ) {
            foreach ( $files as $file ) {
                if ( $file ) {
                    if ( isset( $document ) ) {
                        $url = Helper::moveImage($file,'document');
                        $document[] = $url;
                    }
                }
            }
            $inputs[ 'document' ] = $document;
        }

        unset( $inputs[ 'service_id' ] );
        unset( $inputs[ 'sms_code' ] );
        unset( $inputs[ 'urlstamp' ] );
        unset( $inputs[ 'urlimage' ] );
        unset( $inputs[ 'urldocument' ] );

        $inputs[ 'credit' ] = ( float ) str_replace( ',', '', $request->credit );

        $organization->update( $inputs );
        $organization->services()->sync( $request->input( 'service_id' ) );

        $files = $request->file( 'document' );
        $document = $organization->document ? $organization->document : [];

        $codes = [];
        $array = explode( ',', $request->input( 'sms_code' ) );
        foreach ( $array as $item ) {
            array_push( $codes, Helper::numberConverter( $item ) );
        }
        $organization->update( [
            'sms_code' => $codes,
        ] );

        return redirect( route( 'organization.index' ) )->with( [
            'update' => 'success'
        ] );
    }

    /**
     * @param Organization $organization
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Organization $organization ) {

        if (!auth()->user()->hasPermission('organization-delete')) {
            abort(403);
        }
        $organization->delete();
        if (\request()->ajax()){
            return response(['status'=>'success']);
        }
        alert('سازمان با موفقیت حذف گردید .' , 'عملیات موفق')->persistent('باشه');
        return redirect()->back();
    }

    public function createUser() {
        $representations = Representation::whereStatus( true )->get();
        $organizations = Organization::whereStatus( true )->get();
        return view( 'Admin.organization.createUser', compact( 'representations', 'organizations' ) );
    }

    public function storeUser( Request $request ) {
        $user = User::where( 'phone', $request->phone )->first();
        $inputs = $request->all();

        if ( !$user ) {
            $this->validate( request(), [
                'name' => 'required|string',
                'family' => 'required|string',
                'phone' => 'required|regex:/^09[0-9]{9}$/|unique:users',
            ] );

            $pass = rand( 12345, 99999 );

            $pass = Hash::make( $pass );
            $array = [
                'password' => $pass,
            ];
        } else {
            $inputs[ 'level' ] = ( $user->level == 'admin' ? 'admin' : 'organ' );
            $array = [
                'password' => $user->password,
            ];
        }

        $inputs[ 'wallet' ] = ( float ) str_replace( ',', '', $request->wallet );
        $inputs[ 'credit' ] = ( float ) str_replace( ',', '', $request->credit );

        $organization = Organization::find( $request->organization_id );

        if ( $organization->credit >= $inputs[ 'credit' ] ) {
            $user = User::where( 'phone', $inputs[ 'phone' ] )->first();
            $credit = $user ? $inputs[ 'credit' ] - $user->credit : $inputs[ 'credit' ];
            $organization->update( [
                'credit' => $organization->credit - $credit,
            ] );
        } else {
            return redirect()->back()->with( [
                'failByMessage' => 'اعتبار سازمان کافی نمی باشد.'
            ] );
        }

        $phone = $inputs[ 'phone' ];
        unset( $inputs[ '_token' ] );
        unset( $inputs[ 'phone' ] );
        $user = User::updateOrCreate(
            [
                'phone' => $phone
            ],
            array_merge( $inputs, $array )
        );
        //event( new fastregisterorganCallcenter( $user, $inputs[ 'password' ] ) );
        return redirect( route( 'user.index' ) )->with( [
            'create' => 'success'
        ] );
    }

    /**
     * @param Request $request
     * @return void
     */
    public function items(Request $request)
    {
        $parent= Organization::find($request->id);
        $children= $parent->children;
        return view('Admin.organization.items',compact('parent','children'));
    }

}
