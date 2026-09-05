<?php

namespace App\Http\Controllers\Admin\site;

use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BrandCollection;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Summary of index
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $last_categories = Category::filter()->get();
        return view('Admin.site.brand.list', compact('last_categories'));
    }

    /**
     * filter brands
     * @param Request $request
     * @return Request
     */
    public function filter(Request $request)
    {
        $brands=Brand::filter();
        return new BrandCollection($brands->paginate(20));
    }

    /**
     * Summary of create
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $last_categories = Category::filter()->get();
        return view('Admin.site.brand.create' , compact('last_categories') );
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

        // logo is always required; the registration form only for Iranian brands (type 0)
        if (empty($request->logo_path)) {
            return response(['status' => 'warning', 'message' => 'لطفا لوگوی برند را بارگذاری کنید']);
        }

        $same_brands = Brand::where('title',$request->title)->get();
        $count=0;
        foreach ($same_brands as $same_brand) {
            $count=$same_brand->categories()->where('category_id',$request->category_id)->count();
        }
        if($count == 0){
            $brand = Brand::create(collect($request->all())
                ->except(['logo', 'registration_form', 'logo_path', 'registration_form_path'])->all()); // see update()
            if($request->type == 0){
                MediaLibraryHelper::attachTo($brand, $request->registration_form_path, 'registration_form');
            }
            MediaLibraryHelper::attachTo($brand, $request->logo_path, 'logo');

            if(isset($request->category_id)){
                $count_cat = $brand->categories()->where('category_id',$request->category_id)->count();
                if($count_cat == 0)$brand->categories()->attach($request->category_id);
            }

            return response(['status'=>'true','message'=>'برند با موفقیت ثبت شد']);
        }
        return response(['status'=>'warning','message'=>'برند با این عنوان فارسی وجود دارد']);

    }

    /**
     * Summary of show
     * @param mixed $id
     * @return \Illuminate\Contracts\View\View
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
     * Summary of edit
     * @param mixed $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id){
        if ($id) {
            $brand = Brand::find($id);
            return view('Admin.site.brand.edit', ['brand' => $brand]);
        }
        return redirect()->back();
    }

    /**
     * Summary of update
     * @param Request $request
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id){
        $request->validate(['title' => 'required'], ['title.required' => 'لطفا عنوان را وارد کنید',]);

        $brand= Brand::find($id);
        if($request->type == 0) $request['link'] = $request->link_zero;
        if($request->type == 1) $request['link'] = $request->link_one;

        // Logo required (keep existing or upload new); registration form for Iranian brands
        // (type 0). Checked via the ACCESSOR, not getFirstMedia(): a not-yet-migrated brand
        // has its image in the legacy column with no Spatie media, so getFirstMedia() was
        // empty and the save was rejected with "upload a logo" — making it impossible to
        // save (and therefore to migrate) any legacy brand.
        if (! $brand->logo && empty($request->logo_path)) {
            return back()->withErrors(['logo' => 'لطفا لوگوی برند را بارگذاری کنید'])->withInput();
        }

        // Never mass-assign the legacy columns: `logo` and `registration_form` are BOTH
        // fillable columns and the file inputs' names, so a failed ajax upload would write
        // a raw UploadedFile straight into them.
        $brand->update(collect($request->all())
            ->except(['logo', 'registration_form', 'logo_path', 'registration_form_path'])->all());

        if($request->type == 0){
            MediaLibraryHelper::attachTo($brand, $request->registration_form_path, 'registration_form');
        }
        MediaLibraryHelper::attachTo($brand, $request->logo_path, 'logo');

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
