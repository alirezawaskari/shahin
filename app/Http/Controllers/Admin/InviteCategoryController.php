<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InviteCategory;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Request;

class InviteCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $invite_categories = InviteCategory::orderBy('id','DESC')->paginate(20);
        return view('Admin.inviteCategory.list',compact('invite_categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $services=Service::all();$categories=Category::all();
        return view('Admin.inviteCategory.create',compact('categories','services'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['percent' => 'required'], ['percent.required' => 'لطفا درصد را وارد کنید']);
        $invitecategoryable_ids=[];
        if($request->invitecategoryable == 'product'){ $type = Product::class ;
            if($request->product_ids != []){
                foreach ($request->product_ids as $product_id) {

                    InviteCategory::updateOrCreate(['invitecategoryable_id' => $product_id , 'invitecategoryable_type'=>$type],[
                        'percent'=>$request->percent]);

                }
            }
        }
        if($request->invitecategoryable == 'category'){ $type = Category::class ;
            if($request->category_ids != []){ $invitecategoryable_ids=$request->category_ids; }
        }
        if($request->invitecategoryable == 'service'){ $type = Service::class ;
            if($request->service_ids != []){ $invitecategoryable_ids=$request->service_ids; }
        }

        if($invitecategoryable_ids != []) {
            foreach ($invitecategoryable_ids as $id) {
                InviteCategory::updateOrCreate(['invitecategoryable_id' => $id , 'invitecategoryable_type'=>$type],[
                    'percent'=>$request->percent]);
            }
        }

        return redirect('/dashboard/inviteCategory')->with(['create' => 'success']);
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if ($id) {
            $invite_category = InviteCategory::find($id);
            return view('Admin.inviteCategory.edit', compact('invite_category'));
        }
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate(['percent' => 'required'], ['percent.required' => 'لطفا درصد را وارد کنید',]);

        $invite_category = InviteCategory::find($id);
        $invite_category->update($request->all());

        return redirect('/dashboard/inviteCategory')->with(['update' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    //use in InviteCategory
    public function searchTitleProduct(Request $request)
    {
        $view_result = '';
        if (\Auth::user()->isAbleTo('product-detail')) {
            $products = Product::select('id', 'name', 'image', 'root_category_id')->where('isShow', 1)->where('status', 5);
            $products = $products->where('name', 'like', '%' . $request->text . '%')
                ->orderBy('id', 'DESC')
                ->get();
        }
        if ($products != '') {
            $view = view('Admin.partials.inviteCategory.resultTitleProduct', ['products' => $products]);
            $view_result = $view->render();
        }
        return ['view_result' => $view_result];
    }

}
