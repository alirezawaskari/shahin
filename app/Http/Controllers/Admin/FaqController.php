<?php

namespace App\Http\Controllers\Admin;

use App\Models\Faq;
use function GuzzleHttp\Promise\all;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $faqs = Faq::all();
        return view('Admin.faq.all', compact('faqs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.faq.create');
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
           'title' => 'required|string',
        ]);

        Faq::create($request->all());

        return redirect(route('faq.index'))->with([
            'faq_create'=>'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function show(Faq $faq)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function edit(Faq $faq)
    {
        return view('Admin.faq.edit' , compact('faq'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Faq $faq
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Faq $faq)
    {
        $this->validate($request , [
            'title' => 'required|string',
        ]);

        $faq->update($request->all());

        return redirect(route('faq.index'))->with([
            'faq_update'=>'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Faq $faq
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect(route('faq.index'))->with([
            'faq_delete'=>'success'
        ]);
    }
}
