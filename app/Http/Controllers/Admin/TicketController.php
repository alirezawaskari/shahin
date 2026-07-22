<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\TicketCollection;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketUser;
use App\Models\User;
use App\Events\ticketNotification;
use App\Helpers\MediaLibraryHelper;
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
        unset($inputs['file']); // legacy column: never written, see update()
        $ticket = Ticket::create($inputs);
        foreach ((array) $request->urlfile as $file) {
            if ($file) {
                MediaLibraryHelper::attachTo($ticket, (string) $file, 'file');
            }
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

        // Never write the legacy `file` column. It used to be overwritten with ONLY this
        // request's uploads, which dropped every previously attached file and every
        // un-adopted legacy path in a single write — and since attachTo() ran first the
        // collection was already non-empty, so adoptLegacyMedia() skipped it and those
        // legacy paths were erased without ever being migrated.
        // Spatie media is the source of truth; Ticket::fileUrls() unions the column back
        // in for display until the pending paths are adopted.
        unset($inputs['urlfile'], $inputs['file']);

        // Adopt BEFORE attaching: adoptLegacyMedia() skips a collection that already has
        // media, so attaching first would leave pending legacy files unmigrated.
        $ticket->adoptLegacyMedia();

        foreach ((array) $request->urlfile as $file) {
            if ($file) {
                MediaLibraryHelper::attachTo($ticket, (string) $file, 'file');
            }
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
        // `file` here is the raw UploadedFile array from $request->all() — writing it into
        // the legacy column stored garbage, and urlfile (the temp media ids the ajax widget
        // actually submits) was never attached at all, so files uploaded on the ارجاع tab
        // were silently lost. Attach them to the history instead.
        unset($inputs['file'], $inputs['urlfile']);
        $ticket_history = TicketHistory::create($inputs);
        foreach ((array) $request->urlfile as $file) {
            if ($file) {
                MediaLibraryHelper::attachTo($ticket_history, (string) $file, 'file');
            }
        }
        $ticket = $ticket_history->ticket;
        // fires `saved`, which adopts the TICKET's own pending legacy files
        $ticket->update(['status' => 0]);

        // @see TicketHistoryController::store() — existing histories are never re-saved,
        // so their legacy `file` paths can only be adopted from here.
        foreach ($ticket->histories as $history) {
            $history->adoptLegacyMedia();
        }

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
