<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Resources\CenterCollection;
use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\Center;
use App\Models\CenterRequest;
use App\Models\FinancialPlansType;
use App\Models\GhorfeOnlineList;
use App\Models\Product;
use App\Models\Rating;
use App\Models\Review;
use App\Models\Service;
use App\Models\Tag;
use App\Models\User;
use App\Models\Validation;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;

class CenterController extends AdminController
{
    public function index() {
        $user = auth()->user();
        if($user->isAbleTo('center'))
        {
            $delete = $user->isAbleTo('delete_center');

            $edit = $user->isAbleTo('edit-center');
            $financial_report_detail = $user->isAbleTo('center_financial_report_detail');
            return view( 'Admin.center.all', compact( 'delete' , 'edit' , 'financial_report_detail' ) );
        }
        return abort('401');
    }
    /**
     * @param Request $request
     * @return CenterCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter(Request $request)
    {
        $centers = Center::filter()->latest();
        return new CenterCollection($centers->paginate(20));
    }
    /**
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function create( Request $request ) {
        // Set when the form was opened from a business's "درباره ما" branch tab — the new
        // branch has to end up on THAT business, and a لاله admin arriving from there has no
        // ghorfe of their own for createCenter() to infer it from.
        return view( 'Admin.center.create', [
            'ghorfeOnlineListId' => $request->input( 'ghorfe_online_list_id' ),
        ] );
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function store( Request $request ) {

        $this->validate( request(), [
            'title' => 'required|string',
            'address' => 'required',
        ]);

        //create centerRequest
        $item = $request->item;
        if ( $request->cat_type == 'category' ) {
            $center_category_id = $item['product_category'];
            $centerable_id = $request->category;
            $centerable_type = Category::class;
        } else {
            $center_category_id = $item['service_category'];
            $centerable_id = $request->service;
            $centerable_type = Service::class;
        }

        $item['center_category_id'] = $center_category_id;
        unset($item['product_category']);
        unset($item['service_category']);

        $this->validateBranchProfile($request);

        $center_request = CenterRequest::create($item);
        //create center
        $center = $this->createCenter($centerable_id, $centerable_type, $request, $center_request);
        //create user

        //create task (callCenter)

        // Straight on to the branch page tabs: the create form only collects the contract
        // side, and everything the frontend renders (hours, plans, faq, images) is edited there.
        return redirect( route( 'center.edit', $center->id ) )->with( [
            'create' => 'success',
        ] );
    }
    /**
     * @param \App\Models\Center $center
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function edit( Center $center ) {
        if(auth()->user()->isAbleTo('edit-center'))
        {
        // center_request_id is nullable and the request can be deleted independently, so
        // the view must never dereference this blindly — a fresh model keeps every
        // `$center_request->x` read working (and empty) instead of fataling on null.
        $center_request = $center->center_request ?: new CenterRequest();

        return view( 'Admin.center.edit', [
            'center'                => $center,
            'center_request'        => $center_request,
            // "طرح های مالی فعال برای این شعبه" — offered plans and the ones already picked
            'financial_plans'       => FinancialPlansType::where('status', 1)->orderBy('title')->get(),
            'selectedFinancialPlans'=> $center->financialPlansTypes->pluck('id')->all(),
            // read-only: staff belong to the branch through employees.center_id, which is
            // set on the employee's own page, not here
            'branchEmployees'       => $center->employees()->with('user:id,name,family')->get(),
            'askedQuestions'        => $center->askedQuestions()->get(),
            'ratings'               => $center->ratings()->latest()->get(),
        ] );
        }
        return abort('401');
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Center $center
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update( Request $request, Center $center ) {
        if(auth()->user()->admin_representation)
        {
            return redirect()->route('center.index')->with([
                'failByMessage' => 'امکان ویرایش برای شما وجود ندارد.',
            ]);
        }
        $item = $request->item;
        // The category selects use a `<option selected disabled>` placeholder, so when the
        // center's saved category matches no option (or the list is empty) the browser
        // submits no value and the key is absent -> keep the existing category instead of
        // crashing on an undefined array key.
        $existingCategory = optional($center->center_request)->center_category_id;
        if ( $request->cat_type == 'category' ) {
            $center_category_id = !empty($item['product_category']) ? $item['product_category'] : $existingCategory;
            $centerable_id = $request->category;
            $centerable_type = Category::class;
        } else {
            $center_category_id = !empty($item['service_category']) ? $item['service_category'] : $existingCategory;
            $centerable_id = $request->service;
            $centerable_type = Service::class;
        }

        $item['center_category_id'] = $center_category_id;
        unset($item['product_category']);
        unset($item['service_category']);
        unset($item['contract_file']);
        // Spatie: the ajax upload now returns a temp media id in `contract_file_path`.
        // Attach it to the center's single-file `contract_file` collection (replaces any
        // previous file). The legacy `contract_file` column is left untouched.
        if ($request->contract_file_path) {
            \App\Helpers\MediaLibraryHelper::attachTo($center, $request->contract_file_path, 'contract_file');
        }
        unset($item['urlcontract_file'], $item['contract_file_path']);
        // center_request_id is nullable — a center without a request must still be editable
        // (edit() already hands the view a blank stand-in for exactly this case).
        if ($center->center_request) {
            $center->center_request->update($item);
        }

        $this->validateBranchProfile($request);

        $center->update( [
            'centerable_id' => $centerable_id,
            'centerable_type' => $centerable_type,
            'creator_representation_id' => $request->creator_representation_id,
            'title' => $request->title,
            'status' => $request->status,
            'description' => $request->description,
            'address' => $request->address,
            'phone' => Helper::numberConverter($request->phone),
            'contract_number' => $request->contract_number,
            // vertaToMiladi() throws "Unknown datetime ''" on an empty value, and the date
            // is optional — a center with no contract date must still be savable.
            'contract_start_date' => filled($request->contract_start_date)
                ? Helper::vertaToMiladi($request->contract_start_date)
                : null,
            'is_show' => isset($request->is_show) ? $request->is_show : 0,
            // public branch page
            'label_type'     => in_array($request->label_type, array_keys(Center::LABELS), true) ? $request->label_type : 'branch',
            'intro_text'     => $request->intro_text,
            'email'          => $request->email,
            'map_iframe_link'=> $request->map_iframe_link,
            'lat'            => Helper::convert($request->lat) ?: null,
            'lon'            => Helper::convert($request->lon) ?: null,
            'working_hours'  => $this->normalizeWorkingHours($request->input('working_hours', [])),
            'about_title'    => $request->about_title,
            'services_title' => $request->services_title,
            'staff_title'    => $request->staff_title,
            'plans_title'    => $request->plans_title,
        ] );
        !auth()->user()->admin_representation_id ? $center->representations()->sync($request->input('representation_id')) : '';

        $center->services()->sync($request->services);

        // Branch page images. Same ajax pipeline as contract_file: the hidden {field}_path
        // holds a temp media id that attachTo moves into the single-file collection.
        foreach (['image', 'background_image'] as $collection) {
            if ($request->input($collection . '_path')) {
                \App\Helpers\MediaLibraryHelper::attachTo($center, $request->input($collection . '_path'), $collection);
            }
        }

        // The posted list IS the list, so unticking a plan has to detach it.
        $center->financialPlansTypes()->sync($request->input('financial_plan_ids', []));

        $this->syncAskedQuestions($center, $request);
        $this->moderateRatings($center, $request);

        return redirect( route( 'center.edit', $center->id ) )->with( [
            'update'     => 'success',
            'active_tab' => $request->input('active_tab', 'base_info'),
        ] );
    }

    /**
     * Rules for the fields the branch FRONTEND reads. Kept apart from the contract-side
     * fields above, which validate (or don't) exactly as they always have — this must not
     * start rejecting saves that used to go through.
     */
    private function validateBranchProfile(Request $request): void
    {
        $request->validate([
            'label_type'      => 'nullable|in:' . implode(',', array_keys(Center::LABELS)),
            'intro_text'      => 'nullable|string|max:5000',
            'email'           => 'nullable|email|max:191',
            'map_iframe_link' => 'nullable|string|max:2000',
            'lat'             => 'nullable|numeric|between:-90,90',
            'lon'             => 'nullable|numeric|between:-180,180',
            'about_title'     => 'nullable|string|max:255',
            'services_title'  => 'nullable|string|max:255',
            'staff_title'     => 'nullable|string|max:255',
            'plans_title'     => 'nullable|string|max:255',
            'financial_plan_ids'   => 'nullable|array',
            'financial_plan_ids.*' => 'integer|exists:financial_plans_types,id',
            'asked_question_questions'   => 'nullable|array',
            'asked_question_questions.*' => 'nullable|string|max:1000',
            'asked_question_answers'     => 'nullable|array',
            'asked_question_answers.*'   => 'nullable|string|max:5000',
        ], [
            'email.email'   => 'ایمیل شعبه معتبر نیست',
            'lat.numeric'   => 'عرض جغرافیایی باید عدد باشد',
            'lon.numeric'   => 'طول جغرافیایی باید عدد باشد',
        ]);
    }

