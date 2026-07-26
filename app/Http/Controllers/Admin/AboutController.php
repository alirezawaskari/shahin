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

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        return view('Admin.about.create');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
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
        // Spatie: attach each box image from its temp media id ($image_*_path).
        foreach (['image_two', 'image_three', 'image_four', 'image_five'] as $collection) {
            $pathKey = $collection . '_path';
            if ($request->$pathKey) {
                \App\Helpers\MediaLibraryHelper::attachTo($about, $request->$pathKey, $collection);
            }
        }

        return redirect('/dashboard/about')->with([
            'create' => 'success'
        ]);
    }


    /**
     * @param $id
     */
    public function show($id)
    {
        //
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        if ($id) {
            $about = About::find($id);
            return view('Admin.about.edit', ['about' => $about]);
        }
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {

        $about = About::find($id);

        // The box images are required (see store()). On edit a re-upload isn't required —
        // the saved image is kept — so the rule is "must not end up empty": required only
        // for a box that has no image yet. Mirrors the client-side check in the view, so
        // the two can't disagree.
        $imageRules = [];
        $imageMessages = [];
        $labels = [
            'image_two' => 'عکس باکس دوم', 'image_three' => 'عکس باکس سوم',
            'image_four' => 'عکس باکس چهارم', 'image_five' => 'عکس باکس پنجم',
        ];
        foreach ($labels as $collection => $label) {
            if ($about && ! $about->$collection) {
                $imageRules[$collection . '_path'] = 'required';
                $imageMessages[$collection . '_path.required'] = 'لطفا ' . $label . ' را بارگذاری کنید';
            }
        }

        $request->validate(array_merge([
            'content_one' => 'required', 'content_two' => 'required', 'content_three' => 'required',
            'content_four' => 'required', 'content_five' => 'required',
        ], $imageRules),
            array_merge([
                'content_one.required' => 'لطفا متن اول را وارد کنید',
                'content_two.required' => 'لطفا متن دوم را وارد کنید',
                'content_three.required' => 'لطفا متن سوم را وارد کنید',
                'content_four.required' => 'لطفا متن چهارم را وارد کنید',
                'content_five.required' => 'لطفا متن پنجم را وارد کنید',
            ], $imageMessages)
        );
        $about->update($request->all());

        // Spatie: attach any newly uploaded box image (attachTo replaces the single file
        // in that collection, so no manual old-file deletion is needed).
        foreach (['image_two', 'image_three', 'image_four', 'image_five'] as $collection) {
            $pathKey = $collection . '_path';
            if ($request->$pathKey) {
                \App\Helpers\MediaLibraryHelper::attachTo($about, $request->$pathKey, $collection);
            }
        }

        Cache::forget('about');
        return redirect('/dashboard/about')->with([
            'update' => 'success'
        ]);
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {
        //
    }

}
