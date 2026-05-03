<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\City;
use App\Models\Faq;
use App\Models\Newsletter;
use App\Models\Organization;
use App\Models\Phone;
use App\Models\Service;
use App\Models\serviceField;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class HomeController extends Controller
{
    /**
     * Summary of index
     * @param User $callcenter
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(User $callcenter)
    {
        //forget representation session's if it is set
        session()->has('representation') ? session()->forget('representation') : '';
		if(isset(auth()->user()->organization_id))
        {
            $organ = Organization::find(auth()->user()->organization_id);
            return redirect(url('organizations/'.$organ->slug));
        }
        $sliders = Slider::filter()->whereNULL('representation_id')->whereStatus(true)->where('type' , 0)->orderBy('id', 'desc')->get();
        $sliders_mobile = Slider::filter()->whereNULL('representation_id')->whereStatus(true)->where('type' , 4)->orderBy('id', 'desc')->get();
        $services = Service::filter()->whereStatus(1)->where('isShow' , true)->orderBy('created_at', 'desc')->get();

        //dd(Factor::whereType('online')->whereStatus(true)->sum('price'));
        return view('site.home', compact('callcenter','sliders', 'services','sliders_mobile'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function message()
    {
        return view('site.message');
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function challenge()
    {
		 $sliders = Slider::filter()->whereStatus(true)->get();
         $services = Service::filter()->whereStatus(1)->where('challenge' , true)->orderBy('created_at', 'desc')->get();
        //dd(Factor::whereType('online')->whereStatus(true)->sum('price'));
        return view('site.challenge', compact('sliders', 'services'));
    }

    /**
     * @param User $callcenter
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function mobile(User $callcenter)
    {
        $sliders = Slider::filter()->whereStatus(true)->where('type' , null)->orderBy('id', 'desc')->get();
        $services = Service::filter()->whereStatus(1)->where('isShow' , true)->orderBy('created_at', 'desc')->get();
        return view('site.mobile', compact('callcenter' ,'sliders', 'services'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function newsletter(Request $request)
    {
        $email = $request->input('email');
        if (strpos($email, '@')) {
            $check = Newsletter::whereEmail($email)->first();
            if ($check) {

                return back()->with([
                    'newsletter_exist' => 'success'
                ]);

            } else {
                Newsletter::create($request->all());

                return back()->with([
                    'newsletter_success' => 'success'
                ]);

            }
        } else {

            return back()->with([
                'newsletter_error' => 'success'
            ]);

        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function phone(Request $request)
    {
        $input = $request->all();
        $input['phone'] = Helper::numberConverter($input['phone']);

        $this->validate($request, [
            'phone' => ['required'],
        ]);
        Phone::create($input);

        //set cookie
        Cookie::queue(Cookie::make('phone_newsletter', 'true', 1440 * 30));

        return back()->with([
            'phone_success' => 'success'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function cityselect(Request $request)
    {
        if ($request->ajax()) {
            $id = $request->input('id');
            if (City::where('province_id', $id)->get()->count() > 0) {
                $cities = City::where('province_id', $id)->get();
                return response()->json(['count' => 'available', 'cities' => $cities]);
            }
        }
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function faq()
    {
        $faqs = Faq::all();
        return view('site.faq' , compact('faqs'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function representation()
    {
        return view('site.representation.index');
    }

}
