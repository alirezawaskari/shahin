<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends AdminController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function child(Request $request)
    {

        if ($request->ajax()) {
            if (isset($request->id) && is_numeric($request->id)) {
                $id = $request->input('id');
                $category_childes = Category::where('parent_id', $id)->get();
                return response()->json(['count' => 'available', 'category_childs' => $category_childes,'category_child_count'=>$category_childes->count()]);
            }
        }
    }
}

