<?php

namespace App\Http\Controllers\Admin\site;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $comments = Comment::orderBy('id', 'DESC');
        $queries=[];
        $columns=['type'];
        foreach ($columns as $column) {
            if($request->has($column)){
                if($request->$column == 1){
                    $comments = $comments->where('commentable_type',Product::class);
                }
                if($request->$column == 2){
                    $comments = $comments->where('commentable_type',Service::class);
                }

                $queries[$column]=$request->$column;
            }
        }
        $comments=$comments->paginate(15)->appends($queries);
        $filter=true;
        return view('Admin.site.comments.list', compact('comments','queries','filter'));
    }

    public function desktop()
    {
        $comments=[];
        if(User::checkManager() != null){ $center_id = User::checkManager();
            $center = Center::find($center_id);

            if($center->centerable_type == Service::class){
                $comments=$center->centerable->comments()->orderBy('id', 'DESC')->paginate(15);
            }

            if($center->centerable_type == Category::class){
                $product_ids=$center->centerable->products()->pluck('products.id');
                $comments=Comment::where('commentable_type',Product::class)
                    ->whereIn('commentable_id',$product_ids->toArray())->orderBy('id', 'DESC')->paginate(15);
            }
        }
        $filter=false;
        return view('Admin.site.comments.list', compact('comments','filter'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
       $comment=Comment::find($id);
       if($comment && $comment->active == 0){
           $comment->active = 1;
           $comment->save();
           return redirect()->back()->with([ 'update' => 'success']);
       }
        if($comment && $comment->active == 1){
            $comment->active = 0;
            $comment->save();
            return redirect()->back()->with([ 'update' => 'success']);
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
