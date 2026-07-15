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
        unset($inputs['urlfile']);
        $inputs['file'] = [];
        $file_ticket = $request->urlfile;

        $ticket_history = TicketHistory::create($inputs);

        $temp = [];
        if ($file_ticket) {
            $files = $file_ticket;
            foreach ($files as $file) {
                if ($file) {
                    $media = MediaLibraryHelper::attachTo($ticket_history, (string) $file, 'file');
                    if ($media) {
                        $temp[] = $media->getUrl();
                    }
                }
            }
            $ticket_history->update(['file' => $temp]);
        }

        $ticket = $ticket_history->ticket;

        $ticket->update([
            'status' => $ticket->status ? 0 : 1,
        ]);

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
