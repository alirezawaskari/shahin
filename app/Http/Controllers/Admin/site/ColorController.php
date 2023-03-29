<?php

namespace App\Http\Controllers\Admin\site;

use App\Http\Controllers\Controller;
use App\Http\Resources\ColorCollection;
use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('Admin.site.color.list');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function filter(Request $request)
    {
//        $colors=Color::query();
       $colors= Color::orderBy('id', 'DESC');
       if (isset($request->title) && $request->title!= null){
           $colors->where('title', 'like', '%' . $request->title . '%')
               ->orWhere('color_palette',$request->title);
       }

        return new ColorCollection($colors->paginate(20));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.site.color.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['title' => 'required','color_palette'=>'required'],
        [
            'title.required' => 'لطفا عنوان را وارد کنید',
            'color_palette.required' => 'لطفا پالت رنگ را وارد کنید',
        ]);

        Color::create($request->all());

        return redirect()
            ->route('color.create')
            ->with([ 'create' => 'success']);
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        if ($id) {
            $color = Color::find($id);
//             $color->color_palette=$this->rgbToHex($color->color_palette);
            return view('Admin.site.color.edit', ['color' => $color]);
        }
        return redirect()->back();
    }

//    /**
//     * convert rgb color to hex color
//     * @param string $rgbColor
//     * @return string
//     */
//    private function rgbToHex(string $rgbColor)
//    {
//        preg_match_all('!\d+!', $rgbColor, $matches);
//        $matches=collect($matches)->first();
//            if (is_array($matches) && sizeof($matches) == 3)
//                list($r, $g, $b) =$matches;
//            $r = intval($r); $g = intval($g);
//            $b = intval($b);
//            $r = dechex($r<0?0:($r>255?255:$r));
//            $g = dechex($g<0?0:($g>255?255:$g));
//            $b = dechex($b<0?0:($b>255?255:$b));
//            $color = (strlen($r) < 2?'0':'').$r;
//            $color .= (strlen($g) < 2?'0':'').$g;
//            $color .= (strlen($b) < 2?'0':'').$b;
//            $color ='#'.$color;
//            return $color;
//
//    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        $request->validate(['title' => 'required','color_palette'=>'required'],
            [
                'title.required' => 'لطفا عنوان را وارد کنید',
                'color_palette.required' => 'لطفا پالت رنگ را وارد کنید',
            ]);
        $request['status'] = isset($request->status) ? 1 : 0;
        $color= Color::find($id);
        $color->update($request->all());

        return redirect()
            ->route('color.index')
            ->with([ 'update' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
            $color=Color::find($id);
            if($color) {
                $color->delete();
                    return response([
                        'operator' => true,
                        'status'=>'success',
                        'message'=>'حذف با موفقیت انجام گردید .'
                    ]);
            }
        return response([
            'operator' => true,
            'status'=>'error',
            'message'=>'رنگ انتخاب شده صحیح نمی باشد !'
        ]);
    }
}
