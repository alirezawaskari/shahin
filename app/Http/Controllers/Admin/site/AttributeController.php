<?php

namespace App\Http\Controllers\Admin\site;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function createAttribute($id)
    {
        return view('Admin.site.attribute.create', ['attribute_group_id'=>$id]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['title' => 'required','type' => 'required'],
        [
            'title.required' => 'لطفا عنوان را وارد کنید',
            'type.required' => 'لطفا نوع را وارد کنید',
        ]);
        $request['isFilter'] = isset($request['isFilter']) ? 1 : 0;
        $request['is_price'] = isset($request['is_price']) ? 1 : 0;
        $request['is_show'] = isset($request['is_show']) ? 1 : 0;

        $attribute_group = AttributeGroup::find($request['attribute_group_id']);
        if($attribute_group){
            $request['category_id'] = $attribute_group->category_id ;
            $has_rec_is_price=Attribute::where('is_price',1)->where('category_id',$attribute_group->category_id)->count();
        }

        if($request['is_price'] == 1){
            if($has_rec_is_price == 0){
               Attribute::create($request->all());

                return redirect()
                    ->route('attribute.show',[$attribute_group->id])
                    ->with([
                    'create' => 'success'
                ]);

            }else{
                return redirect()
                    ->route('attribute.show',[$attribute_group->id])
                    ->with(['failByMessage' => 'امکان ثبت مجدد ویژگی با قابلیت قیمت گذاری وجود ندارد']);
            }
        }
        else{
             Attribute::create($request->all());

            return redirect()
                ->route('attribute.show',[$attribute_group->id])
                ->with([
                'create' => 'success'
            ]);

        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //show attribute lists in category list by category id
    /**
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function show($id)
    {
        $attribute_group = AttributeGroup::find($id);
        $attributes = Attribute::where('attribute_group_id',$attribute_group->id)->paginate(15);
        return view('Admin.site.attribute.list',compact('attribute_group','attributes'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        if ($id) {
            $attribute = Attribute::find($id);
            return view('Admin.site.attribute.edit', ['attribute' => $attribute]);
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
    public function update(Request $request, $id){
        $request->validate(['title' => 'required','type' => 'required'],
            ['title.required' => 'لطفا نام دسته بندی را وارد کنید',
                'type.required' => 'لطفا نوع را وارد کنید',]
        );

        $request['isFilter'] = isset($request['isFilter']) ? $request['isFilter'] : 0;
        $request['is_price'] = isset($request['is_price']) ? $request['is_price'] : 0;
        $request['is_show'] = isset($request['is_show']) ? $request['is_show'] : 0;
        $attribute = Attribute::find($id);
        $attribute->update($request->all());

        return redirect()
            ->route('attribute.show',[$attribute->attribute_group_id])
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
            $attribute=Attribute::find($id);
            $attribute_group_id = $attribute->attribute_group_id;
            if($attribute) {
                $attribute->attributeItems()->delete();
                $attribute->delete();
                return redirect()
                    ->route('attribute.show',[$attribute_group_id])
                    ->with(['delete' => 'success']);
            }
        }
        return redirect()->back();
    }


}
