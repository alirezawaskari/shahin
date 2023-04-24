<?php

namespace App\Http\Controllers\Admin;

use App\Models\Lalecard;
use Verta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;


class LalecardController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $lalecards = Lalecard::all();
        return view('Admin.lalecard.all', compact('lalecards'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        return view('Admin.lalecard.create');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $inputs = $request->all();
        $inputs['post_date'] = Helper::vertaToMiladi(Helper::numberConverter($inputs['post_date']));

        Lalecard::create($inputs);

        return redirect(route('lalecard.index'))->with([
            'create'=>'success'
        ]);
    }
}
