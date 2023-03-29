<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleRate;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    public function articles()
    {
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $articles = Cache::remember('article-' . $currentPage, Carbon::now()->addMinute(30), function(){
            return Article::whereStatus(1)->latest()->paginate(10);
        });
        return view('site.articles.index' , compact('articles'));
    }

    public function single(Article $article)
    {
        OpenGraph::setDescription(substr($article->summary,200));
        OpenGraph::setTitle('article');
        OpenGraph::addProperty('type', 'articles');
        return view('site.articles.single' , compact('article'));
    }

    public function rate(Request $request)
    {
        $type = $request->input('type');
        $article = $request->input('article');

        if (auth()->check()){
           $user = auth()->user();

           if (ArticleRate::where('article_id' , $article)->where('user_id' , $user->id)->count() > 0){
               return redirect()->back()->with([
                   'article_rate' => 'find'
               ]);
           }else{
               ArticleRate::create([
                   'user_id' => $user->id,
                   'article_id' => $article,
                   'type' => $type,
               ]);
               return redirect()->back()->with([
                   'article_rate' => 'success'
               ]);
           }
        }else{
            return redirect()->back()->with([
                'article_rate' => 'login'
            ]);
        }
    }
}