    /**
     * Opening hours, one entry per weekday, always the same shape.
     *
     * A day the admin never touched still gets a row, so the frontend can render the whole
     * week without guarding every lookup. Ticking "تعطیل" clears the two times rather than
     * keeping stale ones behind the closed flag.
     */
    private function normalizeWorkingHours(array $input): array
    {
        $hours = [];

        foreach (array_keys(Center::WEEK_DAYS) as $day) {
            $row    = is_array($input[$day] ?? null) ? $input[$day] : [];
            $closed = ! empty($row['closed']);

            $hours[$day] = [
                'open'   => $closed ? '' : trim(Helper::convert($row['open'] ?? '')),
                'close'  => $closed ? '' : trim(Helper::convert($row['close'] ?? '')),
                'closed' => $closed,
            ];
        }

        return $hours;
    }

    /**
     * The branch "سوالات متداول" repeater: create / update / delete in one pass.
     *
     * Rows live in `asked_questions` under the center's own morph, which is what separates
     * them from the business-wide ones the "درباره ما" page manages (those are `faqs` rows
     * carrying ghorfe_online_list_id).
     */
    private function syncAskedQuestions(Center $center, Request $request): void
    {
        $ids       = $request->input('asked_question_ids', []);
        $questions = $request->input('asked_question_questions', []);
        $answers   = $request->input('asked_question_answers', []);

        $kept = [];

        foreach ($questions as $index => $question) {
            $question = trim((string) $question);

            // an empty repeater row is not a question
            if ($question === '') {
                continue;
            }

            // `answer` is NOT NULL in the schema, so an unanswered row stores an empty string
            $attributes = ['question' => $question, 'answer' => trim((string) ($answers[$index] ?? ''))];
            $id         = $ids[$index] ?? null;

            // re-fetched through the center: a posted id must not reach another branch's row
            $asked = $id ? $center->askedQuestions()->find($id) : null;

            if ($asked) {
                $asked->update($attributes);
            } else {
                $asked = $center->askedQuestions()->create($attributes);
            }

            $kept[] = $asked->id;
        }

        // soft delete — AskedQuestion uses SoftDeletes, so a removed row stays recoverable
        $center->askedQuestions()->whereNotIn('id', $kept ?: [0])->delete();
    }

