<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Validation;
use Illuminate\Http\Request;

class ValidationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $validations = Validation::orderBy('id', 'DESC')->paginate(15);
        return view('Admin.site.validation.list', ['validations' => $validations]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.site.validation.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['title' => 'required'], ['title.required' => 'لطفا عنوان را وارد کنید']);
        Validation::create($request->all());
        return redirect('/dashboard/validation')->with(['create' => 'success']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if ($id) {
            $validation= Validation::find($id);
            return view('Admin.site.validation.edit', compact('validation'));
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
        $request->validate(['title' => 'required'],['title.required' => 'لطفا عنوان را وارد کنید']);

        $validation = Validation::find($id);
        $validation->update($request->all());

        return redirect('/dashboard/validation')->with(['update' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($id) {
            $validation = Validation::find($id);
            $validation->delete();
            return redirect('/dashboard/validation')->with(['delete' => 'success']);
        }
        return redirect()->back();
    }
}
