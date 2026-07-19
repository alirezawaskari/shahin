<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Models\AgencyType;
use App\Models\Gallery;
use App\Models\User;
use Illuminate\Http\Request;

class AgencyTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $agencyTypes = AgencyType::orderBy('id', 'DESC')->paginate(15);
        return view('Admin.agencyTypes.all', ['agencyTypes' => $agencyTypes]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){
        $users = User::with('roles')->whereHas('roles', function ($query)  {
            $query->where('roles.name','superadmin-representation');
        })->get();
        return view('Admin.agencyTypes.create',compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'en_title' => 'required',
            'image_path' => 'required',
            'total_price' => 'required|integer',
            'prepayment' => 'required|integer',
        ],
            [
                'title.required' => 'لطفا عنوان را وارد کنید',
                'en_title.required' => 'لطفا عنوان انگلیسی را وارد کنید',
                'total_price.required' => 'لطفا قیمت کل را وارد کنید',
                'total_price.integer' => 'لطفا قیمت کل را درست وارد کنید',
                'prepayment.required' => 'لطفا پیش پرداخت را وارد کنید',
                'prepayment.integer' => 'لطفا پیش پرداخت را درست وارد کنید',
                'image_path.required' => 'لطفا عکس را وارد کنید',
            ]
        );

        if($request->total_price > 0 )$request['total_price'] = str_replace(",","",$request->total_price);
        if($request->prepayment > 0 )$request['prepayment'] = str_replace(",","",$request->prepayment);

        $request['is_show'] = isset($request['is_show']) ? 1 : 0;
        // image fields are stored via Spatie (attachTo below), never as column values
        $agencyTypes = AgencyType::create($request->all());

        MediaLibraryHelper::attachTo($agencyTypes, $request->image_path, 'image');
        foreach ($request->multiple_image ?? [] as $multiple_image) {
            if (isset($multiple_image['file'])) {
                MediaLibraryHelper::attachTo($agencyTypes, $multiple_image['file'], 'gallery', [
                    'title' => $multiple_image['title'] ?? null,
                ]);
            }
        }

        return redirect('/dashboard/agencyType')->with([
            'create' => 'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if ($id) {
            $users = User::with('roles')->whereHas('roles', function ($query)  {
                $query->where('roles.name','superadmin-representation');
            })->get();
            $agencyTypes = AgencyType::find($id);
            return view('Admin.agencyTypes.edit', compact('users','agencyTypes'));
        }
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'en_title' => 'required',
            'total_price' => 'required',
            'prepayment' => 'required',
            'content' => 'required'
        ],
            [
                'title.required' => 'لطفا عنوان را وارد کنید',
                'en_title.required' => 'لطفا عنوان انگلیسی را وارد کنید',
                'total_price.required' => 'لطفا قیمت کل را وارد کنید',
                'prepayment.required' => 'لطفا پیش پرداخت را وارد کنید',
                'content.required' => 'لطفا توضیحات را وارد کنید',
            ]
        );
        if($request->total_price > 0 )$request['total_price'] = str_replace(",","",$request->total_price);
        if($request->prepayment > 0 )$request['prepayment'] = str_replace(",","",$request->prepayment);

        $request['is_show'] = isset($request['is_show']) ? $request['is_show'] : 0;
        $agencyTypes = AgencyType::find($id);
        // image fields handled by Spatie (attachTo below); never column values
        $agencyTypes->update($request->all());

        MediaLibraryHelper::attachTo($agencyTypes, $request->image_path, 'image');
        foreach ($request->multiple_image ?? [] as $extra_file) {
            if (isset($extra_file['file'])) {
                MediaLibraryHelper::attachTo($agencyTypes, $extra_file['file'], 'gallery', [
                    'title' => $extra_file['title'] ?? null,
                ]);
            }
        }

        // persist edited titles of the already-saved gallery images
        foreach ($request->gallery_title ?? [] as $mediaId => $title) {
            $media = \App\Models\Media::where('id', $mediaId)
                ->where('model_type', get_class($agencyTypes))
                ->where('model_id', $agencyTypes->id)
                ->where('collection_name', 'gallery')
                ->first();
            if ($media) {
                $media->setCustomProperty('title', $title)->save();
            }
        }

        return redirect('/dashboard/agencyType')->with([
            'update' => 'success'
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($id) {
            $agencyTypes= AgencyType::find($id);
            // media is preserved on this soft delete (Spatie's cascade purges it only
            // on force delete; the native App\Models\Media::delete() keeps that safe).
            $agencyTypes->delete();
            return redirect('/dashboard/agencyType')->with([ 'delete' => 'success' ]);
        }
        return redirect()->back();
    }
}
