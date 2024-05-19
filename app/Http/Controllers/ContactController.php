<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Card;
use App\Models\Contactsetting;
use App\Helpers\Helper;
use App\Models\Callcenter;
use App\Models\Role;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $contact = Contactsetting::first();
        $cards = Card::where('is_show' , true)->get();
        return view('site.contact', compact('contact', 'cards'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function job()
    {
        return view('site.job');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function savecontact(Request $request)
    {
        $request['phone'] = Helper::numberConverter($request->phone);
        $this->validate(request(), [
            'name' => 'required|string',
            'family' => 'required|string',
            'phone' => 'numeric|required|regex:/(09)[0-9]{9}/|digits:11|',
            'card_id' => 'required',
            'text' => 'required',
            'images.*' => 'mimes:jpg,jpeg,png,bmp',
            'gender' => ['required',Rule::in(array(0,1))],
            'province' => 'required|numeric|exists:provinces,id',
            'city' => 'required|numeric|exists:cities,id',
        ]);
        //check if user not exist -> create user
        User::createUser($request);
        $user = User::where('phone',$request->phone)->first();
        $contactsImages = null;
        if (!is_null($request->file('images'))) {
            foreach ($request->file('images') as $image) {
                $contactsImages[] = Helper::uploadImage($image, 'contactus');
            }
        }
        Contact::create(["name" => $request->name.' '.$request->family,
            "phone" => $request->phone,
            "card_id" => $request->card_id,
            "text" => $request->text, 'status' => '0', 'files' => $contactsImages,
            "representation_id" => session()->has('representation') ? session()->get('representation')->id : null,
        ]);
        //create task to follow up
        $callcenter_roles = Role::select('id')->where('card_id' , $request->card_id)->get();
        $description = 'تماس با ما ، موضوع :'.$request->text;
        Callcenter::createTask($user, $description , $callcenter_roles);

        return redirect()->back()->with([
            'contact_success' => 'success'
        ]);
    }
}
