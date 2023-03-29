<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AboutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $abouts = About::all();
        return view('Admin.about.list', ['abouts' => $abouts]);
    }

    public function create()
    {
        return view('Admin.about.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'image_two_path' => 'required', 'image_three_path' => 'required', 'image_four_path' => 'required'
            ,'image_five_path' => 'required',
            'content_one' => 'required', 'content_two' => 'required', 'content_three' => 'required',
            'content_four' => 'required', 'content_five' => 'required',
        ],
            [
                'image_two_path.required' => 'لطفا عکس باکس دوم را وارد کنید',
                'image_three_path.required' => 'لطفا عکس باکس سوم را وارد کنید',
                'image_four_path.required' => 'لطفا عکس باکس چهارم را وارد کنید',
                'image_five_path.required' => 'لطفا عکس باکس پنجم را وارد کنید',
                'content_one.required' => 'لطفا متن اول را وارد کنید',
                'content_two.required' => 'لطفا متن دوم را وارد کنید',
                'content_three.required' => 'لطفا متن سوم را وارد کنید',
                'content_four.required' => 'لطفا متن چهارم را وارد کنید',
                'content_five.required' => 'لطفا متن پنجم را وارد کنید',
            ]
        );

        $about = About::create($request->all());
        if(isset( $request->image_two_path)) {
            $about->image_two = Helper::moveImage($request->image_two_path,'about');
        }
        if(isset( $request->image_three_path)) {
            $about->image_three = Helper::moveImage($request->image_three_path,'about');
        }
        if(isset( $request->image_four_path)) {
            $about->image_four = Helper::moveImage($request->image_four_path,'about');
        }
        if(isset( $request->image_five_path)) {
            $about->image_five = Helper::moveImage($request->image_five_path,'about');
        }

        $about->save();

        return redirect('/dashboard/about')->with([
            'create' => 'success'
        ]);
    }


    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        if ($id) {
            $about = About::find($id);
            return view('Admin.about.edit', ['about' => $about]);
        }
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'content_one' => 'required', 'content_two' => 'required', 'content_three' => 'required',
            'content_four' => 'required', 'content_five' => 'required',
        ],
            [
                'content_one.required' => 'لطفا متن اول را وارد کنید',
                'content_two.required' => 'لطفا متن دوم را وارد کنید',
                'content_three.required' => 'لطفا متن سوم را وارد کنید',
                'content_four.required' => 'لطفا متن چهارم را وارد کنید',
                'content_five.required' => 'لطفا متن پنجم را وارد کنید',
            ]
        );

        $about = About::find($id);
        $about->update($request->all());

        if(isset( $request->image_two_path)) {
            if (\File::exists(public_path($about->image_two))) {
                \File::delete(public_path($about->image_two));
            }
            $about->image_two = Helper::moveImage($request->image_two_path,'about');
        }
        if(isset( $request->image_three_path)) {
            if (\File::exists(public_path($about->image_three))) {
                \File::delete(public_path($about->image_three));
            }
            $about->image_three = Helper::moveImage($request->image_three_path,'about');
        }
        if(isset( $request->image_four_path)) {
            if (\File::exists(public_path($about->image_four))) {
                \File::delete(public_path($about->image_four));
            }
            $about->image_four = Helper::moveImage($request->image_four_path,'about');
        }
        if(isset( $request->image_five_path)) {
            if (\File::exists(public_path($about->image_five))) {
                \File::delete(public_path($about->image_five));
            }
            $about->image_five = Helper::moveImage($request->image_five_path,'about');
        }

        $about->save();
        Cache::forget('about');
        return redirect('/dashboard/about')->with([
            'update' => 'success'
        ]);
    }

    public function destroy($id)
    {
        //
    }

}
