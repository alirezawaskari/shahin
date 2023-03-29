<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ContractForm;

class ContractFormController extends Controller {
    public function index() {
        if ( auth()->user()->isAbleTo( 'contract-form' ) ) {
            $contract_forms = ContractForm::all();
            return view( 'Admin.contractform.all', compact( [ 'contract_forms' ] ) );
        }
        return abort( 403 );
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View|void
     */
    public function create() {
        if ( auth()->user()->isAbleTo( 'contract-form-create' ) ) {
            return view( 'Admin.contractform.create' );
        }
        return abort( 401 );
    }
    /**
    * Store a new contract form field.
    *
    * @param  Request  $request
    * @return Response
    */
    public function store( Request $request ) {
        ContractForm::create( $request->all() );
        return redirect( route( 'contractForm.index' ) )->with( [
            'update'=>'success'
        ] );
    }

    /**
     * @param ContractForm $contractForm
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View|void
     */
    public function edit(ContractForm $contractForm)
    {
        if(auth()->user()->isAbleTo('contract-form-edit'))
        {
            return view('Admin.contractform.edit' , compact('contractForm'));
        }
        return abort(401);
    }

    /**
     * @param Request $request
     * @param ContractForm $contractForm
     * @return \Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request , ContractForm $contractForm)
    {
        if(auth()->user()->isAbleTo('contract-form-edit'))
        {
            $contractForm->update($request->all());
            return redirect( route( 'contractForm.index' ) )->with( [
                'update'=>'success'
            ] );
        }
        return redirect( route( 'contractForm.index' ) )->with( [
            'failByMessage'=>'شما مجاز به این کار نیستید.'
        ] );
    }

    /**
     * @param ContractForm $contractForm
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function formItems(ContractForm $contractForm)
    {
        $items = $contractForm->beforeformitems()->get();
        return view('Admin.contractform.formitem' , compact('items' , 'contractForm'));
    }

    /**
     * @param ContractForm $contractForm
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function formItemCreate(ContractForm $contractForm)
    {
        $beforeForm=$contractForm;
        return view('Admin.beforeformitem.create' , compact('beforeForm'));
    }
}
