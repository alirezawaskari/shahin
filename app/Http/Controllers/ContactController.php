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
    public function index()
    {

        $contact = Cache::remember('contact', Carbon::now()->addMinute(60), function () {
            return Contactsetting::first();
        });
        $cards = Card::where('is_show' , true)->get();
        return view('site.contact', compact('contact', 'cards'));
    }

    public function job()
    {
        return view('site.job');
    }

    public function savecontact(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required|string',
            'phone' => 'required',
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
