<?php

namespace App\Http\Controllers\Admin\site;

use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\NewsCollection;
use App\Models\CategoryNews;
use App\Models\News;
use App\Models\Tag;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('Admin.site.news.list');
    }

    /**
     * @param Request $request
     * @return NewsCollection
     */
    public function filter(Request $request)
    {
        $news=News::query();
        if (isset($request->title) && $request->title != ''){
            $news->where('title','like','%'.$request->title.'%');
        }
        if (isset($request->startDate) && isset($request->endDate)){
            $news->where('created_at','>',$request->startDate)->where("created_at",'<',$request->endDate);
        }
        return new NewsCollection($news->latest()->paginate(20));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tags = Tag::all();
        $category_news = CategoryNews::all();
        return view('Admin.site.news.create', compact('category_news','tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['title' => 'required','subject'=> 'required', 'image_path' => 'required'],
            [
                'title.required' => 'لطفا عنوان را وارد کنید',
                'image_path.required' => 'لطفا عکس را وارد کنید',
                'subject.required' => 'لطفا موضوع خبر را وارد کنید',
            ]);

        $request['user_id'] = $request->user()->id;
        // Never mass-assign the legacy `image` column: it is BOTH a fillable column and the
        // file input's name, so a failed ajax upload would write a raw UploadedFile into it.
        // Spatie media is the source of truth; the accessor falls back to the column.
        $news = News::create(collect($request->all())->except(['image', 'image_path'])->all());

        // move the temp ajax upload into the news image collection (Spatie, optimized)
        MediaLibraryHelper::attachTo($news, $request->image_path, 'image');
//      if(isset($request->tag)) $news->tag($request->tag);

        return redirect('/dashboard/site/news')->with(['create' => 'success']);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        if ($id) {
            $news= News::find($id);
//            $tags = Tag::pluck('name');
            $category_news = CategoryNews::all();
            return view('Admin.site.news.edit', compact('news','category_news'));
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
        $request->validate(['title' => 'required','subject'=> 'required'],
            [
                'title.required' => 'لطفا عنوان را وارد کنید',
                'subject.required' => 'لطفا موضوع خبر را وارد کنید',
            ]
        );

        $request['status'] = isset($request['status']) ? $request['status'] : 0;
        $request['user_id'] = $request->user()->id;
        $news = News::find($id);
        $news->update(collect($request->all())->except(['image', 'image_path'])->all()); // see store()

        // replace the news image via Spatie (deletes the old file, optimizes the new one)
        MediaLibraryHelper::attachTo($news, $request->image_path, 'image');

//        $list_tags_news=$news->tagNames();
//        $request_tags=$request->tag;
//        if($list_tags_news != null){
//            if($list_tags_news && $request_tags){
//                $removing_tag=array_diff($list_tags_news,$request_tags);
//                $new_tags=array_diff($request_tags,$list_tags_news);
//                $news->untag($removing_tag);
//                $news->tag($new_tags);
//            }
//        }else{
//            if(isset($request->tag)) $news->tag($request->tag);
//        }

        return redirect('/dashboard/site/news')->with(['update' => 'success']);
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
            $news = News::find($id);
            $news->delete();
            return response([
                'operator' => true,
                'status'=>'success',
                'message'=>'حذف با موفقیت انجام گردید .'
            ]);
        }
        return response([
            'operator' => true,
            'status'=>'error',
            'message'=>'خبر انتخاب شده صحیح نمی باشد !'
        ]);
    }

}
