<?php

namespace App\Http\Controllers\Admin\site;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCollection;
use App\Models\Article;
use App\Models\CategoryArticle;
use App\Models\Tag;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    protected $articles;

    public function __construct(Article $articles)
    {
        $this->articles = $articles;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('Admin.site.article.list');
    }

    /**
     * @param Request $request
     * @return string
     */
    public function filter(Request $request)
    {
        $articles = Article::query();
        if (isset($request->title) && $request->title != ''){
            $articles->where('title','like','%'.$request->title.'%');
        }
        if (isset($request->startDate) && isset($request->endDate)){
            $articles->where('created_at','>',$request->startDate)->where("created_at",'<',$request->endDate);
        }
        return new ArticleCollection($articles->latest()->paginate(15));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category = CategoryArticle::all();
        $tags = Tag::all();
        return view('Admin.site.article.create', ['category' => $category, 'tags' => $tags]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['title' => 'required', 'category_article_id' => 'required',
            'image' => 'required','description'=>'required','summary'=>'required'],
            [
                'title.required' => 'لطفا عنوان را وارد کنید',
                'category_article_id.required' => 'لطفا دسته بندی را وارد کنید',
                'image.required' => 'لطفا عکس را وارد کنید',
                'description.required' => 'لطفا متن را وارد کنید',
                'summary.required' => 'لطفا خلاصه را وارد کنید',
            ]
        );

        $request['user_id'] = $request->user()->id;
        $article = Article::create($request->all());

        if(isset($request->image_path)) {
            $article->image = Helper::moveImage($request->image_path,'article');
            $article->save();
        }

//        if($request->tag != '')) $article->tag(['tag_name'=>$request->tag]);

        return redirect('/dashboard/site/article')->with([
            'create' => 'success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        if ($id) {
            $article= Article::find($id);
            $category = CategoryArticle::all();
            $tags = Tag::pluck('name');
            return view('Admin.site.article.edit', ['article' => $article, 'category' => $category, 'tags' => $tags]);
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
        $request->validate(['title' => 'required', 'category_article_id' => 'required|integer'
            ,'description'=>'required','summary'=>'required'
        ],
            [
                'title.required' => 'لطفا عنوان را وارد کنید',
                'category_article_id.required' => 'لطفا دسته بندی را وارد کنید',
                'category_article_id.integer' => 'لطفا دسته بندی را درست وارد کنید',
                'description.required' => 'لطفا متن را وارد کنید',
                'summary.required' => 'لطفا خلاصه را وارد کنید',
            ]
        );
        $request['user_id'] = $request->user()->id;
        $request['status'] = isset($request['status']) ? $request['status'] : 0;
        $article = Article::find($id);
        $article->update($request->all());

        if(isset( $request->image_path)) {
            if (\File::exists(public_path($article->image))) {
                \File::delete(public_path($article->image));
            }
            $article->image = Helper::moveImage($request->image_path,'article');
            $article->save();
        }

//        $list_tags_article=$article->tagNames();
//        $request_tags=$request->tag;
//        if($list_tags_article != null){
//            if($list_tags_article && $request_tags){
//                $removing_tag=array_diff($list_tags_article,$request_tags);
//                $new_tags=array_diff($request_tags,$list_tags_article);
//                $article->untag($removing_tag);
//                $article->tag($new_tags);
//            }
//        }else{
//            if(isset($request->tag)) $article->tag($request->tag);
//        }


        return redirect('/dashboard/site/article')->with([
            'update' => 'success'
        ]);

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
            $article = Article::find($id);
            if(\File::exists(public_path($article->image))){
                \File::delete(public_path($article->image));
            }
            $article->delete();
            return response([
                'operator' => true,
                'status'=>'success',
                'message'=>'حذف با موفقیت انجام گردید .'
            ]);
        }
        return response([
            'operator' => true,
            'status'=>'error',
            'message'=>'مقاله انتخاب شده صحیح نمی باشد !'
        ]);
    }
}