    /**
     * Approve / reject / delete visitor ratings.
     *
     * Ratings are written by the public API, never here — the admin only decides what the
     * frontend may show, so this handles no creation.
     */
    private function moderateRatings(Center $center, Request $request): void
    {
        foreach ($request->input('rating_status', []) as $id => $status) {
            if (! array_key_exists((int) $status, Rating::STATUS)) {
                continue;
            }
            // scoped to this center so a posted id can't flip another subject's rating
            $center->ratings()->where('id', $id)->update(['status' => (int) $status]);
        }

        if ($deleted = array_filter($request->input('rating_delete', []))) {
            $center->ratings()->whereIn('id', $deleted)->delete();
        }
    }
    /**
     * @param \App\Models\Center $center
     * @param \App\Models\Service $service
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function serviceTariffs(Center $center , Service $service) {
        $tariffs = $service->tariffs;
        $access_selling_price = false;
        if (auth()->user()->isAbleTo('service_tariff_selling_price')){
            $access_selling_price = true;
        }

        return view( 'Admin.center.serviceTariffList', compact('access_selling_price','center', 'service' ,'tariffs') );
    }
    /**
     * @param \App\Models\Center $center
     * @param \App\Models\Service $service
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
    */
    public function createServiceTariff(Center $center,Service $service)
    {
        $tariffs = $service->tariffs;
        $employees = $center->users->where('level' , 'admin');
        $service_index_tariffs = $service->serviceIndexTariffs;

        return view('Admin.center.serviceTariffCreate' , compact(['service_index_tariffs','employees' ,'center' , 'tariffs','service']));
    }
    /**
     * @param \App\Models\Center $center
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Center $center)
    {
        $center->delete();
        return redirect()->back()->with([
            'delete' => 'success'
        ]);
    }
    /**
     * @param mixed $center_id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function doctorsByCenter($center_id)
    {
        $users = User::with('roles')->whereHas('roles', function ($query) {
            $query->where('roles.name', 'doctor');
        })->where('level', 'admin')->where('center_id', $center_id)->paginate(15);
        return view('Admin.center.doctorsByCenter', ['users' => $users]);
    }
    /**
     * @param mixed $id
     * @param \Illuminate\Http\Request $request
     * @return string|\Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     * list of product centers
     */
    public function productsByCenterId($id, Request $request)
    {
        $products = Product::with('centers')->whereHas('centers', function ($query) use ($id) {
            $query->where('center_id', $id);
        })->select('id', 'name', 'image', 'type', 'isShow', 'root_category_id', 'internal_use', 'brand_id', 'status')
            ->orderBy('id', 'DESC')->where('type', 1);

        $categories = Category::categoryDepth($id);


        $columns = ['id', 'name', 'root_category_id', 'status', 'brand_id'];
        $queries = [];

        foreach ($columns as $column) {
            if ($request->has($column)) {
                if ($request->$column != null) {
                    if ($column == 'id' || $column == 'status') {
                        $products = $products->where($column, $request->$column);
                        $queries[$column] = $request->$column;
                    }
                    if ($column == 'root_category_id' || $column == 'brand_id') {
                        if ($request->$column != 'all') {
                            $products = $products->where($column, $request->$column);
                            $queries[$column] = $request->$column;
                        } else {
                            $queries[$column] = 'all';
                        }
                    }
                    if ($column == 'name' || $column == 'en_name') {
                        $products = $products->where($column, 'like', '%' . $request->$column . '%');
                        $queries[$column] = $request->$column;
                    }
                }

            }
        }
        $brands = [];
        $product_brands = $products->with(['brand'])->get();

        $products = $products->paginate(15)->appends($queries);

        foreach ($product_brands as $item) {
            if (isset($item->brand->title)) $brands[$item->brand->id] = $item->brand->title;
        }

        $brands = array_unique($brands);

        if ($request->ajax()) {
            return view('Admin.center.products.load', ['products' => $products, 'type_id' => 1])->render();
        }
        $page_link = route('productsByCenterId', $id);
        $create_link = route('createGoodByValues', ['type' => 'center', 'id' => $id]);

        return view('Admin.center.products.good', ['products' => $products, 'type_id' => 1
            , 'categories' => $categories, 'queries' => $queries, 'center_id' => $id, 'brands' => $brands,
           'create_link' => $create_link, 'page_link' => $page_link]);
    }
    /**
     * @param mixed $id
     * @param mixed $center_id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function productEdit($id, $center_id)
    {
        $product = Product::find($id);

        $center = Center::find($center_id);
        $category_center_id = $center->centerable_id;
        $category_center = Category::where('parent_id', $category_center_id)->get();
        $category = Category::find($category_center_id);
        // default so compact() below always has it, even when the category (or its parent)
        // can't be resolved — mirrors ProductController@edit.
        $parent_category_id = '';
        if ($category) {
            $parent_category = Category::where('id', $category->parent_id)->select('id')->first();
            if ($parent_category) {
                $parent_category_id = $parent_category->id;
            }
        }

        $reviews_strength = $product->reviews()->where('reviewable_type', Center::class)
            ->where('reviewable_id', $center_id)->where('type', 0)->get();
        $reviews_weakness = $product->reviews()->where('reviewable_type', Center::class)
            ->where('reviewable_id', $center_id)->where('type', 1)->get();
        $categories_product = $product->categories()->orderBy('category_product.id', 'ASC')->get();


        $parents = Category::where('parent_id', 1)->get();
        $tags = Tag::pluck('name');
        $centers = Center::select('id', 'title')
            ->where('centerable_type', Category::class)->get();
        $product_centers_ids = $product->centers()->pluck('centers.id')->toArray();

        $last_category_id = Category::latestCategory($product);

        $attributeGroups = AttributeGroup::where('category_id', $last_category_id)->get();
        $attributes = [];
        if (isset($attributeGroups) && count($attributeGroups) > 0) {
            foreach ($attributeGroups as $item) {
                $attributes[$item->id] = $item->attributes()->where('is_price', 0)->get();
            }
        }
        $product_attributes = $product->attributes()
            ->where('productattributeable_id', $center_id)->where('productattributeable_type', Center::class)
            ->where('is_price', 0)->get();
        // $last_category_id may not resolve to a Category, so guard the ->brands read.
        $brands = Category::find($last_category_id);
        if (!is_null($brands)) {
            $brands = $brands->brands;
        } else {
            $brands = array();
        }
        $product_validation = $product->productValidations;
        $validations = Validation::all();

        return view('Admin.center.products.edit', compact('reviews_strength',
            'reviews_weakness', 'categories_product', 'parents', 'tags', 'product', 'center_id', 'centers',
            'parent_category_id', 'category_center_id', 'category_center', 'product_centers_ids',
            'product_attributes', 'brands', 'product_validation', 'validations', 'attributes'));
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @param mixed $id
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function productUpdate(Request $request, $id)
    {
        $request->validate(['good.name' => 'required'], ['good.name.required' => 'لطفا عنوان را وارد کنید']);
        $product = Product::find($id);
        //user is not store manager
        if (User::checkManager() == null) {
            $request['reference_price'] = str_replace(',', '', $request->reference_price);
            $request['isShow'] = isset($request['isShow']) ? $request['isShow'] : 0;
            $request['is_sale'] = isset($request['is_sale']) ? $request['is_sale'] : 0;
            $request['internal_use'] = isset($request['internal_use']) ? $request['internal_use'] : 0;

            //admin:The edited product goes into product-edit review mode
            if ($product->status == 4) $request['status'] = 6;
        }

        if (User::checkManager() != null) {
            //manager : The product is confirmed and edited
            if ($product->status == 5) $request['status'] = 4;
        }

        $productattributeable_id = 0;
        $productattributeable_type = '';

        if (isset($request->center_id)) {
            $productattributeable_id = $request->center_id;
            $productattributeable_type = Center::class;
        }

        Product::saveImageGood($request, $product);
        Product::updateAttributeGood($request, $product, $productattributeable_id, $productattributeable_type);

        if ($request->good['name'] != null)
            $request['slug'] = SlugService::createSlug(Product::class, 'slug', $request->good['name']);

        //category
        if (isset($request->choose_category_id)) {
            $all_father = Category::findAllFather($request->choose_category_id);
            array_push($all_father, $request->choose_category_id);
            $request->category_id = $all_father;
            $request['root_category_id'] = $request->category_id[2];

            foreach ($product->categories()->get() as $cat_pro) {
                $cat_pro->pivot->delete();
                if (!isset($request->good['brand_id'])) {
                    $product->brand_id = null;
                    $product->save();
                }
            }
            $product->categories()->attach($request->category_id);
        }

        if (isset($request->good['brand_id'])) $request['brand_id'] = $request->good['brand_id'];

        $product->update(['name' => $request->good['name'], 'en_name' => $request->good['en_name'],
            'is_sale' => $request->is_sale, 'isShow' => $request->isShow, 'internal_use' => $request->internal_use,
            'length' => $request->good['length'], 'width' => $request->good['width'], 'height' => $request->good['height'],
            'weight' => $request->good['weight'], 'description' => $request->good['description'], 'root_category_id' => $request['root_category_id']
        ]);

        //tag
//        if (isset($request->tag)) {
//            $request_tags = $request->tag;
//            $list_tags_product = $product->tagNames();
//
//            Product::UpdateTag($list_tags_product, $request_tags, $product);
//        }

        if (isset($request->image_path) || isset($request->multiple_image)) {
            Product::storeImagesProduct($request->image_path, $request->multiple_image, $product);
        }

        //reviewStrengthOld
        if (isset($request->reviewStrengthOld)) {
            foreach ($request->reviewStrengthOld as $index => $value) {
                $review = Review::find($index);
                $review->update(['text' => $value['text']]);
            }
        }

        //reviewWeaknessOld
        if (isset($request->reviewWeaknessOld)) {
            foreach ($request->reviewWeaknessOld as $index => $value) {
                $review = Review::find($index);
                $review->update(['text' => $value['text']]);
            }
        }

        return redirect(route('productsByCenterId',$request->center_id))->with(['update' => 'success']);
    }
    /**
     * @return array{centers: mixed}
     */
    public function getCenterCategory()
    {
        $centers = Center::select('id', 'title')->where('title', '!=', null)
            ->where('centerable_type', Category::class)->get();
        return ['centers' => $centers];
    }

