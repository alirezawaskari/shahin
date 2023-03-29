<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AgencyType;
use App\Models\ArrangeContract;
use App\Models\Center;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\Request;

class ArrangeContractController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function arrangeContractList($id)
    {
        $arrange_contracts=[];
        if (\Auth::user()->isAbleTo('arrange-contract-detail')) {
            $arrange_contracts = Contract::find($id)->arrangeContracts()->orderBy('id','DESC')->paginate(15);
        }
        if (\Auth::user()->isAbleTo('arrange-contract')) {
            $arrange_contracts = Contract::find($id)->arrangeContracts()->where('user_id',\Auth::user()->id)
                ->orderBy('id','DESC')->paginate(15);
        }

        $contract_id = $id ;
        return view('Admin.arrangeContract.all', compact('arrange_contracts','contract_id'));
    }

    public function arrangeContractListDesktop($id)
    {
        $arrange_contracts=[];
        if (\Auth::user()->isAbleTo('arrange-contract-detail')) {
            $has_role = User::find(\Auth::user()->id)->whereHas('roles', function ($query)  {
                $query->where('roles.name','superadmin');
            })->count();
            if($has_role == 1){
                $arrange_contracts = Contract::find($id)->arrangeContracts()->where('status',2)
                    ->orderBy('id','DESC')->paginate(15);
            }
        }
        $contract_id = $id ;
        return view('Admin.arrangeContract.all', compact('arrange_contracts','contract_id'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'no' => 'required', 'file_path' => 'required',
        ], [
                'no.required' => 'لطفا شماره را وارد کنید', 'file_path.required' => 'لطفا فایل را وارد کنید',
            ]
        );

        if($request->contract_type_selected == 0){
            //is agency
            $rec=AgencyType::find($request->agency_type_id);
        }

        if($request->contract_type_selected == 1){
            //is center
            $rec=Center::find($request->center_id);
        }

        if (isset($request->file_path)) {
            $file = Helper::moveImage($request->file_path, 'arrangeContract');
        }

        ArrangeContract::create(['no'=>$request->no,'contract_id'=>$request->contract_id, 'file'=>$file,'user_id'=>\Auth::user()->id,
        'owner_id'=>$rec->user_id]);

        return redirect('/dashboard/arrangeContractList/'.$request->contract_id)->with([
            'create' => 'success'
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $agency_types = AgencyType::all();
        $centers = Center::all();
        $contract_id = $id ;
        return view('Admin.arrangeContract.create',compact('agency_types','centers','contract_id'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $agency_types=[];$centers=[];
        if ($id) {
            $arrange_contract = ArrangeContract::find($id);
            $user=User::find($arrange_contract->owner_id);
            if(isset($user) && $user->admin_representation_id != null){  $agency_types = AgencyType::all(); }
            if(isset($user) && $user->center_id != null){ $centers = Center::all();  }
            return view('Admin.arrangeContract.edit', compact('arrange_contract','agency_types','centers'));
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
    public function update(Request $request, $id)
    {
        $request->validate([
            'no' => 'required'], ['no.required' => 'لطفا شماره را وارد کنید']
        );

        if(isset($request->agency_type_id)){ $rec=AgencyType::find($request->agency_type_id); }
        if(isset($request->center_id) ){ $rec=Center::find($request->center_id); }

        $arrange_contract = ArrangeContract::find($id);

        if($rec){  $arrange_contract->update(['no'=>$request->no, 'owner_id'=>$rec->user_id]);

            if (isset($request->file_path)) {
                $file = Helper::moveImage($request->file_path, 'arrangeContract');
                $arrange_contract->file = $file;
                $arrange_contract->save();
            }

        }

        return redirect('/dashboard/arrangeContractList/'.$request->contract_id)->with(['update' => 'success' ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function status($id,$status)
    {
        $arrange_contract=ArrangeContract::find($id);
        if($arrange_contract) {
            $arrange_contract->status = $status;
            $arrange_contract->save();
        }
        return redirect()->back();

    }
}
