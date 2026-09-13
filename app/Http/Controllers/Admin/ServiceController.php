<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;
use App\Http\Resources\ServiceCollection;
use App\Http\Resources\ServiceItemCollection;
use App\Models\AskedQuestion;
use App\Models\BeforeForm;
use App\Models\Category;
use App\Models\Gallery;
use App\Models\MediaLibrary;
use App\Models\Menu;
use App\Models\Service;
use App\Models\WorkSample;
use App\Services\V1\Admin\SeoContentService;
use App\Services\V1\Auth\AdminAuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ServiceController extends AdminController
{
    public function __construct(
        private SeoContentService $seoService
    ) {}

    /**
     * Summary of index
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        // $columns=['title','pay_type','purchase_type'];

        // foreach ($columns as $column) {
        //     if($request->has($column)){
        //         if ($request->$column != null) {
        //             if ($column == 'title') {
        //                 $services = $services->where($column, 'like', '%' . $request->$column . '%');
        //                 $queries[$column] = $request->$column;
        //             }
        //         }
        //         if ($column == 'pay_type' || $column == 'purchase_type' ) {
        //             if($request->$column != 'all'){
        //                 $services = $services->where($column, 'like', '%' . $request->$column . '%');
        //                 $queries[$column]=$request->$column;
        //             }
        //             if($request->$column == 'all'){
        //                 $queries[$column]='all';
        //             }
        //         }
        //     }
        // }

        $pay_type = [0 => 'نقدی', 1 => 'اقساطی',2 => 'هر دو'];
        $purchase_type = [ 0 => 'آنلاین', 1=>'حضوری',2=>'هر دو'];
        $delete = auth()->user()->isAbleTo( 'service-delete' );
        return view('Admin.service.all', compact('pay_type','purchase_type','delete'));
    }

    /**
     * @return ServiceCollection
     */
    public function filter()
    {
        $services = Service::filter()->latest();

        return new ServiceCollection($services->paginate(20));
    }

    public function getItems($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json([], 404);
        }

        $items = $service->activeServiceItems($service);
        return response()->json($items);
    }
    /**
     * Summary of create
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $service_categories = Category::filter()->where('parent_id',2)->select('id','title','parent_id')
                ->orderBy('id', 'DESC')->get();
        $all_categories = Category::filter()->select('id','title')->get();
        $all_services = Service::filter()->select('id','title')->get();

        // ویزارد ایجاد محصول دسته‌بندی مرحله قبل را می‌فرستد تا از پیش انتخاب شود.
        // دسته‌بندی‌های عمیق‌تر از سطح یک در فهرست بالا نیستند، پس اضافه‌شان می‌کنیم.
        $in_wizard = (bool) request('wizard');
        $wizard_category = request('wizard_category')
            ? Category::select('id','title','parent_id')->find(request('wizard_category'))
            : null;
        if ($wizard_category && ! $service_categories->contains('id', $wizard_category->id)) {
            $service_categories->prepend($wizard_category);
        }

        return view('Admin.service.create',['all_categories'=>$all_categories,
            'all_services'=>$all_services,'service_categories'=>$service_categories,
            'in_wizard'=>$in_wizard,'wizard_category'=>$wizard_category]);
    }

    /**
     * Summary of store
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'title' => 'required|string',
            'en_title' => 'required',
            'price' => 'required',
            'category_id'=>'required',
            'status'=>array('required',Rule::in(0,1)),
            'isShow'=>array('required',Rule::in(0,1)),
            'items'=>array('required',Rule::in(0,1)),
            'center'=>array('required',Rule::in(0,1)),
            'multiple'=>array('required',Rule::in(0,1)),
            'ability_invite_friend'=>array('required',Rule::in(0,1)),
         ]);

        if($request->price > 0 )$request['price'] = str_replace(",","",$request->price);
        $request['ability_invite_friend'] = isset($request['ability_invite_friend']) ? 1 : 0;

        $ghorfe_online = ghorfe();
        // legacy media columns are never mass-assigned — see AdoptsLegacyMedia::exceptLegacyMediaKeys()
        $service = Service::create((new Service)->exceptLegacyMediaKeys($request->all()));

        if ($ghorfe_online) {
            $ghorfe_online->services()->attach($service->id);
        }
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


        // ویزارد ایجاد محصول: خدمت تنوع ندارد، پس مستقیم به مرحله پایانی می‌رویم.
        if ($request->wizard) {
            return redirect(route('productWizard.done', [
                'kind' => 'service',
                'category_id' => $request->category_id,
            ]))->with(['create' => 'success']);
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
        $all_categories = Category::filter()->select('id','title')->get();
        $all_services = Service::filter()->select('id','title')->where('id','!=',$service->id)->get();
        $similar_services=$service->similarGroups()->where('similar_model',Service::class)->where('relation_model',Service::class)->pluck('relation_id')->toArray();
        $similar_categories=$service->similarGroupCategories()->where('similar_model',Service::class)->where('relation_model',Category::class)->pluck('relation_id')->toArray();
        $service_categories=[];
        $service_categories = Category::filter()->where('parent_id',2)->select('id','title','parent_id')->get();
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
            case "seo_content":
                 return $this->updateSeoContent($request,$service);
        }
    }

    public function destroy(Service $service)
    {
        $service->deleted_at = Carbon::now();
        $service->update();
        return response()->json([
            'status'  => 'success',
            'page'  => 1
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
            $id = is_array($request->input('id')) ? $request->input('id') : [$request->input('id')];
            if (Service::filter()->where('status' , true)->whereIn('category_id', $id)->get()->count() > 0) {
                $services = Service::filter()->where('status' , true)->whereIn('category_id', $id)->get();
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
            'status'=>array('required',Rule::in(1,0)),
            'isShow'=>array('required',Rule::in(0,1)),
            'items'=>array('required',Rule::in(0,1)),
            'center'=>array('required',Rule::in(0,1)),
            'multiple'=>array('required',Rule::in(0,1)),
            'ability_invite_friend'=>array('required',Rule::in(0,1)),
            'price'=>['required'],
            ]);
        $request['price'] = (float) str_replace(',', '', $request['price']);
        if ($validator->fails()) {
            return response(['status'=>'error','messages'=>$validator->messages()->all()]);
        }
        $service->update([
            'title'=>$request->title,
            'en_title'=>$request->en_title,
            'description'=>$request->description,
            'status'=>$request->status,
            'isShow'=>$request->isShow,
            'items'=>$request->items,
            'center'=>$request->center,
            'multiple'=>$request->multiple,
            'session'=> $request->has('is_session') ? 1 : 0,
            'ability_invite_friend'=>$request->ability_invite_friend,
            'price'=>$request->price,
            'gift'=>$request->gift,
        ]);
        return response(['status'=>'success','messages'=>['ثبت تغییرات با موفقیت انجام گرفت !']]);
    }

    /**
     * update text information of service
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function updateTextInformation(Request $request, Service $service)
    {
          // Persist only the text fields actually submitted. The ghorfe متن form sends just
          // `content`; writing the others unconditionally would null out txt_box_1/left_content.
          $fields = [];
          foreach (['content', 'txt_box_1', 'left_content' , 'is_complete_content'] as $f) {
              if (!is_null($request->input($f))) {
                  $fields[$f] = $request->input($f);
              }
          }
          $service->update($fields);
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
            ]);
        if ($validator->fails()) {
            return response(['status'=>'error','messages'=>$validator->messages()->all()]);
        }

        $service->update(array(
              'category_id'=>$request->category_id,
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
     * Summary of uploadFirstImages
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Http\Response
     */
    private function uploadFirstImages(Request $request, Service $service)
    {
        $request->validate(
            [
                'thumb' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                'image' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                'icon' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                'img_box_1' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                // 'desktop_slider' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                // 'mobile_slider' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
                'image_app' => 'nullable|mimes:jpg,jpeg,png,svg,gif',
            ]);


        // Touch-to-migrate: this handler never calls $service->save(), so the model's `saved`
        // event (which normally runs adoption) does not fire here. Run it explicitly, BEFORE
        // attaching new uploads, so a legacy image is migrated rather than silently skipped.
        $service->adoptLegacyMedia();

        foreach (Service::MEDIA_FIELDS as $key) {
            // attach the temporary ajax upload into the service's own media collection (Spatie)
            MediaLibraryHelper::attachTo($service, $request->input($key . '_path'), $key);
        }
        $this->uploadSliders($request, $service);

        $service->load('media');
        $singles = [];
        foreach (Service::MEDIA_FIELDS as $key) {
            $singles[$key] = $this->singleMediaInfo($service, $key);
        }

        return response([
            'status'   => 'success',
            'messages' => array('ثبت تغییرات با موفقیت انجام گرفت !'),
            'media'    => ['singles' => $singles],
        ]);
    }

    /**
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function uploadPortfolioImages(Request $request, Service $service)
    {

        // Touch-to-migrate: see the note in uploadFirstImages(). Run before the new uploads are
        // attached — adoption skips a collection that already has media, so attaching first
        // would let one new image block every pending legacy work sample from ever migrating.
        $service->adoptLegacyMedia();

        // update titles of already-saved portfolio images
        foreach ($request->existing_portfolio ?? [] as $mediaId => $data) {
            $media = \App\Models\Media::find($mediaId);
            if ($media && $media->model_type === Service::class && (string) $media->model_id === (string) $service->id) {
                $media->setCustomProperty('title', $data['title'] ?? '');
                $media->save();
            }
        }

        // attach newly uploaded images into the service's portfolio collection (Spatie)
        foreach ($request->multiple_image ?? [] as $item) {
            if (!empty($item['file'])) {
                MediaLibraryHelper::attachTo($service, $item['file'], 'portfolio', [
                    'title' => $item['title'] ?? '',
                ]);
            }
        }

        $service->load('media');
        return response([
            'status'   => 'success',
            'messages' => array('ثبت تغییرات با موفقیت انجام گرفت !'),
            'media'    => ['lists' => [
                'portfolio' => $this->mediaListInfo($service, 'portfolio'),
            ]],
        ]);
    }

    /** Ordered media info for an unlimited "list" collection (portfolio…). */
    private function mediaListInfo(Service $service, string $collection): array
    {
        return $service->getMedia($collection)->map(fn ($media) => [
            'id'    => $media->id,
            'url'   => $media->getFullUrl(),
            'title' => $media->getCustomProperty('title'),
        ])->values()->all();
    }

    /**
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private function uploadSiteImages(Request $request, Service $service)
    {
        // Touch-to-migrate: see the note in uploadFirstImages(). Run before syncing the slots so
        // the legacy `galleries` rows fill their slots first; a slot the admin re-uploaded is
        // then replaced below by syncGallerySlots(), which only touches submitted slots.
        $service->adoptLegacyMedia();

        $this->syncGallerySlots($service, 'gallery_horizontal', $request->horizontalImages ?? []);
        $this->syncGallerySlots($service, 'gallery_vertical', $request->verticalImages ?? []);

        $service->load('media');
        return response([
            'status'   => 'success',
            'messages' => array('ثبت تغییرات با موفقیت انجام گرفت !'),
            'media'    => ['slots' => [
                'horizontalImages' => $this->gallerySlotsInfo($service, 'gallery_horizontal'),
                'verticalImages'   => $this->gallerySlotsInfo($service, 'gallery_vertical'),
            ]],
        ]);
    }

    /** Media info for a single-file collection (image/icon/thumb…), or null. */
    private function singleMediaInfo(Service $service, string $collection): ?array
    {
        $media = $service->getFirstMedia($collection);
        return $media ? ['id' => $media->id, 'url' => $media->getFullUrl()] : null;
    }

    /** Media info keyed by slot index for a gallery collection. */
    private function gallerySlotsInfo(Service $service, string $collection): array
    {
        $slots = [];
        foreach ($service->getMedia($collection) as $media) {
            $slots[(int) $media->getCustomProperty('index')] = [
                'id'    => $media->id,
                'url'   => $media->getFullUrl(),
                'title' => $media->getCustomProperty('title'),
            ];
        }
        return $slots;
    }

    /**
     * Attach/replace/rename the images of a slot-based Spatie gallery collection.
     */
    private function syncGallerySlots(Service $service, string $collection, array $items)
    {
        foreach ($items as $index => $item) {
            $existing = $service->gallerySlot($collection, (int) $index);

            if (!empty($item['file'])) {
                // new upload for this slot -> attachTo() replaces the media in this slot
                // (matched by the 'index' custom property) and deletes the old file natively.
                MediaLibraryHelper::attachTo($service, $item['file'], $collection, [
                    'index' => (int) $index,
                    'title' => $item['file_title'] ?? '',
                ]);
            } elseif ($existing && array_key_exists('file_title', $item)) {
                // title-only update
                $existing->setCustomProperty('title', $item['file_title'] ?? '');
                $existing->save();
            }
        }
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

    private function updateSeoContent(Request $request, Service $service)
    {
        $this->seoService->save($service, $request->only([
            'seo_title',
            'meta_description',
            'robots',
            'canonical_url',
            'redirect_from',
            'redirect_to',
            'redirect_enabled',
            'og_title',
            'og_description',
            'og_site_name',
        ]));

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
        // if ($request->file('desktop_slider')) {
        //     $desktop_slider = File::where('title', 'desktop_slider')->where('fileable_type', Service::class)->where('fileable_id', $service->id)->first();

        //     if (!is_null($desktop_slider)) {
        //         Helper::deleteImage(public_path($desktop_slider->path));
        //         $desktop_slider->delete();
        //     }
        //     $this->uploadDesktopSliderImage($service, $request->file('desktop_slider'));
        // }   //upload mobile slider
        // if ($request->file('mobile_slider')) {
        //     $mobile_slider = File::where('title', 'mobile_slider')->where('fileable_type', Service::class)->where('fileable_id', $service->id)->first();

        //     if (!is_null($mobile_slider)) {
        //         Helper::deleteImage(public_path($mobile_slider->path));
        //         $mobile_slider->delete();
        //     }
        //     $this->uploadMobileSliderImage($service, $request->file('mobile_slider'));
        // }
    }
    /**
     * @param \App\Models\Service $service
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function getIndexTariffs(Service $service)
    {
        $delete = auth()->user()->isAbleTo( 'service_index_tariff_delete' );
        $service_index_tariffs = $service->serviceIndexTariffs;
        return view('Admin.service.indexTariffs.all', compact('delete','service_index_tariffs' , 'service'));
    }
    public function getFinancialPlansType($id)
    {
        $title = 'فهرست طرح های مالی';
        return view('Admin.service.financialPlansType.index', compact('title','id'));
    }
}
