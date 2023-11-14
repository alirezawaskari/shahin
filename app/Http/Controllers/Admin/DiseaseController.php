<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiseaseCollection;
use App\Models\Disease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiseaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('Admin.disease.all' );
    }

    public function filter()
    {
        $disease = Disease::filter()->latest();
        return new DiseaseCollection($disease->paginate(20));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'unique:diseases,name'],
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'message' => 'عنوان تکراری است ، قبلا ذخیره شده است .']);
        }

        Disease::create([
            'name'=>$request->name
        ]);

        return response(['message'=>' با موفقیت ثبت گردید .','status'=>'success']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Disease $disease)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Disease $disease)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Disease $disease)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return response(['status'=>'error','messages'=>$validator->messages()->all()]);
        }
        $disease->update([
           'name' =>$request->name,
        ]);
        return response([
            'message'=>'عملیات موفق ',
            'status'=>'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Disease $disease)
    {
        //
    }
}
