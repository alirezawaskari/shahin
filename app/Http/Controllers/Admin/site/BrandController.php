<?php

namespace App\Http\Controllers\Admin\site;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BrandCollection;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $last_categories = Category::lastCategory();
        return view('Admin.site.brand.list', compact('last_categories'));
    }

    /**
     * filter brands
     * @param Request $request
     * @return Request
     */
    public function filter(Request $request)
    {
        $brands=Brand::query();
            if (isset($request->category_id) && $request->category_id!= "all"){
                $brands=Brand::whereHas('categories', function ($query) use ($request)  {
                    $query->where('id',$request->category_id);
                })->where('status',$request->status);
            }
            if (isset($request->status) && $request->status!= "all"){
                $brands=$brands->where('status',$request->status);
            }
        return new BrandCollection($brands->paginate(20));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.site.brand.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->type == 0) $request['link'] = $request->link_zero;
        if($request->type == 1) $request['link'] = $request->link_one;

        //Brand request
        if($request->request_by == 1) {$request['request_by'] = \Auth::user()->id;$request['status']=0;}

        $same_brands = Brand::where('title',$request->title)->get();
        $count=0;
        foreach ($same_brands as $same_brand) {
            $count=$same_brand->categories()->where('category_id',$request->category_id)->count();
        }
        if($count == 0){
            $brand = Brand::create($request->all());

            if($request->type == 0){
                $registration_form_path = $request->registration_form_path;
                if(isset($registration_form_path)) {
                    if (\File::exists(public_path($brand->registration_form))) \File::delete(public_path($brand->registration_form));
                    $brand->registration_form = Helper::moveImage($registration_form_path,'brand');
                    $brand->save();
                }
            }

            $logo_path = $request->logo_path;
            if(isset($logo_path)) {
                if (\File::exists(public_path($brand->logo))) \File::delete(public_path($brand->logo));
                $brand->logo = Helper::moveImage($logo_path,'brand');
                $brand->save();
            }

            if(isset($request->category_id)){
                $count_cat = $brand->categories()->where('category_id',$request->category_id)->count();
                if($count_cat == 0)$brand->categories()->attach($request->category_id);
            }

            return response(['status'=>'true','message'=>'برند با موفقیت ثبت شد']);
        }
        return response(['status'=>'warning','message'=>'برند با این عنوان فارسی وجود دارد']);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $brands = Category::find($id)->brands()->where('status',1)->paginate(15);

        return view('Admin.site.brand.list', compact('brands','id'));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function createByCatId($id)
    {
        $category=Category::where('id',$id)->select('id','title')->first();
        return view('Admin.site.brand.create',['category'=>$category]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        if ($id) {
            $brand = Brand::find($id);
            return view('Admin.site.brand.edit', ['brand' => $brand]);
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
        $request->validate(['title' => 'required'], ['title.required' => 'لطفا عنوان را وارد کنید',]);

        $brand= Brand::find($id);
        if($request->type == 0) $request['link'] = $request->link_zero;
        if($request->type == 1) $request['link'] = $request->link_one;

        $brand->update($request->all());

        if($request->type == 0){
            $registration_form_path = $request->registration_form_path;
            if(isset($registration_form_path)) {
                if (\File::exists(public_path($brand->registration_form))) \File::delete(public_path($brand->registration_form));
                $brand->registration_form = Helper::moveImage($registration_form_path,'brand');
                $brand->save();
            }
        }

        $logo_path = $request->logo_path;
        if(isset($logo_path)) {
            if (\File::exists(public_path($brand->logo))) \File::delete(public_path($brand->logo));
            $brand->logo = Helper::moveImage($logo_path,'brand');
            $brand->save();
        }

        return redirect()->back()->with([
            'update' => 'success'
        ]);

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
            $brand=Brand::find($id);
            if($brand) {
                foreach ($brand->categories()->get() as $item) {
                    $item->pivot->delete();
                }
                $brand->delete();
                return response([
                    'operator' => true,
                    'status'=>'success',
                    'message'=>'حذف با موفقیت انجام گردید .'
                ]);
            }
        return response([
            'operator' => true,
            'status'=>'error',
            'message'=>'برند انتخاب شده صحیح نمی باشد !'
        ]);

    }


//    public function statusBrand($id,$status)
//    {
//       $brand = Brand::find($id);
//       if($brand){
//           $brand->status = $status;
//           $brand->save();
//       }
//       return redirect()->back();
//    }

    /**
     * change status of brand
     * @param Brand $brand
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function statusBrand(Brand $brand)
    {
         $brand->changeStatus();
         return  response([
             'operator' => true,
             'status'=>'success',
             'message'=>'عملیات با موفقیت انجام گردید .'
         ]);
    }


}
