<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Card;
use App\Models\Contactsetting;
use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
        $this->validate(request(), [
            'name' => 'required|string',
            'phone' => 'numeric|required|regex:/(09)[0-9]{9}/|digits:11|',
            'card_id' => 'required',
            'text' => 'required',
            'images.*' => 'mimes:jpg,jpeg,png,bmp'
        ]);
        $contactsImages = null;
        if (!is_null($request->file('images'))) {
            foreach ($request->file('images') as $image) {
                $contactsImages[] = Helper::uploadImage($image, 'contactus');
            }
        }
        Contact::create(["name" => $request->name,
            "phone" => $request->phone,
            "card_id" => $request->card_id,
            "text" => $request->text, 'status' => '0', 'files' => $contactsImages]);

        return redirect()->back()->with([
            'contact_success' => 'success'
        ]);
    }
}
