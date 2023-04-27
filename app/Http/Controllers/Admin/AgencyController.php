<?php

namespace App\Http\Controllers\Admin;

use App\Models\Agency;
use App\Models\User;
use App\Models\AgencyType;
use App\Exports\AgencyExport;
use App\Models\Province;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Http\Resources\AgencyCollection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Verta;

class AgencyController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
		Agency::where('expire_date' , '<=' , Carbon::now()->toDateString())
        ->where('status' , '3')
        ->update([
            'status' => 5,
        ]);

		$provinces = Province::all();
		$agency_types = AgencyType::all();

        foreach ($provinces as $province)
        {
            $count = Agency::where('province', 'LIKE' , '%' . $province->name . '%')->count();
            $province->code = $count;
        }

        return view('Admin.agency.all' , compact('provinces','agency_types'));
    }

    /**
     * @return AgencyCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter()
    {
        $agencies = Agency::filter()->orderBy('updated_at', 'desc');

		if (\request('exel') != null){
            return (new AgencyExport($agencies))->download('agencies.xlsx');
        }

        return new AgencyCollection($agencies->paginate(20));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        $agency_types = AgencyType::all();
        return view('Admin.agency.create' , compact('agency_types'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request , [
            'name' => 'required',
            'phone' => 'required|numeric',
            'province' => 'required',
            'description' => 'required',
            'city' => 'required',
        ]);

        $pass = rand(12345, 99999);
        if(!User::where('phone' , $request->phone)->first())
		{
		    $user = User::create([
                'name' => $request->name,
                'family' => $request->family,
                'phone' => $request->phone,
                'password' => Hash::make($pass),
                'active' => true,
                'level' => 'user',
                'callcenter_id' => auth()->user()->id,
                'representation_id' => auth()->user()->admin_representation ? auth()->user()->admin_representation->id : null,
            ]);
		}

        $inputs = $request->all();
        unset($inputs['urlimgmeli']);
        unset($inputs['urlimgturnover']);


        if($request->urlimgmeli)
        {

            $inputs['imgmeli'] = Helper::moveImage($request->urlimgmeli,'document');
        }
		if($request->urlimgturnover)
        {
            $inputs['imgturnover'] = Helper::moveImage($request->urlimgturnover,'document');
        }

        $agency = Agency::create($inputs);

        $agency->histories()->create([
            'user_id' => auth()->user()->id,
            'status' => 2,
            'description' => $request->description ? $request->description : 'درخواست عاملیت ثبت شده است',
        ]);

        return redirect(route('agency.index'))->with([
            'agency_create' => 'success'
        ]);
    }

    /**
     * @param Agency $agency
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(Agency $agency)
    {
        $user = User::where('phone' , $agency->phone)->first();
        $comments = Helper::userHistories($user);
        $agency_types = AgencyType::all();

        return view('Admin.agency.edit' , compact('agency' , 'comments' , 'agency_types'));
    }

    /**
     * @param Request $request
     * @param Agency $agency
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, Agency $agency)
    {
        if($request->description)
        {
            $agency->histories()->create([
                'user_id' => auth()->user()->id,
                'status' => 2,
                'description' => $request->description,
            ]);
        }


        $inputs = $request->all();

		unset($inputs['urlimgmeli']);
        unset($inputs['urlimgturnover']);
        unset($inputs['description']);


		$inputs['status'] = $request->description && $request->status == 0 ? 1 : $request->status;
        $inputs['expire_date'] = Helper::vertaToMiladi($request->expire_date);

        if($request->urlimgmeli)
        {
            Helper::deleteImage($agency->urlimgmeli);
            $inputs['imgmeli'] = Helper::moveImage($request->urlimgmeli,'document');
        }
		if($request->urlimgturnover)
        {
            Helper::deleteImage($agency->urlimgturnover);
            $inputs['imgturnover'] = Helper::moveImage($request->urlimgturnover,'document');
        }

        $agency->update($inputs);

        return redirect(route('agency.index'))->with([
            'agency_update' => 'success'
        ]);
    }

    /**
     * @param Agency $agency
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Agency $agency)
    {
        $agency->delete();
        return redirect(route('agency.index'))->with([
            'agency_delete' => 'success'
        ]);
    }

    /**
     * @param Request $request
     */
    public function offer(Request $request)
    {
        if ($request->ajax()) {
            $type = $request->input('type');
            $id = $request->input('id');
            if ($type == 1) {
                Agency::where('id', $id)->update(array('status' => '0'));
            }
        }
    }

    /**
     * @param Request $request
     */
    public function onner(Request $request)
    {
        if ($request->ajax()) {
            $type = $request->input('type');
            $id = $request->input('id');
            if ($type == 1) {
                Agency::where('id', $id)->update(array('status' => '1'));
            }
        }
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function suspend()
    {
        $agencies = Agency::where('expire_date' , '>' , Carbon::now()->toDateString())->where('expire_date', '!=' , null)->where('status' , '3')->orderBy('updated_at', 'DESC')->get();
        return view('Admin.agency.suspend' , compact('agencies'));
    }
}
