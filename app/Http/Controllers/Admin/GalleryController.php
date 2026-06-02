<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $galleries = Gallery::latest()->get();
        return view('Admin.gallery.all' , compact('galleries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.gallery.create');
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
            'image' => 'required|mimes:jpg,jpeg,png,svg,gif',
        ]);

        $url = $this->upload($request->file('image'));
        $input = $request->all();

        Gallery::create(array_merge($input, ['image' => $url]));

        return redirect(route('gallery.index'))->with([
            'gallery_create'=>'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Gallery  $gallery
     * @return \Illuminate\Http\Response
     */
    public function show(Gallery $gallery)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Gallery  $gallery
     * @return \Illuminate\Http\Response
     */
    public function edit(Gallery $gallery)
    {
        return view('Admin.gallery.edit' , compact('gallery'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Gallery  $gallery
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Gallery $gallery)
    {
        $img = $request->file('image');
        $input = $request->all();

        if ($img) {
            $url = $this->upload($img);
            $gallery->update(['image' => $url]);
        }


        unset($input['image']);

        $gallery->update($input);

        return redirect(route('gallery.index'))->with([
            'gallery_update'=>'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Gallery $gallery
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Gallery $gallery)
    {
        Helper::deleteImage($gallery->image);
        $gallery->delete();

        return redirect()->back()->with([
            'message'=>'تصویر با موفقیت حذف شد'
        ]);
    }
}
