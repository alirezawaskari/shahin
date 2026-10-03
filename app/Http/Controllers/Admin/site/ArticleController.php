<?php

namespace App\Http\Controllers\Admin\site;

use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCollection;
use App\Models\Article;
use App\Models\Representation;
use App\Models\CategoryArticle;
use App\Models\Tag;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * @var Article
     */
    protected $articles;

    /**
     * @param Article $articles
     */
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
        $articles = Article::filter();
        return new ArticleCollection($articles->latest()->paginate(15));
    }


    /**
     * Summary of create
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $category = CategoryArticle::all();
        $tags = Tag::all();
        return view('Admin.site.article.create', compact(['category' , 'tags']));
    }

    /**
     * Summary of store
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate(['title' => 'required','slug_title' => 'required', 'category_article_id' => 'required',
            'image_path' => 'required','description'=>'required'],
            [
                'title.required' => 'لطفا عنوان را وارد کنید',
                'slug_title.required' => 'لطفا عنوان  url مرتبط با این مقاله را وارد کنید',
                'category_article_id.required' => 'لطفا دسته بندی را وارد کنید',
                'image_path.required' => 'لطفا عکس را وارد کنید',
                'description.required' => 'لطفا متن را وارد کنید',
            ]
        );

        $request['user_id'] = $request->user()->id;
        $article = Article::create(collect($request->all())->except(['old_image', 'image_path'])->all()); // see update()
        $representations = auth()->user()->admin_representation_id ? [auth()->user()->admin_representation_id] : $request->input('representation_id');
        if(ghorfe())
        {
            $article = ghorfe()->articles()->create($request->all());
        }
        $article->representations()->sync($representations);
        MediaLibraryHelper::attachTo($article, $request->image_path, 'old_image');

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

            return view('Admin.site.article.edit', compact(['article', 'category', 'tags']));
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
        $request->validate(['title' => 'required', 'slug_title' => 'required', 'category_article_id' => 'required|integer'
            ,'description'=>'required'
        ],
            [
                'title.required' => 'لطفا عنوان را وارد کنید',
                'slug_title.required' => 'لطفا عنوان  url مرتبط با این مقاله را وارد کنید',
                'category_article_id.required' => 'لطفا دسته بندی را وارد کنید',
                'category_article_id.integer' => 'لطفا دسته بندی را درست وارد کنید',
                'description.required' => 'لطفا متن را وارد کنید',
            ]
        );
        $request['user_id'] = $request->user()->id;
        $request['status'] = isset($request['status']) ? $request['status'] : 0;

        $article = Article::find($id);
        // Never write the legacy `old_image` column: it is BOTH a fillable column and the
        // file input's name, so a failed ajax upload would put a raw UploadedFile straight
        // into it. Spatie media is the source of truth; the accessor falls back to the
        // column for rows that haven't been migrated yet.
        $article->update(collect($request->all())->except(['old_image', 'image_path'])->all());
        !auth()->user()->admin_representation_id ? $article->representations()->sync($request->input('representation_id')) : '';

        MediaLibraryHelper::attachTo($article, $request->image_path, 'old_image');

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

        if(auth()->user()->admin_representation_id)
        {
            $article->representations()->update(['representation_id' => auth()->user()->admin_representation_id]);
        }
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
