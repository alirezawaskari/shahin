<?php

namespace App\Http\Controllers\Admin;

use App\Notifications\UserNotification;
use App\Models\Notify;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Notification;

class NotifyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notifications = Notify::latest()->paginate(20);
        return view('Admin.notification.all' , compact('notifications'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.notification.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request , [
           'title' => 'required',
            'message' => 'required',
            'user' => 'required'
        ]);

        $notify = Notify::create([
           'title' => $request->input('title'),
           'message' => nl2br($request->input('message')),
           'user' => $request->input('user') == 'all' ? 'all' : $request->input('user'),
        ]);
        $data = [
            'title' => $notify->title,
            'message' => $notify->message,
        ];

        if ($request->input('user') == 'all'){
            Notification::send(User::all(), new UserNotification($data));
        }else{
            $user = User::find($request->input('user'));
            $user->notify(new UserNotification($data));
        }

        return redirect(route('notification.index'))->with([
           'notification_create' =>  'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Notify  $notify
     * @return \Illuminate\Http\Response
     */
    public function show(Notify $notify)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Notify  $notify
     * @return \Illuminate\Http\Response
     */
    public function edit(Notify $notify)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Notify  $notify
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Notify $notify)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Notify  $notify
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notify $notify)
    {
        //
    }
}
