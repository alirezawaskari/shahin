<?php

namespace App\Http\Controllers\Admin;

use App\Models\Help;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class HelpController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $help = Help::first();
        return view('Admin.help.edit', compact('help'));
    }

    /**
     * @param Request $request
     * @param Help $help
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Help $help)
    {
        $this->validate(request(), [
            'title' => 'required',
            'content' => 'required',
        ]);

        $help->update($request->all());

        //cache
        Cache::flush();

        return redirect()->back()->with([
            'setting_update'=>'success'
        ]);
    }

}
