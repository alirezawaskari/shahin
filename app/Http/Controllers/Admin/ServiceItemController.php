<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Models\Menu;
use App\Models\ServiceItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AskedQuestion;
use App\Models\Gallery;
use App\Models\Service;
use App\Models\WorkSample;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class ServiceItemController extends Controller
{

    /**
     * Summary of createByService
     * @param Service $service
     * @return \Illuminate\Contracts\View\View
     */
    public function createByService(Service $service)
    {
        return view('Admin.serviceItem.create', compact(['service']));
    }

    /**
     * Summary of storeByService
     * @param Service $service
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeByService(Service $service , Request $request)
    {
        $request->validate(['title' => 'required',
        ], [
                'title.required' => 'لطفا عنوان را وارد کنید',
            ]
        );
        $service_item = $service->service_items()->create($request->all());

        if($request->menu_title != '') $menu_title = $request->menu_title;
        else $menu_title = $service_item->title;

        if($request->is_menu == 1){
            Menu::updateOrCreate(['menuable_id' => $service_item->id],[
                'menuable_type' => ServiceItem::class,'parent_id'=>$service_item->service_id,
                'title'=>$menu_title,'depth'=> 3 ]);
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
        return view('Admin.serviceItem.edit',['service' => $serviceItem]);
    }

    /**
     * Summary of update
     * @param Request $request
     * @param ServiceItem $serviceItem
     */
    public function update(Request $request, ServiceItem $serviceItem)
    {
         switch ($request->update_type) {
            case "basic_information":
               return $this->updateBasicInformation($request,$serviceItem);
            case "text_information":
                return $this->updateTextInformation($request,$serviceItem);
            case "question_information":
                return $this->updateQuestionInformation($request,$serviceItem);
            case "first_images":
                return $this->uploadFirstImages($request,$serviceItem);
            case "portfolio_images":
                return $this->uploadPortfolioImages($request,$serviceItem);
            case "site_images":
                return $this->uploadSiteImages($request,$serviceItem);
            case "insert_question":
                return $this->insertQuestion($request,$serviceItem);
            case "update_single_question":
                return $this->updateSingleQuestion($request,$serviceItem);
             case "delete_single_question":
                 return $this->deleteSingleQuestion($request,$serviceItem);
        }
    }

    /**
     * update basic information of service
     * @param Request $request
     * @param ServiceItem $serviceItem
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function updateBasicInformation(Request $request, ServiceItem $serviceItem)
    {
        $validator = Validator::make($request->all(), [
            'title'=>['required'],
            'en_title'=>['required'],
            'description'=>['required'],
            'status'=>array('required',Rule::in(1,0)),
            'isShow'=>array('required',Rule::in(0,1)),
            ]);
        if ($validator->fails()) {
            return response(['status'=>'error','messages'=>$validator->messages()->all()]);
        }
        $serviceItem->update([
            'title'=>$request->title,
            'en_title'=>$request->en_title,
            'description'=>$request->description,
            'status'=>$request->status,
            'isShow'=>$request->isShow,
        ]);
        return response(['status'=>'success','messages'=>['ثبت تغییرات با موفقیت انجام گرفت !']]);
    }
    /**
     * update text information of service
     * @param Request $request
     * @param ServiceItem $serviceItem
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function updateTextInformation(Request $request, ServiceItem $serviceItem)
    {
        $serviceItem->update(array(
            'content'=>$request['content'],
            'txt_box_1'=>$request->txt_box_1,
            'left_content'=>$request->left_content,
        ));
        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }
    /**
     * update question information of service
     * @param Request $request
     * @param ServiceItem $serviceItem
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function updateQuestionInformation(Request $request, ServiceItem $serviceItem)
    {
        if ($request->askedQuestion != '') {
            $serviceItem->askedQuestions()->delete();
            foreach ($request->askedQuestion as $asked_question) {
                if (isset($asked_question['answer'])) {
                    AskedQuestion::create(['askedquestionable_id' => $serviceItem->id,
                        'askedquestionable_type' => ServiceItem::class,
                        'question' => $asked_question['question'],'answer' => $asked_question['answer']]);
                }
            }
        }
        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }
    /**
     * Summary of uploadFirstImages
     * @param Request $request
     * @param ServiceItem $serviceItem
     * @return \Illuminate\Http\Response
     */
    private function uploadFirstImages(Request $request, ServiceItem $serviceItem)
    {
        $request->validate(
            [
                'thumb' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                'image' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                'icon' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                'img_box_1' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                'image_app' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
            ]);

        $imagesTitles['img_box_1']=$request->file('img_box_1');
        $imagesTitles['icon']=$request->file('icon');
        $imagesTitles['thumb']=$request->file('thumb');
        $imagesTitles['image']=$request->file('image');
        $imagesTitles['image_app']=$request->file('image_app');
        foreach ($imagesTitles as $key=>$value){
            if (!is_null($request->file($key))){
                $serviceItem->$key = Helper::uploadImage($value , 'serviceItem');
                Helper::deleteImage(public_path($serviceItem->$key));
                $serviceItem->save();
            }
        }
        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }

    /**
     * @param Request $request
     * @param ServiceItem $serviceItem
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function uploadPortfolioImages(Request $request, ServiceItem $serviceItem)
    {
        $files = $request->multiple_image;
        if ($files != '') {
            foreach ($files as $extra_file) {
                if (isset($extra_file['file'])) {
                    $extra_file['title'] = isset($extra_file['title']) ? $extra_file['title'] : null;
                    $image = Helper::moveImage($extra_file['file'],'serviceItem');
                    WorkSample::create(['worksampleable_id' => $serviceItem->id,
                        'worksampleable_type' => ServiceItem::class, 'title' => $extra_file['title']
                        ,'image' => $image]);
                }
            }
        }

        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }

    /**
     * @param Request $request
     * @param ServiceItem $serviceItem
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function uploadSiteImages(Request $request, ServiceItem $serviceItem)
    {
        //banner vertical image
        $files = $request->verticalImages;
        if ($files != '') {
            foreach ($files as $extra_file) {
                if (isset($extra_file['file'])) {
                    $image = Helper::moveImage($extra_file['file'],'serviceItem');
                    Gallery::create(['galleryable_id' => $serviceItem->id,
                        'galleryable_type' => ServiceItem::class, 'title' => $extra_file['file_title']
                        , 'image' => $image, 'type' => 0]);
                }
            }
        }
        //banner horizontal image
        $files = $request->horizontalImages;
        if ($files != '') {
            foreach ($files as $extra_file) {
                if (isset($extra_file['file'])) {
                    $image = Helper::moveImage($extra_file['file'],'serviceItem');
                    Gallery::create(['galleryable_id' => $serviceItem->id,
                        'galleryable_type' => ServiceItem::class,
                        'title' => $extra_file['file_title'], 'image' => $image, 'type' => 1]);
                }
            }
        }
        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }
        /**
     * @param Request $request
     * @param ServiceItem $serviceItem
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function insertQuestion(Request $request, ServiceItem $serviceItem)
    {
        $validator = Validator::make($request->all(),
            [
                'question'=>['required' ],
                'answer'=>['required' ],
            ]);
        if ($validator->fails()) {
            return response(['status'=>'error','messages'=>$validator->messages()->all()]);
        }
       $question=$serviceItem->askedQuestions()->create([
           'question'=>$request->question,
           'answer'=>$request->answer
       ]);
        return response(['status'=>'success','data'=>['type'=>'asked_question','data'=>$question],'messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }

    /**
     * @param Request $request
     * @param ServiceItem $serviceItem
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function updateSingleQuestion(Request $request, ServiceItem $serviceItem)
    {
        $validator = Validator::make($request->all(),
            [
                'asked_question_id'=>['required',Rule::exists('asked_questions','id')],
                'question'=>['required' ],
                'answer'=>['required' ],

            ]);
        if ($validator->fails()) {
            return response(['status'=>'error','messages'=>$validator->messages()->all()]);
        }

        AskedQuestion::where('id',$request->asked_question_id)->where('askedquestionable_type',ServiceItem::class)
            ->where('askedquestionable_id',$serviceItem->id)->update([
            'question'=>$request->question,
            'answer'=>$request->answer
        ]);
        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }

    /**
     * delete single
     * @param Request $request
     * @param ServiceItem $serviceItem
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function deleteSingleQuestion(Request $request, ServiceItem $serviceItem)
    {
        AskedQuestion::where('id',$request->asked_question_id)->where('askedquestionable_type',Service::class)
            ->where('askedquestionable_id',$serviceItem->id)->delete();
        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
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
            $service_item->menu()->delete();
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
                    $temp['label'] = $item->title;
                    return $temp;
                });
                return response()->json(['count' => 'available', 'service_items' => $service_items]);
           }
       }
   }
}
