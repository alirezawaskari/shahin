<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Models\TicketHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TicketHistoryController extends Controller
{
    public function store(Request $request)
    {
        $this->validate(request(), [
            'content' => 'required|string',
        ]);
        $inputs = $request->all();
        $inputs['user_id'] = auth()->user()->id;
        $inputs['type'] = 1;
        // legacy column: never written. Spatie media is the source of truth and
        // TicketHistory::fileUrls() unions the column back in for display.
        unset($inputs['urlfile'], $inputs['file']);

        $ticket_history = TicketHistory::create($inputs);

        foreach ((array) $request->urlfile as $file) {
            if ($file) {
                MediaLibraryHelper::attachTo($ticket_history, (string) $file, 'file');
            }
        }

        $ticket = $ticket_history->ticket;

        // fires `saved`, which adopts the TICKET's own pending legacy files
        $ticket->update([
            'status' => $ticket->status ? 0 : 1,
        ]);

        // Existing histories are never re-saved, so nothing would ever fire their `saved`
        // event and their legacy `file` paths could never migrate. Replying to the ticket
        // is the save that owns this screen, so adopt them here too.
        foreach ($ticket->histories as $history) {
            $history->adoptLegacyMedia();
        }

        return redirect()->back()->with([
            'create' => 'success'
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
