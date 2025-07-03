<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\roleCollection;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{

    public function index()
    {
        if (auth()->user()->hasPermission('role'))
        {
            $roles = Role::latest()->get();
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
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
     * @param  \App\Models\Role $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Role $role
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        if (auth()->user()->hasPermission('role'))
        {
            return view('Admin.role.edit' , compact('role'));
        }
        return abort(401);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Role $role
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Role $role)
    {
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
     * @param  \App\Models\Role $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        //
    }
}
