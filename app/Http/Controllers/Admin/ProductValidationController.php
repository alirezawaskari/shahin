<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductValidation;
use Illuminate\Http\Request;

class ProductValidationController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(isset($request->product_id) && isset($request->validation_id) && isset($request->field) &&
            $request->product_id != null && $request->validation_id != null && $request->field != null) {

            $same_data=ProductValidation::where('product_id',$request->product_id)
                ->where('field',$request->field)->count();

            $product = Product::find($request->product_id);

            if($same_data == 0){
                ProductValidation::create($request->all());
            }
            else{
                $product_validation = ProductValidation::where('product_id',$request->product_id)
                    ->where('field',$request->field)->first();
                $product_validation->validation_id = $request->validation_id;
                if(isset($request->productvalidationable_id))
                $product_validation->productvalidationable_id = $request->productvalidationable_id;
                if(isset($request->productvalidationable_type))
                $product_validation->productvalidationable_type = $request->productvalidationable_type;
                $product_validation->save();
            }

            if($product->status == 3){
                $product->status = 2 ; $product->save();
            }
            else if($product->status == 4){
                $product->status = 6 ; $product->save();
            }
            else{
                $product->status = 2 ; $product->save();
            }
            $product->message = 'برای مشاهده دلایل رد کالا روی "ویرایش محصول" کلیک کنید.';
            $product->save();

            return response(['status'=>'true']);

        }
        return response(['status'=>'false']);
    }

    public function getValidation(Request $request)
    {
        if(isset($request->product_id) && isset($request->field) &&
            $request->product_id != null && $request->field != null) {

            $same_data = ProductValidation::where('product_id',$request->product_id)
                ->where('field',$request->field)->first();

            if($same_data){
                return response(['validation_id'=>$same_data->validation_id,'status'=>'true']);
            }else{
                return response(['validation_id'=>null,'status'=>'true']);
            }

        }
        return response(['status'=>'false']);
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

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }
}
