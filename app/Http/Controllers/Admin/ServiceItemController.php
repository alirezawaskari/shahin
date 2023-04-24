<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Models\Gallery;
use App\Models\Menu;
use App\Models\ServiceItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiceItemController extends Controller
{

    /**
     * @param $service
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function createByService($service)
    {
        return view('Admin.serviceItem.create', compact(['service']));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $request->validate(['title' => 'required', 'price' => 'required',
        ], [
                'title.required' => 'لطفا عنوان را وارد کنید',
                'price.required' => 'لطفا قیمت را وارد کنید',
            ]
        );
        if($request->price > 0 )$request['price'] = str_replace(",","",$request->price);
        $request['status'] = isset($request['status']) ? 1 : 0;
        $request['isShow'] = isset($request['isShow']) ? 1 : 0;
        $service_item = ServiceItem::create($request->all());

        if($request->menu_title != '') $menu_title = $request->menu_title;
        else $menu_title = $service_item->title;

        if($request->is_menu == 1){
            Menu::updateOrCreate(['menuable_id' => $service_item->id],[
                'menuable_type' => ServiceItem::class,'parent_id'=>$service_item->service_id,
                'title'=>$menu_title,'depth'=> 3 ]);
        }

        if(isset( $request->image_path)) {
            if (\File::exists(public_path($service_item->image))) {
                \File::delete(public_path($service_item->image));
            }
            $service_item->image = Helper::moveImage($request->image_path,'service_item');
            $service_item->save();
        }

        $files = $request->multiple_image;
        if ($files != '') {
            foreach ($files as $extra_file) {
                if (isset($extra_file['file'])) {
                    $image = Helper::moveImage($extra_file['file'],'service_item');
                    Gallery::create(['galleryable_id'=>$service_item->id,
                        'galleryable_type'=> ServiceItem::class,'title'=>$extra_file['title'],'image'=>$image]);
                }
            }
        }

        return redirect('/dashboard/service/serviceItem/'.$service_item->service->slug)
            ->with(['create' => 'success']);
    }


    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('Admin.serviceItem.create', ['service_id'=>$id]);
    }

    /**
     * @param $service
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function serviceItemList($service)
    {
        $service_items=ServiceItem::where('service_id',$service->id)->orderBy('id','DESC')->paginate(15);
        return view('Admin.serviceItem.list', compact(['service_items' ,'service']));
    }

    /**
     * @param ServiceItem $serviceItem
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse
     */
    public function edit(ServiceItem $serviceItem)
    {
        if ($serviceItem) {
            return view('Admin.serviceItem.edit',['service_item'=>$serviceItem]);
        }
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @param $service_item
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $service_item)
    {
        $request->validate(['title' => 'required', 'price' => 'required',
        ], [
                'title.required' => 'لطفا عنوان را وارد کنید','price.required' => 'لطفا قیمت را وارد کنید',
            ]
        );

        $request['price'] = (float) str_replace(',', '', $request->input('price'));
        $request['status'] = isset($request['status']) ? $request['status'] : 0;
        $request['isShow'] = isset($request['isShow']) ? $request['isShow'] : 0;

        $service_item->update([
            'title' => $request->title,
            'price' => $request->price,
            'content' => $request->content,
            'isShow' => $request->isShow,
            'status' => $request->status,
        ]);

        if($request->menu_title != '') $menu_title = $request->menu_title;
        else $menu_title = $service_item->title;

        if($request->is_menu == 1){
            Menu::updateOrCreate(['menuable_id' => $service_item->id ,'menuable_type' => ServiceItem::class,
                'parent_id'=>$service_item->service_id],['title'=>$menu_title,'depth'=> 3 ]);
        }

        if(isset( $request->image_path)) {
            if (\File::exists(public_path($service_item->image))) {
                \File::delete(public_path($service_item->image));
            }
            $service_item->image = Helper::moveImage($request->image_path,'service_item');
            $service_item->save();
        }

        $files = $request->multiple_image;
        if ($files != '') {
            foreach ($files as $extra_file) {
                if (isset($extra_file['file'])) {

                    $image = Helper::moveImage($extra_file['file'],'service_item');
                    Gallery::create(['galleryable_id'=>$service_item->id,
                        'galleryable_type'=> ServiceItem::class,'title'=>$extra_file['title'],'image'=>$image]);

                }
            }
        }

        return redirect('/dashboard/service/serviceItem/'.$service_item->service->slug)
            ->with(['update' => 'success']);

    }

    /**
     * @param ServiceItem $serviceItem
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(ServiceItem $serviceItem)
    {
        if ($serviceItem) {
            $service_item = $serviceItem;

            if($service_item->galleries()){
                foreach ($service_item->galleries()->get() as $item) {
                    if (\File::exists(public_path($item->image))) {
                        \File::delete(public_path($item->image));
                    }
                }
            }

            if (\File::exists(public_path($service_item->image))) {
                \File::delete(public_path($service_item->image));
            }

            $service_item->delete();
            return redirect('/dashboard/service/serviceItem/'.$service_item->service->slug)
                ->with(['delete' => 'success']);
        }
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function selectByService(Request $request)
    {
       if ($request->ajax()) {
           $id = $request->input('id');
           if (ServiceItem::where('service_id', $id)->get()->count() > 0) {
               $service_items = ServiceItem::where('service_id', $id)->where('status' , 1)->get();
               $service_items = $service_items->map(function ($item) {
                    $temp = [];
                    $temp['id'] = $item->id;
                    $temp['label'] = $item->title . ' - ' . number_format($item->price) . ' ریال';
                    return $temp;
                });
                return response()->json(['count' => 'available', 'service_items' => $service_items]);
           }
       }
   }
}
