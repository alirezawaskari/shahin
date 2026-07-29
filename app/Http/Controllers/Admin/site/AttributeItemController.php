<?php

namespace App\Http\Controllers\Admin\site;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeItem;
use App\Models\AttributeItemColor;
use App\Models\Color;
use Illuminate\Http\Request;

class AttributeItemController extends Controller
{


    /**
     * @param $attribute_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function createAttributeItem($attribute_id)
    {
        $colors = Color::get();
        return view('Admin.site.attributeItem.create',
            ['attribute_id' => $attribute_id, 'colors' => $colors]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['title' => 'required'],['title.required' => 'لطفا عنوان را وارد کنید']);
        if ($request->attribute_type == 'color') {
            foreach ($request->color_id as $item) {
                $attribute_item = new AttributeItem();
                $attribute_item->attribute_id = $request->attribute_id;
                $attribute_item->title = Color::find($item)->title;
                $attribute_item->save();

                AttributeItemColor::create(['attribute_item_id' => $attribute_item->id,
                    'color_id' => $item]);
            }
        } else {
            foreach ($request->item as $item) {
                if ($item != '') {
                    $attribute_item = new AttributeItem();
                    $attribute_item->attribute_id = $request->attribute_id;
                    $attribute_item->title = $item['title'];
                    $attribute_item->save();
                }
            }
        }

        return redirect()
            ->route('attributeItem.show',[$request->attribute_id])
            ->with(['create' => 'success']);

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $attribute = Attribute::find($id);
        $attribute_items = AttributeItem::where('attribute_id', $id)->paginate(15);
        return view('Admin.site.attributeItem.list', ['attribute_items' => $attribute_items,'attribute'=>$attribute]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if ($id) {
            $attribute_item = AttributeItem::find($id);
            $attribute_item_colors = AttributeItemColor::where('attribute_item_id', $id)->first();
            return view('Admin.site.attributeItem.edit', ['attribute_item' => $attribute_item, 'attribute_item_colors' => $attribute_item_colors]);
        }
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate(['title' => 'required'],['title.required' => 'لطفا عنوان را وارد کنید']);

        $request['status'] = isset($request->status) ? 1 : 0;

        $attribute_item = AttributeItem::find($id);
        $attribute_item->update($request->all());
        $attribute=Attribute::find($attribute_item->attribute_id);
        if($attribute->type == 'color') {
            $attribute_item_color = AttributeItemColor::where('attribute_item_id', $id)->first();
            if (isset($attribute_item_color) && $attribute_item_color != null) {
                $attribute_item_color->color_palette = $request->color_palette;
                $attribute_item_color->save();
            }
            else {
                AttributeItemColor::create(['attribute_item_id' => $attribute_item->id,
                    'color_palette' => $request->color_palette]);
            }
        }

        return redirect()->back()->with(['update' => 'success']);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($id) {
            $attribute_item = AttributeItem::find($id);
            $attribute_id = $attribute_item->attribute_id;
            if ($attribute_item) {
                $attribute_item->delete();
                return redirect()->back()
                    ->with(['delete' => 'success']);
            }
        }
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return int[]|string[]
     */
    public function addRequestAttributeItem(Request $request)
    {
        if ($request->attribute_item_title != null && $request->attribute_id) {

            $attribute = Attribute::find($request->attribute_id);

            if ($attribute->type == 'color' && isset($request->color_palette) && $request->color_palette != null) {
                $has_attribute_item = AttributeItem::where('attribute_id',$request->attribute_id)
                    ->where('title',$request->attribute_item_title)->count();
                if($has_attribute_item == 0){

                    $color = Color::create(['title' => $request->attribute_item_title,
                        'color_palette' => $request->color_palette, 'status' => 0]);

                    $attribute_item = AttributeItem::create(['attribute_id' => $request->attribute_id,
                        'title' => $request->attribute_item_title, 'status' => 0]);

                    AttributeItemColor::create(['attribute_item_id' => $attribute_item->id, 'color_id' => $color->id]);
                }
                else {
                    return ['status' => 301];
                }
            }
            else {
                $has_attribute_item = AttributeItem::where('attribute_id',$request->attribute_id)
                    ->where('title',$request->attribute_item_title)->count();
                if($has_attribute_item == 0){
                    AttributeItem::create(['attribute_id' => $request->attribute_id,
                        'title' => $request->attribute_item_title, 'status' => 0]);
                }else{
                    return ['status' => 301];
                }
            }

            return ['status' => 'true'];
        }
        return ['status' => 'false'];
    }

    //Requested Attribute Items

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function suggest()
    {
        $attribute_items = AttributeItem::where('status',0)->paginate(25);
        foreach ($attribute_items as $attribute_item){
            $attribute = $attribute_item->attribute;
            $attribute_item['attribute_name']=$attribute->title;
            $attribute_item['category_title']=$attribute->category->title;
        }
        return view('Admin.site.attributeItem.suggest',compact('attribute_items'));
    }

}
