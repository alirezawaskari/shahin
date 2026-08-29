<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleCollection;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function index()
    {
        if (auth()->user()->hasPermission('role'))
        {
            $roles = Role::orderByDesc('id')->paginate(20);
            return view('Admin.role.all', compact('roles'));
        }
        return abort(401);
    }

    public function filter()
    {
        $roles = Role::filter()->latest();
        return new roleCollection($roles->paginate(20));
    }
    /**
     * @param Request $request
     * @return mixed
     */
    public function getUserByRole(Request $request)
    {
        $users=User::whereHas('roles',function ($q)use($request){
            $q->where('id',$request->id);
        })->select('id','name','family')->where('level','admin')->get();
        return response([
            'data' => array('users'=>$users),
        ]);
    }

    /**
     * @return Factory|View|Application|never|object
     */
    public function create()
    {
        if (auth()->user()->hasPermission('role'))
        {
            return view('Admin.role.create');
        }
        return abort(401);
    }


    /**
     * @param Request $request
     * @return Application|RedirectResponse|Redirector|object
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        abort_unless($request->user()?->hasPermission('role') === true, 403);

        $this->validate(request(), [
            'name' => 'required|string',
            'display_name' => 'required|string',
            'description' => 'required|string',
            'permission_id' => 'required',
            'category' => ['nullable',Rule::in(array_keys(Role::CATEGORIES))],
        ]);
        $permissions = $request->input('permission_id');
        $rol = new Role();
        $rol->name = $request->input('name');
        $rol->display_name = $request->input('display_name');
        $rol->description = $request->input('description');
        $rol->category = $request->input('category');
        $rol->save();
        $rol->syncPermissions($permissions);
        return redirect(route('role.index'))->with([
            'create' => 'success',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param Role $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        //
    }


    /**
     * @param Role $role
     * @return Factory|View|Application|never|object
     */
    public function edit(Role $role)
    {
        if (auth()->user()->hasPermission('role'))
        {
            return view('Admin.role.edit' , compact('role'));
        }
        return abort(401);
    }
    public function getRoles()
    {
        if(request()->ajax()){
            $role = Role::all();
            return new RoleCollection($role);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Role $role
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Role $role)
    {
        abort_unless($request->user()?->hasPermission('role') === true, 403);

        $this->validate(request(), [
            'name' => 'required|string',
            'display_name' => 'required|string',
            'description' => 'required|string',
            'permission_id' => 'required',
            'category' => ['nullable',Rule::in(array_keys(Role::CATEGORIES))],
        ]);

        $inputs = $request->all();
        $permissions = $request->input('permission_id');

        unset($inputs['permission_id']);
        $role->update($inputs);

        $role->syncPermissions($permissions);

        return redirect(route('role.index'))->with([
            'update' => 'success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Role $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        //
    }
}
