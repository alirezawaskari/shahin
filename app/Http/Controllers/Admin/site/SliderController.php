<?php

namespace App\Http\Controllers\Admin\site;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::where('type',"0")->orderBy('id', 'DESC')->paginate(15);
        return view('Admin.site.slider.list', ['sliders' => $sliders]);
    }

    public function create()
    {
        return view('Admin.site.slider.create');
    }

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

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        if ($id) {
            $slider = Slider::find($id);
            return view('Admin.site.slider.edit', ['slider' => $slider]);
        }
        return redirect()->back();
    }

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

        return redirect()
            ->route('slider.index')
            ->with(['update' => 'success']);

    }

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
