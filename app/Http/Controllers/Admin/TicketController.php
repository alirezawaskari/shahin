<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\TicketCollection;
use App\Models\Ticket;
use App\Models\TicketAnswer;
use App\Models\User;
use App\Events\ticketNotification;
use App\Helpers\Helper;
use App\Models\Representation;
use App\Models\Center;
use App\Models\Role;
use App\Models\Card;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Employee;

class TicketController extends Controller
{
    /**
     *
     */
    public function index()
    {
        //
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function sendTicket()
    {
        $employees = Employee::all();
        $title = 'فهرست تیکت های ارسالی';
        $url = 'send/filter';

        return view('Admin.ticket.all', compact('employees' , 'title' , 'url'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function receivedTicket()
    {
        $employees = Employee::all();
        $title = 'فهرست تیکت های دریافتی';
        $url = 'received/filter';

        return view('Admin.ticket.all', compact('employees' , 'title' , 'url'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function desktop()
    {
        $employees = Employee::all();
        $title = 'میزکار تیکت های دریافتی';
        $url = 'desktop/filter';

        return view('Admin.ticket.all', compact('employees' , 'title' , 'url'));
    }

    /**
     * @return TicketCollection
     */
    public function filterSend()
    {
        $tickets = Ticket::filter()->where('user_id', auth()->user()->id)->latest();

        return new TicketCollection($tickets->paginate(20));
    }

    /**
     * @return TicketCollection
     */
    public function filterReceived()
    {
        $tickets = auth()->user()->tickets()->filter()->latest();
        if (auth()->user()->hasRole('superadmin')) {
            $tickets = Ticket::filter()->latest();
        }
        return new TicketCollection($tickets->paginate(20));
    }

    /**
     * @return TicketCollection
     */
    public function filterDesktop()
    {
        $tickets = auth()->user()->tickets()->filter()->where('status' , false);
        return new TicketCollection($tickets->paginate(20));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        $cards = Card::all();
        $representations = Representation::all();
        $centers = Center::all();
        $role = Role::where('name' , 'presentore')->first();
        $presentores = $role->users->where('level' , 'admin');

        return view('Admin.ticket.create', compact('cards', 'representations', 'centers' , 'presentores'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'title' => 'required|string',
            'content' => 'required|string',
            'receiver_id' => 'required',
        ]);
        $inputs = $request->all();
        $representations = isset($inputs['representation_id']) ? $inputs['representation_id'] : '';
        $centers = isset($inputs['center_id']) ? $inputs['center_id'] : '';
        $card = isset($inputs['card_id']) ? $inputs['card_id'] : '';
        $presentores = isset($inputs['presentore_id']) ? $inputs['presentore_id'] : '';

        unset($inputs['representation_id']);
        unset($inputs['center_id']);
        unset($inputs['card_id']);
        unset($inputs['presentore_id']);
        unset($inputs['urlfile']);
        unset($inputs['receiver_id']);
        unset($inputs['role']);

        $inputs['user_id'] = auth()->user()->id;
        $ticket = Ticket::create($inputs);
        $ticket->users()->sync($request->receiver_id);
//        if ($representations) {
//            foreach($representations as $representation)
//            {
//                $role = Role::where('name' , 'superadmin-representation')->first();
//                $receiver = $role->users->where('admin_representation_id' , $representation)->first();
//                $ticket->users()->sync($receiver->id);
//            }
//        }

//        if ($centers) {
//            foreach($centers as $center)
//            {
//                $role = Role::where('name' , 'store-manager')->first();
//                $receiver = $role->users->where('center_id' , $center)->first();
//
//				if(!$receiver)
//				{
//					$role = Role::where('name' , 'center-manager')->first();
//					$receiver = $role->users->where('center_id' , $center)->first();
//			    }
//                $ticket->users()->sync($receiver->id);
//            }
//        }

//        if ($presentores) {
//            foreach($presentores as $presentore)
//            {
//                $ticket->users()->sync($presentore);
//            }
//        }

//        if ($card && $card != 'all') {
//            $role = Role::where('card_id' , $card)->first();
//            $receivers = $role->users->where('level' , 'admin');
//			if($card == 6)
//			{
//				$receivers = User::where('id' , '63')->get();
//			}
//            foreach($receivers as $receiver)
//            {
//                $ticket->users()->sync($receiver->id);
//            }
//        }

        $file_ticket = $request->urlfile;
        $temp = [];
        if ($file_ticket) {
            $files = $file_ticket;
            foreach ($files as $file) {
                if ($file) {
                    if (isset($file_ticket)) {
                        $url = Helper::moveImage($file,'document');
                        $temp[] = $url;
                    }
                }
            }
			$ticket->update(['file' => $temp]);
        }

        event(new ticketNotification(User::find($request->receiver_id) , $ticket));
//        foreach($ticket->users as $user)
//		{
//		    event(new ticketNotification($user , $ticket));
//		}

        return redirect(route('send.tickets'))->with([
            'create' => 'success'
        ]);
    }


    /**
     * @param Ticket $ticket
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function show(Ticket $ticket)
    {
        $cards = Card::all();
        return view('Admin.ticket.show', compact('ticket', 'cards'));
    }

    /**
     * @param Ticket $ticket
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(Ticket $ticket)
    {
        $cards = Card::all();
        $representations = Representation::all();
        $centers = Center::all();
        $role = Role::where('name' , 'presentore')->first();
        $presentores = $role->users->where('level' , 'admin');
        $answers = $ticket->answers;

        return view('Admin.ticket.edit', compact('ticket', 'cards', 'answers', 'representations', 'centers' , 'presentores'));
    }

    /**
     * @param Request $request
     * @param Ticket $ticket
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Ticket $ticket)
    {
        $this->validate(request(), [
            'title' => 'required|string',
            'content' => 'nullable|string',
            'file' => "required|max:10000"
        ]);

        $inputs = $request->all();

        unset($inputs['urlfile']);

        $file_ticket = $request->urlfile;
        if (isset($file_ticket)) {
            $files = $file_ticket;
            foreach ($files as $file) {
                if ($file) {
                    if (isset($file_ticket)) {
                        $url = Helper::moveImage($file,'document');
                        $temp[] = $url;
                    }
                }
            }
			$inputs['file'] = $temp;
        }

        $ticket->update($inputs);

        return redirect(route('send.tickets'))->with([
            'contract_update' => 'success'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refer(Request $request)
    {
        $inputs = $request->all();
        $representations = isset($inputs['representation_id']) ? $inputs['representation_id'] : '';
        $centers = isset($inputs['center_id']) ? $inputs['center_id'] : '';
        $card = isset($inputs['card_id']) ? $inputs['card_id'] : '';
        $presentores = isset($inputs['presentore_id']) ? $inputs['presentore_id'] : '';

        unset($inputs['representation_id']);
        unset($inputs['center_id']);
        unset($inputs['card_id']);
        unset($inputs['presentore_id']);
        unset($inputs['receiver_id']);
        unset($inputs['role']);

        $inputs['user_id'] = auth()->user()->id;
        $ticket = Ticket::find($request->ticket_id);

        $ticket->users()->sync($request->receiver_id);
        event(new ticketNotification(User::find($request->receiver_id) , $ticket));


        /*if ($representations) {
            foreach($representations as $representation)
            {
                $role = Role::where('name' , 'superadmin-representation')->first();
                $receiver = $role->users->where('admin_representation_id' , $representation)->first();
                $ticket->users()->sync($receiver->id);

                event(new ticketNotification($receiver , $ticket));
            }
        }*/

       /* if ($centers) {
            foreach($centers as $center)
            {
                $role = Role::where('name' , 'store-manager')->first();
                $receiver = $role->users->where('center_id' , $center)->first();
                $ticket->users()->sync($receiver->id);
                event(new ticketNotification($receiver , $ticket));
            }
        }*/

      /*  if ($presentores) {
            foreach($presentores as $presentore)
            {
                $receiver  = User::find($presentore);
                $ticket->users()->sync($presentore);
                event(new ticketNotification($receiver , $ticket));
            }
        }*/

        /*if ($card && $card != 'all') {
            $role = Role::where('card_id' , $card)->first();
            $receivers = $role->users->where('level' , 'admin');
			if($card == 6)
			{
				$receivers = User::where('id' , '63')->get();
			}
            foreach($receivers as $receiver)
            {
                $ticket->users()->sync($receiver->id);
                event(new ticketNotification($receiver , $ticket));
            }
        }*/

        $inputs['ticket_id'] = $ticket->id;
        $inputs['user_id'] = auth()->user()->id;
        $ticket_answer = TicketAnswer::create($inputs);
        $ticket = $ticket_answer->ticket;
        $ticket->update(['status' => 0]);

        return redirect()->back()->with([
            'create' => 'success'
        ]);
    }

    /**
     * @param Ticket $contact
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Ticket $contact)
    {
        $contact->delete();
        return redirect()->back()->with([
            'delete' => 'success'
        ]);
    }

    /**
     * @param $file
     * @return string
     */
    private function upload($file)
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        $filepath = "/upload/ticket/{$year}/{$month}/";

        $rand = rand(1, 999);
        $filename = "{$rand}_" . $file->getClientOriginalName();
        $file->move(public_path($filepath), $filename);
        return $filepath . $filename;
    }
}
