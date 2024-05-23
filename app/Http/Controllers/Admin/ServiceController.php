<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Resources\ServiceItemCollection;
use App\Models\AskedQuestion;
use App\Models\BeforeForm;
use App\Models\Category;
use App\Models\File;
use App\Models\Gallery;
use App\Models\Menu;
use App\Models\Service;
use App\Models\WorkSample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ServiceController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $services = Service::orderBy('id', 'DESC');
        $queries=[];
        $columns=['title','pay_type','purchase_type'];

        foreach ($columns as $column) {
            if($request->has($column)){
                if ($request->$column != null) {
                    if ($column == 'title') {
                        $services = $services->where($column, 'like', '%' . $request->$column . '%');
                        $queries[$column] = $request->$column;
                    }
                }
                if ($column == 'pay_type' || $column == 'purchase_type' ) {
                    if($request->$column != 'all'){
                        $services = $services->where($column, 'like', '%' . $request->$column . '%');
                        $queries[$column]=$request->$column;
                    }
                    if($request->$column == 'all'){
                        $queries[$column]='all';
                    }
                }
            }
        }

        $services = $services->paginate(20)->appends($queries);

        if ($request->ajax()) {
            return view('Admin.service.load', ['services' => $services])->render();
        }

        $pay_type = [0 => 'نقدی', 1 => 'اقساطی',2 => 'هر دو'];
        $purchase_type = [ 0 => 'آنلاین', 1=>'حضوری',2=>'هر دو'];

        return view('Admin.service.all', compact('services','queries','pay_type','purchase_type'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $service_categories=[];
        $category_service=Category::where('slug','service')->first();
        if(isset($category_service) && $category_service != ''){
            $service_categories = Category::where('parent_id',$category_service->id)->select('id','title','parent_id')
                ->orderBy('id', 'DESC')->get();
        }
        $all_categories = Category::select('id','title')->get();
        $all_services = Service::select('id','title')->get();
        return view('Admin.service.create',['all_categories'=>$all_categories,
            'all_services'=>$all_services,'service_categories'=>$service_categories]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {

        $this->validate(request(), [
            'title' => 'required|string',
            'price' => 'required',
            'category_id'=>'required',
            'min_credit'=>'required',
            'max_credit'=>'required',
            'status'=>array('required',Rule::in(0,2)),
            'isShow'=>array('required',Rule::in(0,1)),
            'items'=>array('required',Rule::in(0,1)),
            'center'=>array('required',Rule::in(0,1)),
            'multiple'=>array('required',Rule::in(0,1)),
            'ability_invite_friend'=>array('required',Rule::in(0,1)),
            'purchase_type'=>array('required',Rule::in(0,1,2)),
            'pay_type'=>array('required',Rule::in(0,1,2)),
         ]);

        if($request->price > 0 )$request['price'] = str_replace(",","",$request->price);
        if($request->max_credit > 0 )$request['max_credit'] = str_replace(",","",$request->max_credit);
        if($request->min_credit > 0 )$request['min_credit'] = str_replace(",","",$request->min_credit);
        $request['ability_invite_friend'] = isset($request['ability_invite_friend']) ? 1 : 0;


        $service = Service::create($request->all());

        if($request->menu_title != '') $menu_title = $request->menu_title;
        else $menu_title = $service->title;

        if($request->is_menu == 1){
            Menu::create([
                'menuable_id' => $service->id,
                'menuable_type'=>Service::class,
                'position'=>'1',
                'title'=>$menu_title,
                'depth'=>2
            ]);
        }

        if (isset($request->similar_services)){
            $service->similarGroups()->attach($request->similar_services,['similar_model'=>Service::class,'relation_model'=>Service::class]);
        }
        if (isset($request->similar_categories)){
            $service->similarGroupCategories()->attach($request->similar_categories,['similar_model'=>Service::class,'relation_model'=>Category::class]);
        }


        return redirect(route('service.index'))->with([
            'create' => 'success'
        ]);
    }

    /**
     * @param Service $service
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(Service $service)
    {
        $all_categories = Category::select('id','title')->get();
        $all_services = Service::select('id','title')->where('id','!=',$service->id)->get();
        $similar_services=$service->similarGroups()->where('similar_model',Service::class)->where('relation_model',Service::class)->pluck('relation_id')->toArray();
        $similar_categories=$service->similarGroupCategories()->where('similar_model',Service::class)->where('relation_model',Category::class)->pluck('relation_id')->toArray();
        $service_categories=[];
        $category_service=Category::where('slug','service')->first();
        if(isset($category_service) && $category_service != ''){
            $service_categories = Category::where('parent_id',$category_service->id)->select('id','title','parent_id')
                ->orderBy('id', 'DESC')->get();
        }

        return view('Admin.service.edit',['service'=>$service,'all_categories'=>$all_categories,
            'all_services'=>$all_services,'similar_categories'=>$similar_categories,'similar_services'=>$similar_services,
            'service_categories'=>$service_categories]);
    }

    /**
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|void
     */
    public function update(Request $request, Service $service)
    {
         switch ($request->update_type) {
            case "basic_information":
               return $this->updateBasicInformation($request,$service);
            case "text_information":
                return $this->updateTextInformation($request,$service);
            case "site_information":
                return $this->updateSiteInformation($request,$service);
            case "question_information":
                return $this->updateQuestionInformation($request,$service);
            case "first_images":
                return $this->uploadFirstImages($request,$service);
            case "portfolio_images":
                return $this->uploadPortfolioImages($request,$service);
            case "site_images":
                return $this->uploadSiteImages($request,$service);
            case "insert_question":
                return $this->insertQuestion($request,$service);
            case "update_single_question":
                return $this->updateSingleQuestion($request,$service);
             case "delete_single_question":
                 return $this->deleteSingleQuestion($request,$service);
             case "session_titles":
                 return $this->insertSessionTitles($request,$service);
             case "rules":
                 return $this->updateRules($request,$service);
        }
    }


    /**
     * @param Service $service
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return redirect(route('service.index'))->with([
            'delete' => 'success'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function form(Request $request)
    {
        $service = Service::find($request->service);
        $service->update(['after_fields' => $request->after_fields]);
        return response(['operator' => true]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function fetchForm(Request $request)
    {
        $service = Service::find($request->service);
        return response(['operator' => true , 'after' => $service->after_fields]);
    }

    /**
     * @param Service $service
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function beforeForm(Service $service)
    {
        $forms = $service->beforeforms()->get();
        return view('Admin.service.form' , compact('forms' , 'service'));
    }

    /**
     * @param Service $service
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function beforeFormCreate(Service $service)
    {
       $data_display_levels=BeforeForm::getDataDisplayLevel();
       $level_save_data=BeforeForm::getLevelSaveData();
        $before_formable=$service;
        return view('Admin.beforeform.create' , compact('service','data_display_levels','level_save_data','before_formable'));
    }

    /**
     * @param BeforeForm $beforeForm
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function beforeFormItem(BeforeForm $beforeForm)
    {
        $items = $beforeForm->beforeformitems()->get();
        return view('Admin.service.formitem' , compact('items' , 'beforeForm'));
    }

    /**
     * @param BeforeForm $beforeForm
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function beforeFormItemCreate(BeforeForm $beforeForm)
    {
        return view('Admin.beforeformitem.create' , compact('beforeForm'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public static function selectByCategory(Request $request)
    {
        if ($request->ajax()) {
            $id = [$request->input('id')];
            if (Service::whereIn('category_id', $id)->get()->count() > 0) {
                $services = Service::whereIn('category_id', $id)->get();
                return response()->json(['count' => 'available', 'services' => $services]);
            }
        }
    }

    /**
     *  fetch serviceItems  of service
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function fetchServiceItemsByServiceId(Request $request)
    {
        $validator = Validator::make($request->all(), ['id'=>['required',Rule::exists('services','id')]]);
        if ($validator->fails()) {
            return response(['status'=>'error','messages'=>$validator->messages()->all()]);
        } else {
            $service=Service::find($request->id);
            return response(['status'=>'success','data'=>   new ServiceItemCollection($service->service_items)]);
        }
    }

    /**
     * update basic information of service
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function updateBasicInformation(Request $request, Service $service)
    {
        $validator = Validator::make($request->all(), [
            'title'=>['required'],
            'en_title'=>['required'],
            'description'=>['required'],
            'status'=>array('required',Rule::in(1,2)),
            'isShow'=>array('required',Rule::in(0,1)),
            'items'=>array('required',Rule::in(0,1)),
            'center'=>array('required',Rule::in(0,1)),
            'multiple'=>array('required',Rule::in(0,1)),
            'ability_invite_friend'=>array('required',Rule::in(0,1)),
            'wage_percent'=>['required'],
            'price'=>['required'],
            'tax'=>['required'],
            ]);
        $request->price = (float) str_replace(',', '', $request-> price);
        if ($validator->fails()) {
            return response(['status'=>'error','messages'=>$validator->messages()->all()]);
        }
        $service->update(array(
            'title'=>$request->title,
            'en_title'=>$request->en_title,
            'description'=>$request->description,
            'status'=>$request->status,
            'isShow'=>$request->isShow,
            'items'=>$request->items,
            'center'=>$request->center,
            'multiple'=>$request->multiple,
            'tax'=>$request->tax,
            'ability_invite_friend'=>$request->ability_invite_friend,
            'wage_percent'=>$request->wage_percent,
            'price'=>$request->price,
            'gift'=>$request->gift,
        ));
        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }

    /**
     * update text information of service
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function updateTextInformation(Request $request, Service $service)
    {
          $service->update(array(
              'demo'=>$request->demo,
              'content'=>$request->content,
              'txt_box_1'=>$request->txt_box_1,
              'left_content'=>$request->left_content,
          ));
        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }
    /**
     * update site information of service
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function updateSiteInformation(Request $request, Service $service)
    {
        $validator = Validator::make($request->all(),
            [
                'category_id'=>'required',
                'min_credit'=>'required',
                'max_credit'=>'required',
                'pay_type'=>array('required',Rule::in(0,1,2)),
                'purchase_type'=>array('required',Rule::in(0,1,2)),
            ]);
        if ($validator->fails()) {
            return response(['status'=>'error','messages'=>$validator->messages()->all()]);
        }

        $service->update(array(
              'category_id'=>$request->category_id,
              'min_credit'=> (float) str_replace(',', '', $request->min_credit),
              'max_credit'=>(float) str_replace(',', '', $request->max_credit),
              'purchase_type'=>$request->purchase_type,
              'pay_type'=>$request->pay_type,
              'menu_title'=>$request->menu_title,
          ));
        if($service->menu()->exists()) {
            $service->menu->update([
                'title'=>$request->menu_title
            ]);
        }else{
            $service->menu()->create([
                'position'=>'1',
                'title'=>$request->menu_title,
                'depth'=>2
            ]);
        }
        if (isset($request->similar_services)){
            $service->similarGroups()->sync($request->similar_services,['similar_model'=>Service::class,'relation_model'=>Service::class]);
        }
        if (isset($request->similar_categories)){
            $service->similarGroupCategories()->sync($request->similar_categories,['similar_model'=>Service::class,'relation_model'=>Category::class]);
        }
        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }
    /**
     * update question information of service
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function updateQuestionInformation(Request $request, Service $service)
    {
        if ($request->askedQuestion != '') {
            $service->askedQuestions()->delete();
            foreach ($request->askedQuestion as $asked_question) {
                if (isset($asked_question['answer'])) {
                    AskedQuestion::create(['askedquestionable_id' => $service->id,
                        'askedquestionable_type' => Service::class,
                        'question' => $asked_question['question'],'answer' => $asked_question['answer']]);
                }
            }
        }
        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }


    /**
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Http\RedirectResponse
     */
    private function uploadFirstImages(Request $request, Service $service)
    {
        $request->validate(
            [
                'thumb' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                'image' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                'icon' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                'img_box_1' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                'desktop_slider' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                'mobile_slider' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                'image_app' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
            ]);


        $imagesTitles['img_box_1']=$request->file('img_box_1');
        $imagesTitles['icon']=$request->file('icon');
        $imagesTitles['thumb']=$request->file('thumb');
        $imagesTitles['image']=$request->file('image');
        $imagesTitles['image_app']=$request->file('image_app');
        foreach ($imagesTitles as $key=>$value){
            if (!is_null($request->file($key))){
                $service->$key = Helper::uploadImage($value , 'service');
                Helper::deleteImage(public_path($service->$key));
                $service->save();
            }
        }
        $this->uploadSliders($request, $service);
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function uploadPortfolioImages(Request $request, Service $service)
    {

        $files = $request->multiple_image;
        if ($files != '') {
            foreach ($files as $extra_file) {
                if (isset($extra_file['file'])) {
                    $extra_file['title'] = isset($extra_file['title']) ? $extra_file['title'] : null;
                    $image = Helper::moveImage($extra_file['file'],'service');
                    WorkSample::create(['worksampleable_id' => $service->id,
                        'worksampleable_type' => Service::class, 'title' => $extra_file['title']
                        ,'image' => $image]);
                }
            }
        }

        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }

    /**
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function uploadSiteImages(Request $request, Service $service)
    {
        //banner vertical image
        $files = $request->verticalImages;
        if ($files != '') {
            foreach ($files as $extra_file) {
                if (isset($extra_file['file'])) {
                    $image = Helper::moveImage($extra_file['file'],'service');
                    Gallery::create(['galleryable_id' => $service->id,
                        'galleryable_type' => Service::class, 'title' => $extra_file['file_title']
                        , 'image' => $image, 'type' => 0]);
                }
            }
        }
        //banner horizontal image
        $files = $request->horizontalImages;
        if ($files != '') {
            foreach ($files as $extra_file) {
                if (isset($extra_file['file'])) {
                    $image = Helper::moveImage($extra_file['file'],'service');
                    Gallery::create(['galleryable_id' => $service->id,
                        'galleryable_type' => Service::class,
                        'title' => $extra_file['file_title'], 'image' => $image, 'type' => 1]);
                }
            }
        }
        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }

    /**
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function insertQuestion(Request $request, Service $service)
    {
        $validator = Validator::make($request->all(),
            [
                'question'=>['required' ],
                'answer'=>['required' ],
            ]);
        if ($validator->fails()) {
            return response(['status'=>'error','messages'=>$validator->messages()->all()]);
        }
       $question=$service->askedQuestions()->create([
           'question'=>$request->question,
           'answer'=>$request->answer
       ]);
        return response(['status'=>'success','data'=>['type'=>'asked_question','data'=>$question],'messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }

    /**
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function updateSingleQuestion(Request $request, Service $service)
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

        AskedQuestion::where('id',$request->asked_question_id)->where('askedquestionable_type',Service::class)
            ->where('askedquestionable_id',$service->id)->update([
            'question'=>$request->question,
            'answer'=>$request->answer
        ]);
        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }

    /**
     * delete single
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function deleteSingleQuestion(Request $request, Service $service)
    {
        AskedQuestion::where('id',$request->asked_question_id)->where('askedquestionable_type',Service::class)
            ->where('askedquestionable_id',$service->id)->delete();
        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }

    /**
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function insertSessionTitles(Request $request, Service $service)
    {
        if (!is_null($service->session_attributes) && is_array($service->session_attributes)){

           $session_attributes=$service->session_attributes;
           $session_attributes['session_titles']=$request->session_titles;
            $service->update(array(
                'session_attributes'=>$session_attributes
            ));
        }else{
            $service->update(array(
                'session_attributes'=>array('session_titles'=>$request->session_titles)
            ));
        }
        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }

    private function updateRules(Request $request, Service $service)
    {
        $service->update(array(
            'rules'=>$request->rules,
        ));
        return response(['status'=>'success','messages'=>array('ثبت تغییرات با موفقیت انجام گرفت !')]);
    }

    /**
     * @param Request $request
     * @param Service $service
     * @return void
     */
    private function uploadSliders(Request $request, Service $service): void
    {
        //upload desktop slider
        if ($request->file('desktop_slider')) {
            $desktop_slider = File::where('title', 'desktop_slider')->where('fileable_type', Service::class)->where('fileable_id', $service->id)->first();

            if (!is_null($desktop_slider)) {
                Helper::deleteImage(public_path($desktop_slider->path));
                $desktop_slider->delete();
            }
            $this->uploadDesktopSliderImage($service, $request->file('desktop_slider'));
        }   //upload mobile slider
        if ($request->file('mobile_slider')) {
            $mobile_slider = File::where('title', 'mobile_slider')->where('fileable_type', Service::class)->where('fileable_id', $service->id)->first();

            if (!is_null($mobile_slider)) {
                Helper::deleteImage(public_path($mobile_slider->path));
                $mobile_slider->delete();
            }
            $this->uploadMobileSliderImage($service, $request->file('mobile_slider'));
        }
    }

    private function uploadDesktopSliderImage(Service $service, array|\Illuminate\Http\UploadedFile|null $file)
    {
        $url_front_image = Helper::uploadImage($file, 'service');
        return $service->files()->create(['path' => $url_front_image, 'title' => 'desktop_slider']);
    }

    private function uploadMobileSliderImage(Service $service, array|\Illuminate\Http\UploadedFile|null $file)
    {
        $url_front_image = Helper::uploadImage($file, 'service');
        return $service->files()->create(['path' => $url_front_image, 'title' => 'mobile_slider']);
    }
}
