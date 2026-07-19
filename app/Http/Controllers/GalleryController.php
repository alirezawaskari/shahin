<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\GalleryCategory;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $galleries = Gallery::latest()->get();
        $gallerycategories = GalleryCategory::get();

        if ($request->ajax()) {
            $view = view('site.gallerydata',compact('galleries'))->render();
            return response()->json(['html'=>$view]);
        }

        return view('site.gallery' , compact('galleries' , 'gallerycategories'));
    }
}
