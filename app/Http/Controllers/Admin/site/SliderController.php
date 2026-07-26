<?php

namespace App\Http\Controllers\Admin\site;

use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Models\Representation;
use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $sliders = Slider::filter()->orderBy('id', 'DESC')->paginate(15);
        if(auth()->user()->admin_representation_id)
        {
            $sliders = auth()->user()->admin_representation->sliders()->orderBy('id', 'DESC')->paginate(15);
        }
        return view('Admin.site.slider.list', ['sliders' => $sliders]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function indexOldSite()
    {
        $sliders = Slider::filter()->whereStatus(true)->where('type' , null)->orderBy('id', 'desc')->orderBy('id', 'DESC')->paginate(15);
        if(auth()->user()->admin_representation_id)
        {
            $sliders = auth()->user()->admin_representation->sliders()->whereStatus(true)->where('type' , null)->orderBy('id', 'desc')->orderBy('id', 'DESC')->paginate(15);
        }
        return view('Admin.site.slider.list', ['sliders' => $sliders]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function indexMobileOldSite()
    {
        $sliders = Slider::filter()->whereStatus(true)->where('type' , 4)->orderBy('id', 'desc')->orderBy('id', 'DESC')->paginate(15);
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
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function createMobileOld()
    {
        $representations = Representation::where('status' , true)->get();
        return view('Admin.site.slider.createMobileOld' , compact('representations'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
     * The temp media id must point at an IMAGE. The shared uploadFilesTemporarily endpoint
     * also accepts pdf/doc/zip, so the `accept` attribute on the picker is only a hint —
     * this is the enforcement behind it.
     */
    private function imageOnly(?string $tempId): bool
    {
        if (empty($tempId)) {
            return true; // "required" is a separate rule; nothing to check here
        }
        $media = \App\Models\Media::find($tempId);

        return $media && str_starts_with((string) $media->mime_type, 'image/');
    }

    public function store(Request $request)
    {
        // image_path, NOT image: the ajax widget clears the file input after uploading
        // (adminJs.js sets this.value=''), so `image` is never submitted and requiring it
        // rejected every save — even when an image had been uploaded.
        $request->validate(['title' => 'required',
            'image_path' => 'required',
            'link' => 'required',
            ],
            ['title.required' => 'لطفا عنوان را وارد کنید',
                'image_path.required' => 'لطفا عکس را وارد کنید',
                'link.required' => 'لطفا لینک را وارد کنید',
            ]
        );
        if (! $this->imageOnly($request->image_path)) {
            return back()->withErrors(['image_path' => 'فایل انتخاب شده باید یک تصویر باشد'])->withInput();
        }
        $request['type'] = 0;
        // legacy media columns are never mass-assigned — see AdoptsLegacyMedia::exceptLegacyMediaKeys()
        $slider = Slider::create((new Slider)->exceptLegacyMediaKeys($request->all()));
        MediaLibraryHelper::attachTo($slider, $request->image_path, 'img');
        if(auth()->user()->admin_representation_id)
        {
            $slider->update(['representation_id' => auth()->user()->admin_representation_id]);
        }
        return redirect()
            ->route('slider.index')
            ->with(['create' => 'success']);
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeOldSliderMobile(Request $request)
    {
        $request->validate(['title' => 'required',
            'image_path' => 'required',   // see store()
            'link' => 'required',
            ],
            ['title.required' => 'لطفا عنوان را وارد کنید',
                'image_path.required' => 'لطفا عکس را وارد کنید',
                'link.required' => 'لطفا لینک را وارد کنید',
            ]
        );
        if (! $this->imageOnly($request->image_path)) {
            return back()->withErrors(['image_path' => 'فایل انتخاب شده باید یک تصویر باشد'])->withInput();
        }
        $request['type'] = 4;
        $slider = Slider::create((new Slider)->exceptLegacyMediaKeys($request->all()));
        MediaLibraryHelper::attachTo($slider, $request->image_path, 'img');
        if(auth()->user()->admin_representation_id)
        {
            $slider->update(['representation_id' => auth()->user()->admin_representation_id]);
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

        // on edit the saved image is kept, so image_path is optional — but if one IS
        // uploaded it still has to be an image
        if (! $this->imageOnly($request->image_path)) {
            return back()->withErrors(['image_path' => 'فایل انتخاب شده باید یک تصویر باشد'])->withInput();
        }

        $s = Slider::find($id);
        $s->update($s->exceptLegacyMediaKeys($request->all()));
        MediaLibraryHelper::attachTo($s, $request->image_path, 'img');
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
                $slider->delete();
            }
            return ['status' => 'true'];
        }
        return ['status' => 'false'];
    }

}
