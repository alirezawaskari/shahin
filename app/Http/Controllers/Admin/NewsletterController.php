<?php

namespace App\Http\Controllers\Admin;

use App\Exports\NewsletterExport;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NewsletterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $newsletters = Newsletter::latest()->get();
        return view('Admin.newsletter.all' , compact('newsletters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.newsletter.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        Newsletter::create($request->all());

        return redirect(route('newsletter.index'))->with([
            'create'=>'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function show(Newsletter $newsletter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function edit(Newsletter $newsletter)
    {
        return view('Admin.newsletter.edit' , compact('newsletter'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Newsletter $newsletter)
    {
        $newsletter->update($request->all());

        return redirect(route('newsletter.index'))->with([
            'update'=>'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Newsletter $newsletter
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Newsletter $newsletter)
    {
        $newsletter->delete();
        return redirect()->back()->with([
            'delete' => 'success'
        ]);
    }

    public function exel()
    {
        $emails = Newsletter::latest()->get();
        return (new NewsletterExport($emails))->download('emails.xlsx');
    }
}
