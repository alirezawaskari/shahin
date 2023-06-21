<?php

namespace App\Http\Controllers\Admin\site;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $sliders = Slider::where('type',"0")->orderBy('id', 'DESC')->paginate(15);
        return view('Admin.site.slider.list', ['sliders' => $sliders]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function indexOldSite()
    {
        $sliders = Slider::whereStatus(true)->where('type' , null)->orderBy('id', 'desc')->orderBy('id', 'DESC')->paginate(15);
        return view('Admin.site.slider.list', ['sliders' => $sliders]);
    }


    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        return view('Admin.site.slider.create');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate(['title' => 'required',
            'image' => 'required',
            'link' => 'required',
            ],
            ['title.required' => 'لطفا عنوان را وارد کنید',
                'image.required' => 'لطفا عکس را وارد کنید',
                'link.required' => 'لطفا لینک را وارد کنید',
            ]
        );
        $request['type'] = 0;
        $s = Slider::create($request->all());
        if(isset( $request->image_path)) {
            $s->img = Helper::moveImage($request->image_path,'slider');
            $s->save();
        }

        return redirect()
            ->route('slider.index')
            ->with(['create' => 'success']);
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
            $slider = Slider::find($id);
            return view('Admin.site.slider.edit', ['slider' => $slider]);
        }
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate(['title' => 'required',
            'link' => 'required',
        ],
            ['title.required' => 'لطفا عنوان را وارد کنید',
                'link.required' => 'لطفا لینک را وارد کنید',
            ]
        );

        $s = Slider::find($id);
        $s->update($request->all());

        if(isset( $request->image_path)) {
            if (\File::exists(public_path($s->image))) {
                \File::delete(public_path($s->image));
            }
            $s->img = Helper::moveImage($request->image_path,'slider');
            $s->save();
        }
        return redirect()->back();
        return redirect()
            ->route('slider.index')
            ->with(['update' => 'success']);

    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if ($id) {
            $slider = Slider::find($id);
            if(\File::exists(public_path($slider->img))){
                \File::delete(public_path($slider->img));
            }
            $slider->delete();
            return redirect()
                ->route('slider.index')
                ->with(['delete' => 'success']);
        }
        return redirect()->back();
    }


    /**
     * @param Request $request
     * @return string[]
     */
    public function removeImgSlider(Request $request)
    {
        if (isset($request->slider_id)) {
            $slider = Slider::find($request->slider_id);
            if ($slider) {
                if (\File::exists(public_path($slider->image))) {
                    \File::delete(public_path($slider->image));
                }
                $slider->delete();
            }
            return ['status' => 'true'];
        }
        return ['status' => 'false'];
    }

}