    /**
     * @param mixed $centerable_id
     * @param string $centerable_type
     * @param Request $request
     * @param $center_request
     * @return void
     */
    public function createCenter(mixed $centerable_id, string $centerable_type, Request $request, $center_request): Center
    {
        $center = Center::create([
            'status' => $request->status,
            'is_show' => $request->is_show,
            'creator_representation_id' => auth()->user()->admin_representation_id,
            'center_request_id' => $center_request->id,
            'title' => $request->title,
            'description' => $request->description,
            'address' => $request->address,
            'phone' => Helper::numberConverter($request->phone),
            'contract_number' => $request->contract_number,
            // optional, and vertaToMiladi() throws on '' (see update())
            'contract_start_date' => filled($request->contract_start_date)
                ? Helper::vertaToMiladi($request->contract_start_date)
                : null,
            // public branch page — the rest (hours, plans, faq, images) is filled in on edit
            'label_type' => in_array($request->label_type, array_keys(Center::LABELS), true) ? $request->label_type : 'branch',
            'intro_text' => $request->intro_text,
            'email'      => $request->email,
        ]);
        self::createRepresentationsCenter($center , $request->representation_id);

        // Attach the branch to a business, or a ghorfeAdmin would never see the branch they
        // just made: Center::scopeFilter shows them only what hangs off their own ghorfe.
        // The posted id comes from the "درباره ما" branch tab and names the business being
        // edited; without it, fall back to the creator's own business.
        $ghorfeId = GhorfeOnlineList::whereKey($request->input('ghorfe_online_list_id'))->value('id')
            ?: optional(ghorfe())->id;

        if ($ghorfeId) {
            $center->ghorfeOnlineLists()->syncWithoutDetaching([$ghorfeId]);
        }

        return $center;
    }
    /**
     * @param \App\Models\Center $center
     * @param array $representation
     * @return void
     */
    public function createRepresentationsCenter(Center $center , $representation)
    {
        $representations = auth()->user()->admin_representation_id ? [auth()->user()->admin_representation_id] : $representation;
        $center->representations()->sync($representations);
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public static function getByService(Request $request)
    {
        if ($request->ajax()) {
            $service_id = $request->input('id');
            $centers = Center::where( 'status', true )
                    ->whereHas('services', function ($query) use ($service_id) {
                    $query->whereIn('centerable_id', $service_id)->where('centerable_type', Service::class);
                });
            if ($centers->get()->count() > 0) {
                return response()->json(['count' => 'available', 'centers' => $centers->get()]);
            }
        }
    }
    /**
     * @param \App\Models\Center $center
     * @return \Illuminate\Http\RedirectResponse
     */
    public function financialReport(Center $center)
    {
        $user = auth()->user();
        if ($user->isAbleTo('center_financial_report_detail')||$user->isAbleTo('center_financial_report')) {
            $superadmin_center_user = User::where('level' , 'admin')->where('center_id' , $center->id)->whereHas('roles',function ($query){
                $query->where('name','superadmin_center');
            })->first();
            if(isset($superadmin_center_user->employee))
            {
                return redirect()->route('employee.financial.report', [$superadmin_center_user->employee->id]);
            }
            else
            {
                alert()->error('ناموفق', 'مدیری برای این شعبه انتخاب نشده است .')->persistent('باشه');
                return redirect()->back();
            }
        }
        return abort(401);
    }
}
