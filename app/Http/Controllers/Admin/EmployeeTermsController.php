<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Context;
use App\Models\Role;
use Illuminate\Http\Request;

class EmployeeTermsController extends Controller
{
    const PREFIX_TERMS_KEY_IN_CONTEXT_TABLE = 'employee_terms_';
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $role_categories = Role::CATEGORIES;

        $role_categories_with_terms= array();
        foreach ($role_categories as $key_role => $value_role){
            $role_categories_with_terms[$key_role]['role_category_title']= $key_role;
            $role_categories_with_terms[$key_role]['role_category_fa_title']= $value_role;
            $context  = Context::fetchByKey(self::PREFIX_TERMS_KEY_IN_CONTEXT_TABLE.$key_role);
            $role_categories_with_terms[$key_role]['role_category_terms_file_path']= $context->value ?? null;
        }

        return view('Admin.rules.employeeTermsIndex',compact('role_categories_with_terms'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
       $terms_context= Context::fetchByKey(self::PREFIX_TERMS_KEY_IN_CONTEXT_TABLE.$request->role_category);
       if (!is_null($terms_context)){
           Helper::deleteImage(public_path($terms_context->value));
           $file_path= Helper::uploadFile($request->terms_file,'employee_terms');
           $terms_context->update([
               'value'=>$file_path
           ]);
       }else{
          $file_path= Helper::uploadFile($request->terms_file,'employee_terms');
           Context::create([
               'key'=>self::PREFIX_TERMS_KEY_IN_CONTEXT_TABLE.$request->role_category,
               'value'=>$file_path
           ]);
       }
       return back();
    }


}
