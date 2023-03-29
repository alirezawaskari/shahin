<?php

namespace App\Http\Controllers\Admin\site;

use App\Http\Controllers\Controller;
use App\Models\CategoryNews;
use Illuminate\Http\Request;

class CategoryNewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $category_news = CategoryNews::orderBy('id', 'DESC')->paginate(15);
        return view('Admin.site.categoryNews.list', compact('category_news'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.site.categoryNews.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['title' => 'required'], ['title.required' => 'لطفا عنوان را وارد کنید',]);

        CategoryNews::create($request->all());
        return redirect('/dashboard/site/categoryNews')->with([ 'create' => 'success']);
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
    public function edit($id){
        if ($id) {
            $category_news = CategoryNews::find($id);
            return view('Admin.site.categoryNews.edit', compact('category_news'));
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
    public function update(Request $request, $id){
        $request->validate(['title' => 'required'], ['title.required' => 'لطفا عنوان را وارد کنید',]);

        $category_news = CategoryNews::find($id);
        $category_news->update($request->all());

        return redirect('/dashboard/site/categoryNews')->with([ 'update' => 'success']);
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
}
