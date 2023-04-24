<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractHistory;
use Illuminate\Http\Request;

class ContractController extends Controller
 {
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */

    public function index( Request $request )
 {
        $contracts = Contract::orderBy( 'id', 'DESC' );
        $queries = [];
        $columns = [ 'status', 'type' ];
        foreach ( $columns as $column ) {
            if ( $request->has( $column ) ) {
                if ( $request->$column != 'all' ) {
                    $contracts = $contracts->where( $column, $request->$column );
                    $queries[ $column ] = $request->$column;
                }
                if ( $request->$column == 'all' ) {
                    $queries[ $column ] = 'all';
                }
            }
        }
        $contracts = $contracts->paginate( 15 )->appends( $queries );

        $statuses = [
            '1' => 'منتظر تایید وکیل', '2' => 'منتظر تایید مدیر مالی', '3' => 'منتظر تایید مدیر عامل'
        ];
        $type = [ '1' => 'خرید', '2' => 'فروش', '3' => 'واگذاری' ];

        return view( 'Admin.contract.lists', compact( 'statuses', 'contracts', 'type', 'queries' ) );
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */

    public function create()
 {
        return view( 'Admin.contract.create' );
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function store( Request $request )
    {
        $request->validate( [ 'title' => 'required', 'type'=>'required', 'file_path'=> 'required' ], [
            'title.required' => 'لطفا عنوان را وارد کنید',
            'type.required' => 'لطفا نوع را انتخاب کنید',
            'file_path.required' => 'لطفا فایل را وارد کنید'
        ] );

        $contract = Contract::create( $request->all() );

        if ( isset( $request->file_path ) ) {
            $contract->file = Helper::moveImage( $request->file_path, 'contract' );
            $contract->save();
        }

        ContractHistory::create( [ 'contract_id'=>$contract->id, 'user_id'=>\Auth::user()->id, 'file'=>$contract->file ] );

        return redirect( 'dashboard/contract' )->with( [ 'create' => 'success' ] );
    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function show( $id )
    {
        $contract_histories = Contract::find( $id )->contractHistories()->paginate( 15 );
        return view( 'Admin.contract.show', compact( 'contract_histories' ) );
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function edit( $id )
    {
        if ( $id ) {
            $contract = Contract::find( $id );
            return view( 'Admin.contract.edit', [ 'contract' => $contract ] );
        }
        return redirect()->back();
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function update( Request $request, $id )
    {
        $request->validate( [ 'title' => 'required', 'type'=>'required' ], [
            'title.required' => 'لطفا عنوان را وارد کنید',
            'type.required' => 'لطفا نوع را انتخاب کنید',
        ] );
        $contract = Contract::find( $id );
        $contract->update( $request->all() );

        if ( isset( $request->file_path ) ) {
            $contract->file = Helper::moveImage( $request->file_path, 'contract' );
            $contract->save();

            ContractHistory::create( [ 'contract_id'=>$contract->id, 'user_id'=>\Auth::user()->id, 'file'=>$contract->file ] );
        }

        return redirect( '/dashboard/contract' )->with( [ 'update' => 'success' ] );
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function destroy( $id )
    {
        if ( $id ) {
            $contract = Contract::find( $id );
            $contract->delete();
        }
        return redirect()->back();
    }
}
