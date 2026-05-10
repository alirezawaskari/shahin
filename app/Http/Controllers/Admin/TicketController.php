<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\TicketCollection;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketUser;
use App\Models\User;
use App\Events\ticketNotification;
use App\Helpers\Helper;
use App\Models\Representation;
use App\Models\Center;
use App\Models\Role;
use App\Models\Card;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Employee;

class TicketController extends Controller
{

    public function index($type)
    {
        $employees = Employee::all();
        switch ($type) {
            case 'all':
                $title = 'فهرست مکاتبات';
                $url = 'all/filter';
                break;
            case 'send':
                $title = 'فهرست مکاتبات ارسالی';
                $url = 'send/filter';
                break;
            case 'received':
                $title = 'فهرست مکاتبات دریافتی';
                $url = 'received/filter';
                break;
            case 'desktop':
                $title = 'میزکار مکاتبات دریافتی';
                $url = 'desktop/filter';
                break;
            case 'refer':
                $title = 'فهرست مکاتبات ارجاعی';
                $url = 'refer/filter';
                break;
            default :
                abort(404);
        }
        return view('Admin.ticket.all', compact('employees', 'title', 'url'));
    }


    /**
     * @return TicketCollection
     */
    public function filterAll()
    {
        $tickets = Ticket::filter()->latest();
        return new TicketCollection($tickets->paginate(20));
    }

    public function filterSend()
    {
        $tickets = Ticket::filter()->where('user_id', auth()->user()->id)->latest();
        return new TicketCollection($tickets->paginate(20));
    }

    public function filterReceived()
    {
        $tickets = Ticket::filter()->latest();
        if (auth()->user()->admin_representation) {
            $tickets->whereHas('users', function ($query) {
                $query->where('admin_representation_id', auth()->user()->admin_representation_id);
            });
        }

        if (!auth()->user()->hasPermission('show_all_received')) {
            $tickets->whereHas('users', function ($query) {
                $query->where('receiver_id', auth()->user()->id);
            });
        }
        return new TicketCollection($tickets->paginate(20));
    }

    public function filterDesktop()
    {
        $tickets = auth()->user()->tickets()->filter()->where('status', false);
        return new TicketCollection($tickets->latest()->paginate(20));
    }
    public function filterRefer()
    {
        $tickets = Ticket::filter()->latest();
        $tickets->whereHas('users', function ($query) {
            $query->where('sender_id', auth()->user()->id);
        });
        return new TicketCollection($tickets->latest()->paginate(20));
    }


    public function create()
    {
        $cards = Card::all();
        return view('Admin.ticket.create', compact('cards'));
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
            'receivers' => 'required',
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
        unset($inputs['receivers']);
        unset($inputs['role']);
        $inputs['user_id'] = auth()->user()->id;
        $ticket = Ticket::create($inputs);
        $file_ticket = $request->urlfile;
        $temp = [];
        if ($file_ticket) {
            $files = $file_ticket;
            foreach ($files as $file) {
                if ($file) {
                    if (isset($file_ticket)) {
                        $url = Helper::moveImage($file, 'document');
                        $temp[] = $url;
                    }
                }
            }
            $ticket->update(['file' => $temp]);
        }
        foreach ($request->receivers as $receiver) {
            TicketUser::create([
                'ticket_id' => $ticket->id,
                'receiver_id' => $receiver,
                'sender_id' => auth()->user()->id,
            ]);
            if ($receiver != 'all') {
                event(new ticketNotification(User::find($receiver), $ticket));
            }
        }
        return redirect(route('ticket.index',['type' => 'send']))->with([
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
        $role = Role::where('name', 'presentore')->first();
        $presentores = $role->users->where('level', 'admin');
        $histories = $ticket->histories;

        return view('Admin.ticket.edit', compact('ticket', 'cards', 'histories', 'representations', 'centers', 'presentores'));
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
                        $url = Helper::moveImage($file, 'document');
                        $temp[] = $url;
                    }
                }
            }
            $inputs['file'] = $temp;
        }

        $ticket->update($inputs);

        return redirect(route('ticket.index'))->with([
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
        unset($inputs['receivers']);
        unset($inputs['role']);

        $inputs['user_id'] = auth()->user()->id;
        $ticket = Ticket::find($request->ticket_id);
        foreach ($request->receivers as $receiver) {
            TicketUser::create([
                'ticket_id' => $ticket->id,
                'receiver_id' => $receiver,
                'sender_id' => auth()->user()->id,
            ]);
            event(new ticketNotification(User::find($receiver), $ticket));
        }
        $inputs['ticket_id'] = $ticket->id;
        $inputs['user_id'] = auth()->user()->id;
        $inputs['type'] = 0;
        $ticket_history = TicketHistory::create($inputs);
        $ticket = $ticket_history->ticket;
        $ticket->update(['status' => 0]);

        return redirect()->back()->with([
            'create' => 'success'
        ]);
    }


    /**
     * @param Ticket $ticket
     * @return RedirectResponse
     */
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
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
