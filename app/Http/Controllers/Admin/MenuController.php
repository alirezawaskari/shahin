<?php

namespace App\Http\Controllers\Admin;

use App\Menu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->user()->hasPermission('menu')) {
            $menus = Menu::orderBy('sort', 'asc')->get();
            return view('Admin.menu.all', compact('menus'));
        }
        return abort(401);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (auth()->user()->hasPermission('menu')) {
            $menus = Menu::where('parent_id', 0)->get();
            return view('Admin.menu.create', compact('menus'));
        }
        return abort(401);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'title' => 'required',
            'parent' => 'required',
        ]);
        $menu = Menu::create([
            'title' => $request->title,
            'link' => $request->link,
            'parent_id' => $request->parent,
        ]);
        $menu->update(['sort' => $menu->id]);

        //cache
        Cache::flush();

        return redirect(route('menu.index'))->with([
            'create' => 'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Menu  $menu
     * @return \Illuminate\Http\Response
     */
    public function show(Menu $menu)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Menu  $menu
     * @return \Illuminate\Http\Response
     */
    public function edit(Menu $menu)
    {
        if (auth()->user()->hasPermission('menu')) {
            $menus = Menu::where('parent_id', 0)->get();
            return view('Admin.menu.edit', compact('menus', 'menu'));
        }
        return abort(401);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Menu $menu
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Menu $menu)
    {
        $this->validate(request(), [
            'title' => 'required',
            'parent' => 'required',
        ]);

        $inputs = $request->all();
        $menu->update(['parent_id' => $request->input('parent')]);
        unset($inputs['parent']);
        $menu->update($inputs);

        //cache
        Cache::flush();

        return redirect(route('menu.index'))->with([
            'update' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Menu $menu
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Menu $menu)
    {
        if (auth()->user()->hasPermission('menu')) {
            if ($menu->getChild()->get()->count() > 0) {
                foreach ($menu->getChild()->get() as $midmenu) {
                    if ($midmenu->getChild()->get()->count() > 0) {
                        foreach ($midmenu->getChild()->get() as $minmenu) {
                            $minmenu->delete();
                        }
                        $midmenu->delete();
                    } else {
                        $midmenu->delete();
                    }
                }
                $menu->delete();
            } else {
                $menu->delete();
            }

            //cache
            Cache::flush();

            return redirect()->back()->with([
                'delete' => 'success'
            ]);
        }
        return abort(401);

    }


    /**
     * @param Request $request
     */
    public function sort(Request $request)
    {
        if ($request->ajax()) {
            $id = $request->input('id');
            $sort = $request->input('sort');
            Menu::find($id)->update(['sort' => $sort]);

            //cache
            Cache::flush();
        }
    }
}
