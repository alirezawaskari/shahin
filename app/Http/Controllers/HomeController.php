<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Factor;
use App\Models\Faq;
use App\Helpers\Helper;
use App\Models\Newsletter;
use App\Models\Phone;
use App\Models\Service;
use App\Models\serviceField;
use App\Models\Slider;
use App\Models\User;
use App\Models\Organization;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use PragmaRX\Tracker\Vendor\Laravel\Facade as Tracker;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $callcenter)
    {
		if(isset(auth()->user()->organization_id))
        {
            $organ = Organization::find(auth()->user()->organization_id);
            return redirect(url('organizations/'.$organ->slug));
        }
        $sliders = Slider::whereStatus(true)->where('type' , null)->orderBy('id', 'desc')->get();
        $services = Service::whereStatus(1)->where('isShow' , true)->orderBy('created_at', 'desc')->get();

        //dd(Factor::whereType('online')->whereStatus(true)->sum('price'));
        return view('site.home', compact('callcenter','sliders', 'services'));
    }
	  public function message()
    {
        return view('site.message');
    }
	public function challenge()
    {
		 $sliders = Slider::whereStatus(true)->get();
         $services = Service::whereStatus(1)->where('challenge' , true)->orderBy('created_at', 'desc')->get();
        //dd(Factor::whereType('online')->whereStatus(true)->sum('price'));
        return view('site.challenge', compact('sliders', 'services'));
    }
    public function mobile(User $callcenter)
    {
        $sliders = Slider::whereStatus(true)->where('type' , null)->orderBy('id', 'desc')->get();
        $services = Service::whereStatus(1)->where('isShow' , true)->orderBy('created_at', 'desc')->get();
        return view('site.mobile', compact('callcenter' ,'sliders', 'services'));
    }
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

    public function faq()
    {
        $faqs = Faq::all();
        return view('site.faq' , compact('faqs'));
    }

    public function representation()
    {
        return view('site.representation.index');
    }

}
