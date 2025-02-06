<?php

namespace App\Http\Controllers\Admin\site;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\Category;
use Illuminate\Http\Request;

class AttributeGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $attribute_groups = AttributeGroup::orderBy('id', 'DESC');
        $queries=[];
        $columns=['category_id'];
        foreach ($columns as $column) {
            if( $request->has($column)){
                if($request->$column != 'all'){
                    $attribute_groups = $attribute_groups->where($column,$request->$column);
                    $queries[$column]=$request->$column;
                }else{
                    $queries[$column]='all';
                }
            }
        }

        $attribute_groups = $attribute_groups->paginate(15)->appends($queries);
        $last_categories = Category::filter()->lastCategory();
        return view('Admin.site.attributeGroup.list',compact('attribute_groups',
            'last_categories','queries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $parents = Category::where('parent_id', 0)->get();
        return view('Admin.site.attributeGroup.create', ['parents' => $parents]);
    }

    /**
     * @param $category_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function createByCategory($category_id)
    {
        $category = Category::where('id',$category_id)->select('id','title')->first();
        return view('Admin.site..attributeGroup.create', compact('category'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['title' => 'required', 'choose_category_id' => 'required'],
            [
                'title.required' => 'لطفا عنوان را وارد کنید',
                'choose_category_id.required' => 'لطفا گروه کالا را انتخاب کنید'
            ]
        );

        $attribute_group = new AttributeGroup();
        $attribute_group->title = $request->input('title');
        $attribute_group->category_id = $request->input('choose_category_id');
        $attribute_group->save();


        return redirect('/dashboard/attributeGroup?category_id='.$attribute_group->category_id)->with([
            'create' => 'success'
        ]);

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
            $parents = Category::where('parent_id', 0)->get();
            $attribute_group = AttributeGroup::find($id);
            return view('Admin.site.attributeGroup.edit', ['attribute_group' => $attribute_group, 'parents' => $parents]);
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
        $request->validate(['title' => 'required'],
            ['title.required' => 'لطفا نام دسته بندی را وارد کنید']
        );

        $attribute_group = AttributeGroup::find($id);
        if(isset($request->choose_category_id))$attribute_group->category_id = $request->choose_category_id;
        $attribute_group->title = $request->input('title');
        $attribute_group->save();

        if(isset($request->choose_category_id) && $request->choose_category_id != null){
            if($attribute_group->attributes){
                foreach ($attribute_group->attributes as $attribute) {
                    $attribute->category_id=$attribute_group->category_id;
                    $attribute->save();
                }
            }
        }

        return redirect()
            ->route('attributeGroup.index',['category_id'=>$attribute_group->category_id])
            ->with(['update' => 'success']);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if($id){
            $attribute_group=AttributeGroup::find($id);
            if($attribute_group)
            {
                foreach ($attribute_group->attributes()->get() as $attribute) {
                    $attribute->attributeItems()->delete();
                    $attribute->delete();
               }
               $attribute_group->delete();
                return redirect('/dashboard/attributeGroup?category_id='.$attribute_group->category_id)->with([
                    'delete' => 'success'
                ]);
            }
        }
        return redirect()->back();
    }



}
