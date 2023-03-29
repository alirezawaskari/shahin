<?php

namespace App\Http\Controllers\Admin;

use App\Models\GalleryCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GalleryCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $gallerycategories = GalleryCategory::latest()->get();
        return view('Admin.gallerycategory.all' , compact('gallerycategories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.gallerycategory.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'title' => 'required',
        ]);

        GalleryCategory::create($request->all());

        return redirect(route('gallerycategory.index'))->with([
            'gallerycategory_create'=>'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\GalleryCategory  $galleryCategory
     * @return \Illuminate\Http\Response
     */
    public function show(GalleryCategory $galleryCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\GalleryCategory  $galleryCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(GalleryCategory $galleryCategory)
    {
        return view('Admin.gallerycategory.all' , compact('galleryCategory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\GalleryCategory  $galleryCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GalleryCategory $galleryCategory)
    {
        $galleryCategory->update($request->all());

        return redirect(route('gallerycategory.index'))->with([
            'gallerycategory_update'=>'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\GalleryCategory $galleryCategory
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(GalleryCategory $galleryCategory)
    {
        $galleryCategory->delete();

        return redirect(route('gallerycategory.index'))->with([
            'gallerycategory_delete'=>'success'
        ]);

    }
}
