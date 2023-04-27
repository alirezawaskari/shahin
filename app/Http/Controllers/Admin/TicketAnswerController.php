<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Models\TicketAnswer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class TicketAnswerController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'content' => 'required|string',
        ]);
        $inputs = $request->all();
        $inputs['user_id'] = auth()->user()->id;
        unset($inputs['urlfile']);
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
			$inputs['file'] = $temp;
        }

        $ticket_answer = TicketAnswer::create($inputs);
        $ticket = $ticket_answer->ticket;

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
