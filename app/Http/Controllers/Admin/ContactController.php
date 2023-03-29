<?php

namespace App\Http\Controllers\Admin;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Helpers\Helper;
use App\Http\Resources\ContactCollection;

class ContactController extends Controller
{
    public function index()
    {
        $title = 'فهرست پیام ها';
        $url = 'filter';
        $cards = Card::all();
        $delete = false;
        if(auth()->user()->isAbleTo('contact' || 'contact-detail'))
        {
            if(auth()->user()->isAbleTo('contact-delete'))
            {
                $delete = true;
            }
            return view('Admin.contact.all', compact('title' , 'url' , 'cards' , 'delete'));
        }
        return abort(401);
    }

    public function desktop()
    {
        $title = 'میزکار پیام ها';
        $url = 'desktop/filter';
        $cards = Card::all();
        $delete = false;
        if(auth()->user()->isAbleTo('contact' || 'contact-detail'))
        {
            if(auth()->user()->isAbleTo('contact-delete'))
            {
                $delete = true;
            }
            return view('Admin.contact.all', compact('title' , 'url' , 'cards' , 'delete'));
        }
        return abort(401);
    }

    public function filter()
    {
        $contacts = Contact::filter()->where('card_id', auth()->user()->roles->first()->card_id)->orderBy('status' , 'ASC')->latest();

        if (auth()->user()->hasPermission('contact-detail')) {
            $contacts = Contact::filter()->orderBy('status' , 'ASC')->latest();
        }
        return new ContactCollection($contacts->paginate(20));
    }

    public function filterDsktop()
    {
        $contacts = Contact::filter()->where('card_id', auth()->user()->roles->first()->card_id)->orderBy('status' , 'ASC')->where('status' , 0)->latest();
        return new ContactCollection($contacts->paginate(20));
    }

    public function edit(Contact $contact)
    {
        $cards = Card::all();
        return view('Admin.contact.edit', compact('contact' , 'cards'));
    }

    public function update(Request $request, Contact $contact)
    {
        $input = $request->all();
        $input['responder_id'] = auth()->user()->id;
        unset($input['check']);
        $contact->update($input);

        if ($request->check && $request->input('answer')) {
            $phones = $request->input('phone');
            $message = $request->input('answer');
            Helper::sms($phones , $message);
        }

        return redirect(route('contact.index'))->with([
            'update' => 'success'
        ]);
    }

    public function destroy(Contact $contact)
    {
        if (auth()->user()->isAbleTo('contact-delete')) {
            $contact->delete();
            return response([
                'operator' => true,
                'status'=>'success',
                'message'=>'حذف با موفقیت انجام گردید .'
            ]);
        }
        else
        {
            return response([
                'operator' => true,
                'status'=>'unauthorized',
                'message'=>'شما مجاز به انجام این کار نیستید.'
            ]);
        }
    }
}
