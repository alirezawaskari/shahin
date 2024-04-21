<?php

namespace App\Http\Controllers\Admin;

use App\Models\SuspensionStandard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class SuspensionStandardController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        if (auth()->user()->hasPermission('suspensionStandard_list'))
        {
            $suspensionStandards = SuspensionStandard::all();
            return view('Admin.suspensionStandard.all', compact('suspensionStandards'));
        }
        return abort(401);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        if (auth()->user()->hasPermission('suspensionStandard_change'))
        {
            return view('Admin.suspensionStandard.create');
        }
        return abort(401);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required',
            'waitting_time' => 'required',
        ]);
        SuspensionStandard::create($request->all());
        //cache
        Cache::flush();

        return redirect(route('suspensionStandard.index'))->with([
            'create'=>'success'
        ]);
    }

    /**
     * @param SuspensionStandard $suspensionStandard
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(SuspensionStandard $suspensionStandard)
    {
        if (auth()->user()->hasPermission('suspensionStandard_change'))
        {
            return view('Admin.suspensionStandard.edit', compact('suspensionStandard'));
        }
        return abort(401);
    }

    /**
     * @param Request $request
     * @param SuspensionStandard $suspensionStandard
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, SuspensionStandard $suspensionStandard)
    {
        $this->validate(request(), [
            'name' => 'required',
            'waitting_time' => 'required',
        ]);

        $inputs = $request->all();
        $suspensionStandard->update($inputs);

        //cache
        Cache::flush();

        return redirect(route('suspensionStandard.index'))->with([
            'update' => 'success'
        ]);
    }
}
