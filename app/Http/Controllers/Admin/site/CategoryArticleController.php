<?php

namespace App\Http\Controllers\Admin\site;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryArticleCollection;
use App\Models\Category;
use App\Models\Service;
use App\Models\CategoryArticle;
use Illuminate\Http\Request;

class CategoryArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('Admin.site.categoryArticle.list');
    }

    /**
     * @param Request $request
     * @return string
     */
    public function filter(Request $request)
    {
        $categoryArticles = CategoryArticle::with('similarGroupsCategories','similarGroupsServices');
        return new CategoryArticleCollection($categoryArticles->paginate(15));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $all_categories = Category::select('id','title')->get();
        $all_services = Service::select('id','title')->get();
        return view('Admin.site.categoryArticle.create',['all_categories'=>$all_categories,'all_services'=>$all_services]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['title' => 'required'],['title.required' => 'لطفا عنوان را وارد کنید']);
        $categoryArticle=CategoryArticle::create($request->all());

        if (isset($request->similar_categories)){
            $categoryArticle->similarGroupsCategories()->attach($request->similar_categories,['similar_model'=>CategoryArticle::class,'relation_model'=>Category::class]);
        }
        if (isset($request->similar_services)){
            $categoryArticle->similarGroupsServices()->attach($request->similar_services,['similar_model'=>CategoryArticle::class,'relation_model'=>Service::class]);
        }
        return redirect('/dashboard/site/categoryArticle')->with([
            'create' => 'success'
        ]);
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
            $category_article = CategoryArticle::find($id);
            $all_categories = Category::select('id','title')->get();
            $all_services = Service::select('id','title')->get();
            $similar_categories=$category_article->similarGroupsCategories()->where('similar_model',CategoryArticle::class)->where('relation_model',Category::class)->pluck('relation_id')->toArray();
            $similar_services=$category_article->similarGroupsServices()->where('similar_model' ,CategoryArticle::class)->where('relation_model',Service::class)->pluck('relation_id')->toArray();

            return view('Admin.site.categoryArticle.edit', ['category_article' => $category_article,
                'all_categories'=>$all_categories,'all_services'=>$all_services,'similar_categories'=>$similar_categories,
                'similar_services'=>$similar_services]);
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

        $category_article = CategoryArticle::find($id);
        $category_article->update($request->all());

        if(isset($request->similar_categories) && $request->similar_categories != []){
            $category_article->similarGroupsCategories()->where('relation_model',Category::class)->sync($request->similar_categories);
        }

        if(isset($request->similar_services) && $request->similar_services != []){
            $category_article->similarGroupsServices()->where('relation_model',Service::class)->sync($request->similar_services);
        }

        return redirect('/dashboard/site/categoryArticle')->with([
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
        if($id){
            $category_article=CategoryArticle::find($id);
            if($category_article) {
                foreach ($category_article->articles()->get() as $item) {
                    if(\File::exists(public_path($item->img))){
                        \File::delete(public_path($item->img));
                    }
                }
                $category_article->articles()->delete();
                $category_article->delete();
                return response([
                    'operator' => true,
                    'status'=>'success',
                    'message'=>'حذف با موفقیت انجام گردید .'
                ]);
            }
        }
        return response([
            'operator' => true,
            'status'=>'error',
            'message'=>'دسته بندی انتخاب شده صحیح نمی باشد !'
        ]);
    }
}
