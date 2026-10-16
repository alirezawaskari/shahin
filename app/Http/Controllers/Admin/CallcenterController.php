<?php

namespace App\Http\Controllers\Admin;

use App\Classes\External\SendSms;
use App\Events\fastserviceCallcenter;
use App\Events\sendlinkFactor;
use App\Events\smsNotAnswer;
use App\Exports\CallcenterExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\CallcenterCollection;
use App\Models\Agency;
use App\Models\Area;
use App\Models\Callcenter;
use App\Models\Category;
use App\Models\Center;
use App\Models\Factor;
use App\Models\FactorItem;
use App\Models\History;
use App\Models\Organization;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Province;
use App\Models\Reason;
use App\Models\RecruitmentNotice;
use App\Models\Representation;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\SuspensionStandard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CallcenterController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $title = 'فهرست درخواست ها';
        $url = 'filter';
        $delete=false;
        if (auth()->user()->isAbleTo('callcenter-delete')){
            $delete = true;
        }
        return view('Admin.callcenter.all' , compact('title' ,'delete','url'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function desktop()
    {
        $title = 'میزکار درخواست ها';
        $url = 'desktop/filter';
        $delete=false;
        if (auth()->user()->isAbleTo('callcenter-delete')){
            $delete = true;
        }
         return view('Admin.callcenter.all' , compact('title' ,'delete', 'url'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function suspension()
    {
        $title = 'فهرست درخواست های معلق';
        $url = 'suspension/filter';
        $delete=false;
        if (auth()->user()->isAbleTo('callcenter-delete')){
            $delete = true;
        }
        return view('Admin.callcenter.all' , compact('title' ,'delete', 'url'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function paid()
    {
        $title = 'فهرست درخواست های پرداخت شده';
        $url = 'paid/filter';
        $delete=false;
        if (auth()->user()->isAbleTo('callcenter-delete')){
            $delete = true;
        }
        return view('Admin.callcenter.all' , compact('title' ,'delete', 'url'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function Cancellation()
    {
        $title = 'فهرست انصراف از دریافت پیامک';
        $url = 'cancellation/filter';
        $delete=false;
        if (auth()->user()->isAbleTo('callcenter-delete')){
            $delete = true;
        }
        return view('Admin.callcenter.all' , compact('title' ,'delete', 'url'));
    }

    /**
     * @param Request $request
     * @return CallcenterCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter(Request $request)
    {
        // Status 11 (SMS opt-outs) is hidden from the general list unless it is picked
        // explicitly: the dropdown offers it, and excluding it unconditionally made that
        // option always return nothing.
        $wantsOptOuts = in_array('11', array_map('strval', (array) $request->input('status', [])), true);

        $callcenters = Callcenter::query()
            ->when(!$wantsOptOuts, fn ($q) => $q->where('status', '!=', 11))
            ->filter()
            ->latest();

        if ($request->exel == 'on') {
            return (new CallcenterExport($callcenters))->download('callcenters.xlsx');
        }
        return new CallcenterCollection($callcenters->paginate(20));
    }

    //desktop for callcenter

    /**
     * @param Request $request
     * @return CallcenterCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterDesktop(Request $request)
    {
        $callcenters = Callcenter::filter()->where('user_id' , auth()->user()->id)->whereIn('status' , [0 , 4 , 6])->latest();

        if ($request->exel == 'on') {
            return (new CallcenterExport($callcenters))->download('callcenters.xlsx');
        }
        return new CallcenterCollection($callcenters->paginate(20));
    }

    /**
     * @param Request $request
     * @return CallcenterCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterSuspension(Request $request)
    {
        $callcenters = Callcenter::filter()->whereIn('status', [1 , 2 , 9])->latest();
        if ($request->exel == 'true') {
            return (new CallcenterExport($callcenters))->download('callcenters.xlsx');
        }
        return new CallcenterCollection($callcenters->paginate(20));
    }

    /**
     * @param Request $request
     * @return CallcenterCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterPaid(Request $request)
    {
        $callcenters = Callcenter::filter()->where('status', 3)->latest();
        if ($request->exel == 'true') {
            return (new CallcenterExport($callcenters))->download('callcenters.xlsx');
        }
        return new CallcenterCollection($callcenters->paginate(20));
    }

    /**
     * @param Request $request
     * @return CallcenterCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterCancellation(Request $request)
    {
        $callcenters = Callcenter::filter()->where('status', 11)->latest();
        if ($request->exel == 'true') {
            return (new CallcenterExport($callcenters))->download('callcenters.xlsx');
        }
        return new CallcenterCollection($callcenters->paginate(20));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function tasks_requires()
    {
        $user = auth()->user();
        $role_users = Role::where('name', 'callcenter')->orWhere('name', 'presentore')->orWhere('name', 'influencer-marketing')->with('users')->get();
        $admin_users = User::filter()->whereIn('level', ['admin' , 'ghorfeAdmin'])->withCount('tasks')->get();

        $services = Service::filter()->where('status' , true)->where('status' , 1)->get();
        $categories = Category::filter()->where('parent_id', 1)->where('isActive' , 1)->get();
        $permission = false;

        if (auth()->user()->center_id) {
            $services = Service::filter()->where('status' , true)->whereIn('id' , auth()->user()->center->services->pluck('id')->toArray())->get();
            $categories = Category::filter()->where('parent_id', 1)->where('id', auth()->user()->center->centerable_id)->get();
        }
        if($user->isAbleTo('record_payment'))
        {
            $permission = true;
        }
        if ($user->admin_representation) {
            $services = auth()->user()->admin_representation->services;
            $categories = auth()->user()->admin_representation->categories;
            $admin_users = User::filter()->whereIn('level', ['admin','ghorfeAdmin'])->where('admin_representation_id', $user->admin_representation->id)->withCount('tasks')->get();
        }

        $old_callcenters = [];

        foreach ($role_users as $users) {
            foreach ($users->users as $u) {
                if ($u->level == 'user') {
                    $old_callcenters[] = $u;
                }
            }
        }

        $callcenters = [];
        foreach ($admin_users as $u) {
            if ($user->isAbleTo('factor') || $user->isAbleTo('factor-detail')) {
                $callcenters[] = $u;
            }
        }

        return response([
            'data' => [
                'services' => $services->map(function ($item) {
                    $temp = [];
                    $temp['id'] = $item->id;
                    $temp['label'] = $item->title . ' - ' . number_format($item->price) . ' ریال';
                    return $temp;
                }),
                'categories' => $categories->map(function ($item) {
                    $temp = [];
                    $temp['id'] = $item->id;
                    $temp['label'] = $item->title . ' - ' . number_format($item->price) . ' ریال';
                    return $temp;
                }),
                'callcenters' => $callcenters,
                'old_callcenters' => $old_callcenters,
                'reason' => Reason::where('id', '!=', 0)->get(),
                'provinces' => Province::all(),
                'areas' => Area::all(),
                'centers' => Center::where('status' , 1)->get(),
                'representations' => Representation::all(),
                'organizations' => Organization::all(),
                'recruitmentNotices' => RecruitmentNotice::all(),
                'permission' => $permission,
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    /**
     * SMS history of ONE request: the SMS that created it (when it came in by SMS) plus every
     * message the SMS module sent for it. Scoped exactly like the requests list, and confidential
     * message bodies are omitted server-side for viewers without view-confidential-sms.
     *
     * Legacy automated messages (payment / card links, close-task) are not recorded per request
     * anywhere, so they cannot appear here; they only exist as text notes in the details tab.
     */
    public function smsHistory(int $callcenter)
    {
        $task = Callcenter::query()->accessScoped()->with('sms')->findOrFail($callcenter);
        $viewer = auth()->user();
        $items = [];

        if ($task->type == 1 && $task->sms) {
            $items[] = [
                'direction' => 'inbound',
                'party' => $task->sms->sender_number,
                'content' => $task->sms->body,
                'redacted' => false,
                'status' => 'received',
                'service' => 'درخواست از طریق پیامک',
                'date' => verta($task->sms->created_at)->format('Y/m/d H:i'),
                'at' => $task->sms->created_at?->timestamp ?? 0,
            ];
        }

        $messages = \App\Models\SmsMessage::query()
            ->where('callcenter_id', $task->id)
            ->with('senderNumber')
            ->get();
        \App\Services\Sms\SmsConfidentialityGuard::redactMany($messages, $viewer);

        foreach ($messages as $message) {
            $items[] = [
                'direction' => $message->direction === 'inbound' ? 'inbound' : 'outbound',
                'party' => $message->direction === 'inbound' ? $message->sender_phone : $message->recipient_phone,
                'content' => $message->content,
                'redacted' => (bool) ($message->content_redacted ?? false),
                'status' => $message->delivery_status === 'delivered' ? 'delivered' : $message->status,
                'service' => match ($message->service_type) {
                    'otp' => 'OTP',
                    'advertising' => 'تبلیغاتی',
                    default => 'عادی',
                },
                'sender' => $message->sender_phone ?: $message->senderNumber?->number,
                'date' => verta($message->created_at)->format('Y/m/d H:i'),
                'at' => $message->created_at?->timestamp ?? 0,
            ];
        }

        usort($items, fn ($a, $b) => $b['at'] <=> $a['at']);

        return response()->json(['data' => ['messages' => $items]]);
    }

    public function taskDetail(Request $request)
    {
        $task = Callcenter::find($request->id);
        $user = auth()->user();
        $detail = false;
        if ($user->isAbleTo('callcenter-detail')) {
            $detail = true;
        }
        $user = $task->type == 1 ? User::where('phone' , $task->sms->sender_number)->first() : $task->register;
        $user ? $task->update(['reg_id' => $user->id]) : '';
        $histories = Helper::userHistories($task->register);
        $tasks= isset($user->id) ? Callcenter::whereIn('status',array(0,1,2,3,4,6))->where('reg_id',$user->id)->latest()->get() : [];
        $tasks= isset($user->id) ? CallcenterCollection::make($tasks) : [];
        $factors=$user ? $user->factors->whereIn("status",array(0,1))->whereIn('title',array('register','factor'))->where('factor_item_id',null)->map(function ($factor) {
            $item = [];
            $item['resnumber'] = $factor->resnumber;
            $item['price'] = number_format($factor->price) . ' ریال';
            $item['type'] = $factor->type == 'online' ? 'پرداخت آنلاین' : 'کارت به کارت';
            $item['status'] = Factor::status($factor->status);
            $item['date'] = verta($factor->created_at)->format('%d %B ، %Y - H:i');
            $item['items'] = $factor->factorItems->map(fn($factor_item) => self::factorItemLabel($factor_item));
            $item['representation'] = !is_null($factor->representation_id) ? $factor->representation->fa_name : 'مستاجرین و شرکای تجاریی تهران';
            return $item;
        }) : [];
        return response([
            'data' => [
                'per_detail' => $detail,
                'histories' => $histories,
                'tasks'=>$tasks,
                'factors' => $factors,
            ]
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function sendLink()
    {
        $provinces = Province::all();

        if (auth()->user()->center_id) {
            $service_categories = Category::filter()->where('parent_id', 2)->where('isActive' , 1)->whereIn('id', auth()->user()->center->services->pluck('category_id')->toArray())->get();
            $product_categories = Category::filter()->where('parent_id', 1)->where('id', auth()->user()->center->centerable_id)->get();
        }elseif (auth()->user()->admin_representation) {
            $service_categories = auth()->user()->admin_representation->services;
            $product_categories = auth()->user()->admin_representation->categories;
        }else{
            // Same union the modal's composer uses (FilterServiceProvider::tasks_requires):
            // the business's own catalogue counts, not only the hand-maintained pivot.
            $product_categories = Category::invoiceRows(1)->get();
            $service_categories = Category::invoiceRows(2)->get();
        }

        if(auth()->user()->center && auth()->user()->center->centerable_type == Service::class)
        {
            $service_categories = Category::filter()->where('id', auth()->user()->center->centerable->category_id)->get();
        }

        return view('Admin.callcenter.sendLink', compact('provinces','service_categories','product_categories'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function fetchUser(Request $request)
    {

        $phone = Helper::numberConverter($request->phone);
        $user = User::where('phone', $phone)->first();
        $histories = [];
        $factors = [];
        $tasks = [];
        if($user)
        {
            $histories = Helper::userHistories($user);
            $factors = $user->factors->whereIn("status",array(0,1))->whereIn('title',array('register','factor'))->where('factor_item_id',null)->map(function ($factor,$index)  {
                $item = [];
//        $item['resnumber'] = $factor->resnumber;
                $register_by=!is_null($factor->callcenter_id)?User::find($factor->callcenter_id):null;
                $item['register_by'] = !is_null($register_by)?$register_by->name.' '.$register_by->family:'سایت';
                $item['price'] = number_format($factor->price) . ' ریال';
                $item['type'] = $factor->type == 'online' ? 'پرداخت آنلاین' : 'کارت به کارت';
                $item['status'] = $factor->status ;
                $item['date'] = verta($factor->created_at)->format('%d %B ، %Y - H:i');
                $item['items'] = $factor->factorItems->map(fn($factor_item) => self::factorItemLabel($factor_item));
                $item['representation'] = !is_null($factor->representation_id) ? $factor->representation->fa_name : 'مستاجرین و شرکای تجاریی تهران';
                return $item;
            });
            $tasks=Callcenter::whereIn('status',array(0,1,2,3,4,6))->where('reg_id',$user->id)->latest()->get();
            $tasks=CallcenterCollection::make($tasks);
        }

        $vip = $user && $user->vip && $user->vip->type == 'VIP' ? true : false;
        $sos = $user && $user->vip && $user->vip->type == 'SOS' ? true : false;
        $user['vip_user'] = $vip;
        $user['sos'] = $sos;

        return response(['operator' => true, 'data' => ['factors'=>$factors,'user' => $user, 'histories' => $histories,'tasks'=>$tasks]]);
    }

    /**
     * Products of one category, for the کالا tab's Category -> Product -> Variant cascade.
     *
     * Deliberately unfiltered by `isShow`/`status`: those flags govern the public shop, and
     * on this data only a handful of rows carry them, which would leave the operator with an
     * empty picker. The chosen category is what narrows the list.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function productsByCategory(Request $request)
    {
        $category = Category::find($request->id);

        $products = $category
            ? $category->products()->orderBy('products.name')->get(['products.id', 'products.name'])
                ->map(fn($product) => ['id' => $product->id, 'label' => $product->name])
                ->values()
            : collect();

        return response(['operator' => true, 'products' => $products]);
    }

    /**
     * Variants (product attributes) of one product, each carrying the price that becomes the
     * default registration fee for that line.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function productAttributesByProduct(Request $request)
    {
        $attributes = ProductAttribute::with('productattributeable')
            ->where('product_id', $request->id)
            ->orderByDesc('is_show')
            ->get()
            ->map(fn($attribute) => [
                'id' => $attribute->id,
                'label' => self::productAttributeLabel($attribute),
                'price' => (int) $attribute->price,
            ])
            ->values();

        return response(['operator' => true, 'product_attributes' => $attributes]);
    }

    /**
     * The default registration fee of a service — `services.price`, the same figure the
     * price math falls back on. Fetched on select so the operator can edit it before saving.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function serviceRegisterPrice(Request $request)
    {
        $service = Service::find($request->id);

        return response([
            'operator' => true,
            'price' => $service ? (int) $service->price : 0,
        ]);
    }

    /**
     * A human label for a variant. `attribute_item_id` is 0 on most rows here, so the free
     * text in `value` is the only reliable descriptor; the attribute title prefixes it when
     * there is one.
     *
     * @param ProductAttribute $attribute
     * @return string
     */
    private static function productAttributeName(ProductAttribute $attribute): string
    {
        $title = optional(\App\Models\Attribute::find($attribute->attribute_id))->title;
        $value = $attribute->value ?: optional(\App\Models\AttributeItem::find($attribute->attribute_item_id))->title;

        $name = trim(implode(' : ', array_filter([$title, $value])));

        return $name !== '' ? $name : 'تنوع ' . $attribute->id;
    }

    /**
     * Variant label for the picker, where the price is what the operator is choosing between.
     *
     * @param ProductAttribute $attribute
     * @return string
     */
    private static function productAttributeLabel(ProductAttribute $attribute): string
    {
        return self::productAttributeName($attribute) . ' - ' . number_format((int) $attribute->price) . ' ریال';
    }

    /**
     * One invoice line described in full, for the آیتم ها column.
     *
     * A service line is a single row. A product line is one CATEGORY, under which every کالا
     * row the operator added is a factor_item_products record — so the category is followed
     * by its products, each with its own variant and charged amount. Without them a product
     * line would render as nothing but its category title.
     *
     * @param $factor_item
     * @return string
     */
    private static function factorItemLabel($factor_item): string
    {
        $header = [optional($factor_item->factoritemable)->title];

        if ($factor_item->service_item_id) {
            $header[] = optional(ServiceItem::find($factor_item->service_item_id))->title;
        }

        //the charged amount, which may differ from the source price when the operator
        //edited the registration fee for this customer
        $header[] = number_format((int) $factor_item->price) . ' ریال';

        $lines = [implode(' - ', array_filter($header))];

        foreach ($factor_item->products as $product_row) {
            $lines[] = '• ' . implode(' - ', array_filter([
                optional($product_row->product)->name,
                $product_row->productAttribute ? self::productAttributeName($product_row->productAttribute) : null,
                number_format((int) $product_row->price) . ' ریال',
            ]));
        }

        return implode('<br>', $lines);
    }

    //create & send link factor

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function registerForm(Request $request)
    {
        $status = self::getStatusFactorByPayType($request->payment_type);
        $price = 0;
        $cash_factor = [];
        $credit_factor = [];
        $phone = Helper::numberConverter($request->phone);
        User::createUser($request);
        $client = User::where('phone', $phone)->first();
        //create new user
        if (!$client) {
            $client = User::createUser($request);

        }else{
            $conduct=null;
            if ($request->challenging)
              $conduct='challenging';
            $client->update([
                'sex'=>$request->gender,
                'conduct'=>$conduct
            ]);
        }

        $payment_type = $request->payment_type != null ? $request->payment_type : 'online';
        if ($client) {
            $services = $request->service ?: [];
            //sum price factor cash or credit
            $categories = $request->product_category ? $request->product_category : [];
            if (isset($request->send_factor)&&$request->send_factor==1) {
                $invoice_rows_error = self::validateInvoiceRows($request, $services, $categories);
                if ($invoice_rows_error) {
                    return redirect()->back()->withErrors(['invoice_rows' => $invoice_rows_error])->withInput();
                }
                $result_sum_price_factors = self::getSumPriceFactorItems($request , $services , $categories);
                if ( $payment_type == 'credit' ) {
                    $result = User::checkCredit( $client, $result_sum_price_factors['sum_price_factors'] , $request->input( 'password' ) , 'register');
                    if ( !$result[ 'success' ] ) {
                        return redirect()->back()->with( [
                            'message' => $result[ 'message' ]
                        ] );
                    }
                }
                if ( $payment_type == 'wallet' ) {
                    $result = User::checkWallet( $client, $price, $request->input( 'password' ) );
                    if ( !$result[ 'success' ] ) {
                        return redirect()->back()->with( [
                            'message' => $result[ 'message' ]
                        ] );
                    }
                }
            }
            //create task.
            $task = self::createTask($request , $client , $payment_type);
            self::syncTaskItems($request , $task);
            $mobile_supervisor = $request->input('mobile_supervisor') ? $request->input('mobile_supervisor') : $phone;
            if (isset($request->send_factor)&&$request->send_factor==1) {
                //A modal save is one invoice. Cash/credit remains a property of each line;
                //it must not split the rows into two separate factors.
                $factor = self::createInvoiceFactor($request, $client, $task, $services, $categories, $payment_type, $result_sum_price_factors, $status);
                $cash_factor = $result_sum_price_factors['is_cash_factor'] ? $factor : [];
                $credit_factor = $result_sum_price_factors['is_credit_factor'] ? $factor : [];

                //save factor payment by any ways
                if ($result_sum_price_factors['is_credit_factor'] || $result_sum_price_factors['is_cash_factor']) {
                    //create factorItem for services
                    $create_service_factor_item_result = self::createServiceFactorItems($request, $services, $cash_factor, $credit_factor, $mobile_supervisor);
                    //create facorItem for categories
                    self::createCategoryFactorItems($create_service_factor_item_result, $request, $categories, $cash_factor, $credit_factor, $mobile_supervisor);
                    $payment_type = $factor->type;
                    self::getFactorRepresentation($factor);
                    self::updateInvoiceFactorForSupervisor($factor);
                    self::updateCallcenterFieldIsOnlinePayTypeFactor($task, $payment_type);
                    self::updateCallcenterFieldIsZeroPriceFactor($task, $result_sum_price_factors);
                }
                //send sms for online factors
                if ($result_sum_price_factors['sum_price_factors'] != 0 && ($payment_type == 'online' || $payment_type == 'send_card_number' || $payment_type == 'send_shaba_number')) {
                    self::sendSmsFactor($task, $result_sum_price_factors, $factor, $services, $categories, $payment_type);
                } else {
                    Factor::savePayment($factor);
                }
            }
            else {
                if (is_array($services) && count($services)>0){
                    self::sendSmsLinkSite($request , $client , $task,$services);
                }
                //the کالا counterpart of the service study link
                if (is_array($request->product) && count($request->product)>0){
                    self::sendSmsLinkProduct($request , $client , $task , array_keys($request->product));
                }
            }
            Helper::vipUser($request->vip, $client->id);
            Helper::sosUser($request->sos, $client->id);
            if($request->callcenter)
            {
                $request->task = $task->id;
                self::transfer_form($request);
            }
            return redirect()->back()->with( [
                'create' => 'success'
            ] );
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function statusForm(Request $request)
    {
       $validate = Validator::make($request->all(), [
            'reason' => 'required',
            'task' => ['required',Rule::exists('callcenters','id')],
        ]);
        if ($validate->fails()){
            return response([
               'status'=>'error',
               'messages'=>$validate->errors()->all()
            ]);
        }
        $task = Callcenter::find($request->task);

        if($task->status == 3)
        {
            return response([
                'operator' => true,
                'data'=> ['histories'=>Helper::userHistories($task->register)],
                'status'=>'error',
                'messages'=>'همکار گرامی امکان ثبت یادآوری برای درخواست پرداخت شده وجود ندارد'
            ]);
        }

        $task->update([
            'status' => $request->reason == 0 ? 3 : 2,
            'description' => $request->description,
            'reason_id' => $request->reason,
            'date_expire' => $request->date,
        ]);
        //create history
        $description = !is_null($request->description) ? $request->description : Reason::find($request->reason)->title;
        //create history
        $status = $request->reason == 0 ? 3 : 2;
        History::create($task , $task->user_id , $request->reason , $status , $description);
        //transfer task if select callcenter
        if($request->callcenter)
        {
            self::transfer_form($request);
        }
        //send sms
        if($task->register && $task->reason_id == 6)
        {
            self::suspensionBasedOnStandards($request , $task);
            event(new smsNotAnswer($task->register , $task->callcenter , 'مستاجرین و شرکای تجاری تماس'));
        }
        else
        {
            $task->suspensionStandard()->delete();
        }

        if($task->register)
        {
            if (Agency::where('phone', $task->register->phone)) {
                Agency::where('phone', $task->register->phone)->update(['updated_at' => now()]);
            }

            Helper::vipUser($request->vip, $task->register->id);
            Helper::sosUser($request->sos, $task->register->id);
        }

        return response([
            'operator' => true,
            'data'=> ['histories'=>Helper::userHistories($task->register)],
            'status'=>'success',
            'messages'=>'یادآور با موفقیت ثبت گردید !'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function transfer_form(Request $request)
    {
        $validate=Validator::make($request->all(), [
            'callcenter' => 'required',
        ]);
        if ($validate->fails()){
            return response([
                'status'=>'error',
                'messages'=>$validate->errors()->all()
            ]);
        }
        $callcenter = User::find($request->callcenter);
        $task = Callcenter::find($request->task);
        $task->update([
            'user_id' => $request->callcenter,
            'representation_id' => $callcenter->admin_representation_id,
        ]);
        $auth_user =  auth()->user();
        //create history
        $description = "درخواست توسط همکار $auth_user->name  $auth_user->family  به همکار $callcenter->name $callcenter->family انتقال پیدا کرد ";
        $status = $task->status;
        History::create($task , $request->callcenter , $request->reason , $status , $description);

        return response([
            'operator' => true,
            'messages'=>'انتقال با موفقیت انجام گرفت !',
            'status'=>'success'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function task_transfer(Request $request)
    {
       $validation = Validator::make($request->all(), [
            'type' => 'required',
            'startUser' => 'required_if:type,date,default',
            'endUser' => 'required',
            'startDate' => 'required_if:type,date',
            'endDate' => 'required_if:type,date',
            'count' => 'required_if:type,default,date',
            'id' => 'required_if:type,select'
        ]);
            if ($validation->fails()){
                return response(['status'=>'error','messages'=>$validation->messages()->all()]);
            }
        if ($request->type == 'default') {
            $callcenters = Callcenter::where('user_id', $request->startUser)->where('status', '!=', 3)->take($request->count)->get();
        } else if ($request->type == 'date') {
            $callcenters = Callcenter::where('user_id', $request->startUser)->where('status', '!=', 3)->whereBetween('created_at', [$request->startDate, $request->endDate])->take($request->count)->get();
        } else {
            $callcenters = Callcenter::find($request->id);
        }
        $end_user = User::find($request->endUser);

        foreach ($callcenters as $callcenter) {
            $callcenter->user_id = $request->endUser;
            $callcenter->representation_id = $end_user->admin_representation_id;

            $callcenter->save();
        }

        return response([
            'operator' => true,
            'status'=>'success',
            'messages'=>'انتقال با موفقیت انجام گرفت .'
        ]);
    }

    /**
     * @param Callcenter $callcenter
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function destroy(Callcenter $callcenter)
    {
        if (auth()->user()->isAbleTo('callcenter-delete')) {
            $callcenter->delete();
            return response([
                'operator' => true,
                'status'=>'success',
                'message'=>'حذف با موفقیت انجام گردید .'
            ]);
        }
        else
        {
            return response([
                'operator' => true,
                'status'=>'unauthorized',
                'message'=>'شما مجاز به انجام این کار نیستید.'
            ]);
        }

    }
    public function multiDelete(Request $request)
    {
        if (auth()->user()->isAbleTo('callcenter-delete')) {
            foreach ($request->id as $id) {
                $callcenter = Callcenter::find($id);
                if (isset($callcenter)) {
                    $callcenter->delete();
                }
            }
            return response([
                'operator' => true,
                'status'=>'success',
                'message'=>'حذف با موفقیت انجام گردید .'
            ]);
        }
        return response([
            'operator' => true,
            'status'=>'unauthorized',
            'message'=>'شما مجاز به انجام این کار نیستید.'
        ]);

    }
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function delete()
    {
        $callcenters = Callcenter::onlyTrashed()->latest()->paginate(20);
        return view('Admin.callcenter.delete', compact('callcenters'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(Request $request)
    {
        $callcenter = Callcenter::onlyTrashed()->where('id', $request->id)->first();
        $callcenter->restore();

        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }

    /**
     * @return string
     */
    private function getType()
    {
        if(auth()->user()->hasPermission('influencer-marketing'))
        {
            return '3';
        }
        if(auth()->user()->hasPermission('in-person-registration'))
        {
            return '4';
        }
        return '2';
    }

    /**
     * @param $payment_type
     * @return bool
     */
    private function getStatusFactorByPayType($payment_type)
    {
        return $payment_type == 'send_card_number' || $payment_type == 'send_shaba_number' || $payment_type == 'supervisor' || $payment_type == 'online' ? false : true;
    }

    /**
     * @param $cash_factor
     * @param $credit_factor
     * @return string
     */
    private function getPaymentType($cash_factor , $credit_factor)
    {
        $payment_type = $cash_factor ? $cash_factor->type : '';
        $payment_type = $credit_factor ? $credit_factor->type : $payment_type;

        return $payment_type;
    }

    /**
     * Summary of userConductUpdate
     * @param mixed $challenging
     * @param User $user
     * @return User
     */
    private function userConductUpdate($challenging , User $user)
    {
        $challenging= null;
        if ($challenging)
            $challenging='challenging';
        $user->update([
            'conduct' => $challenging,
        ]);
        return $user;
    }

    /**
     * @param $request
     * @param $user
     * @param $payment_type
     * @return mixed
     */
    private function createTask($request , $user , $payment_type)
    {
        $id = Helper::getCallcenter($user->province_id , auth()->user()->admin_representation_id);
        $agent = !auth()->user()->hasPermission('callcenter') ? auth()->user()->id : null;
        $callcenter = ($request->request_type == "link" && $payment_type == "online" && !auth()->user()->hasPermission('callcenter')) ? $id : auth()->user()->id;
        $type_task = self::getType();

        if ($request->task) {
            $callcenter = Callcenter::find($request->task);
            $callcenter->update([
                'type' => $type_task,
                'reg_id' => $user->id,
                'request_type' => 'link',
                'agent_id' => $agent,
                'status' => $request->send_factor ? 1 : 0,
            ]);
        }
        elseif($request->other_task_id == 'new') {
                $callcenter = Callcenter::create([
                    'reg_id' => $user->id,
                    'user_id' => $callcenter,
                    'agent_id' => $agent,
                    'type' => $type_task,
                    'request_type' => $request->request_type,
                    'status' => $request->send_factor ? 1 : 0,
                    'description' => $request->description,
                    'representation_id' => auth()->user()->admin_representation ? auth()->user()->admin_representation->id : null,
                ]);

            $ghorfe_online = ghorfe();
            if ($ghorfe_online) {
                $ghorfe_online->callcenters()->attach($callcenter->id);
            }
                //send a request registeration sms to the user
                // $request->request_type == 'other' ? event(new registerRequestNotification($user)) : '';
        }else{
            $callcenter= Callcenter::find($request->other_task_id);

            $callcenter->update([
                'description' => $request->description,
            ]);
        }

        $request->description ? History::create($callcenter , $callcenter->user_id , null , 2 , $callcenter->description) : '';
        return $callcenter;
    }

    /**
     * Summary of syncTaskItems
     * @param mixed $request
     * @param Callcenter $task
     * @return void
     */
    private function syncTaskItems($request , Callcenter $task)
    {
        if (is_array($request->product_category) && count($request->product_category)>0)
        $task->categories()->attach($request->product_category);
        if (is_array($request->service) && count($request->service)>0)
        $task->services()->attach($request->service);
        if (is_array($request->service_item) && count($request->service_item)>0)
        $task->services_item()->attach($request->service_item);
    }

    /**
     * Every invoice row is an atomic selection. Keeping the row keys from the browser
     * prevents a missing child select in one row from being paired with the next row.
     */
    private static function validateInvoiceRows($request, array $services, array $categories): ?string
    {
        $row_count = 0;

        foreach ($services as $row => $service_id) {
            if (!is_numeric($service_id)) {
                continue;
            }
            $row_count++;
            $service = Service::find($service_id);
            $service_category_id = self::requestItemAt($request->service_category, $row);
            $service_item_id = self::requestItemAt($request->service_item, $row);
            $purchase_type = self::requestItemAt($request->service_purchase_type, $row);
            $service_item = $service_item_id ? ServiceItem::find($service_item_id) : null;

            $category_is_valid = auth()->user()->center
                ? true
                : $service && is_numeric($service_category_id) && (int) $service->category_id === (int) $service_category_id;

            $service_item_is_valid = !$service_item_id
                || ($service_item && (int) $service_item->service_id === (int) $service_id);

            if (!$service || !$category_is_valid || !$service_item_is_valid
                || !in_array((string) $purchase_type, ['0', '1'], true)) {
                return 'برای هر ردیف خدمات، دسته‌بندی، خدمت و نحوه دریافت را کامل انتخاب کنید.';
            }
        }

        foreach ($categories as $row => $category_id) {
            if (!is_numeric($category_id)) {
                continue;
            }
            $row_count++;
            $product_id = self::requestItemAt($request->product, $row);
            $attribute = ProductAttribute::find(self::requestItemAt($request->product_attribute, $row));
            $purchase_type = self::requestItemAt($request->category_purchase_type, $row);
            $category = Category::find($category_id);

            if (!$category || !$category->products()->where('products.id', $product_id)->exists()
                || !$attribute || (int) $attribute->product_id !== (int) $product_id
                || !in_array((string) $purchase_type, ['0', '1'], true)) {
                return 'برای هر ردیف کالا، دسته‌بندی، کالا، تنوع کالا و نحوه دریافت را کامل انتخاب کنید.';
            }
        }

        return $row_count > 0 ? null : 'حداقل یک ردیف خدمت یا کالا برای فاکتور انتخاب کنید.';
    }

    /**
     * @param $request
     * @param $services
     * @param $categories
     * @return array
     */
    private function getSumPriceFactorItems($request , $services , $categories)
    {
        $cash_price_tax = 0;
        $cash_price = 0;
        $credit_price_tax = 0;
        $credit_price = 0;
        $is_cash_factor = false;
        $is_credit_factor = false;

        //sum price factor cash services or credit services
        foreach ($services as $i => $service_id) {
            //'all', or an untouched row still holding its placeholder text
            if ($service_id == 'all' || !is_numeric($service_id)) {
                continue;
            }
            $service = Service::find($service_id);
            if (!$service) {
                continue;
            }
            //registration fee = services.price, unless the operator edited it for this request
            $price = self::resolveRegisterPrice($request->service_register_price, $i, $service->price);
            $tax = ($price * $service->tax) / 100;

            //cash vs installment is the service's own pay_type now that plans are gone
            if ((int) $service->pay_type === 0) {
                $cash_price = $cash_price + $price;
                $cash_price_tax = $cash_price_tax + $tax;
                $is_cash_factor = true;
            } else {
                $credit_price = $credit_price + $price;
                $credit_price_tax = $credit_price_tax + $tax;
                $is_credit_factor = true;
            }
        }
        //sum price factor cash categories or credit categories
        foreach ($categories as $i => $category_id) {
            //'all', or an untouched row still holding its placeholder text
            if ($category_id == 'all' || !is_numeric($category_id)) {
                continue;
            }
            $category = Category::find($category_id);
            $product = Product::find(self::requestItemAt($request->product, $i));
            //registration fee = the selected variant's price, unless the operator edited it
            $price = self::resolveRegisterPrice(
                $request->category_register_price,
                $i,
                self::defaultProductRegisterPrice($request, $i, $category)
            );
            $tax = ($price * (optional($category)->tax ?: 0)) / 100;
            $pay_type = $product && $product->pay_type !== null ? (int) $product->pay_type : 0;

            if ($pay_type === 0) {
                $cash_price = $cash_price + $price;
                $cash_price_tax = $cash_price_tax + $tax;
                $is_cash_factor = true;
            } else {
                //pay credit
                $credit_price = $credit_price + $price;
                $credit_price_tax = $credit_price_tax + $tax;
                $is_credit_factor = true;
            }
        }

        $result = [
            'cash_price_tax' => $cash_price_tax,
            'cash_price' => $cash_price,
            'is_cash_factor' => $is_cash_factor,
            'credit_price_tax' => $credit_price_tax,
            'credit_price' => $credit_price,
            'is_credit_factor' => $is_credit_factor,
            'sum_price_factors' => $cash_price + $cash_price_tax + $credit_price + $credit_price_tax,
        ];
        return $result;
    }

    /**
     * The registration fee for one line: the operator's edited value when they supplied one,
     * otherwise the default read from the Service / product variant.
     *
     * The edit applies to this request only — nothing is written back to the source row.
     *
     * @param $overrides
     * @param $index
     * @param $default
     * @return int
     */
    private static function resolveRegisterPrice($overrides, $index, $default): int
    {
        $value = self::requestItemAt($overrides, $index);

        if (is_string($value)) {
            //Persian digits, then drop grouping separators (both ',' and the Arabic '٬')
            $value = preg_replace('/[^\d.]/u', '', Helper::numberConverter(trim($value)));
        }

        if ($value === null || $value === '' || !is_numeric($value) || $value < 0) {
            return (int) $default;
        }

        return (int) $value;
    }

    /**
     * The default registration fee of a product line: the selected variant's price, falling
     * back to the product and then the category when the operator picked no variant.
     *
     * @param $request
     * @param $index
     * @param $category
     * @return int
     */
    private static function defaultProductRegisterPrice($request, $index, $category): int
    {
        $attribute = ProductAttribute::find(self::requestItemAt($request->product_attribute, $index));
        if ($attribute) {
            return (int) $attribute->price;
        }

        $product = Product::find(self::requestItemAt($request->product, $index));
        if ($product && $product->price) {
            return (int) $product->price;
        }

        return (int) (optional($category)->price ?: 0);
    }

    /**
     * One entry of a parallel `name[]` input, or null when the row did not submit that field.
     *
     * @param $items
     * @param $index
     * @return mixed
     */
    private static function requestItemAt($items, $index)
    {
        return is_array($items) && isset($items[$index]) ? $items[$index] : null;
    }

    /**
     * Create the single factor owned by one ارسال فاکتور submission. Individual lines keep
     * their own cash/credit type; a TotalFactorItem is added when at least one line is credit.
     */
    private function createInvoiceFactor($request, $user, $callcenter, array $services, array $categories, $payment_type, array $totals, $status)
    {
        $price = $totals['cash_price'] + $totals['cash_price_tax']
            + $totals['credit_price'] + $totals['credit_price_tax'];
        $tax = $totals['cash_price_tax'] + $totals['credit_price_tax'];
        $line_count = collect($services)->filter(fn($id) => is_numeric($id))->count()
            + collect($categories)->filter(fn($id) => is_numeric($id))->count();

        $factor = $callcenter->factors()->create([
            'confirm' => false,
            'user_id' => $user->id,
            'task_id' => $callcenter->id,
            'title' => $totals['is_credit_factor'] ? 'register' : 'factor',
            'price' => $price,
            'tax' => $tax,
            'count' => $line_count,
            'callcenter_id' => auth()->user()->id,
            'type' => $payment_type,
            'status' => $price == 0 ? true : $status,
            'number_card' => $request->number_card,
            'payment_code' => $request->payment_code,
            'center_id' => auth()->user()->center_id,
            'representation_id' => auth()->user()->admin_representation_id ?: null,
            'expired_at' => now()->addMinutes(30),
        ]);

        // The invoice belongs to the business that raised it, not to whichever business the
        // customer happens to be a member of — a business associate may invoice someone who
        // has never dealt with their shop, and that invoice must still appear in their own
        // list (Factor::scopeFilter scopes on this pivot). Same attach createTask() does for
        // the task itself.
        $ghorfe_online = ghorfe();
        if ($ghorfe_online) {
            $factor->ghorfeOnlineLists()->syncWithoutDetaching([$ghorfe_online->id]);
        }

        if ($request->url_bill_image) {
            \App\Helpers\MediaLibraryHelper::attachTo($factor, $request->url_bill_image, 'bill_image');
        }

        if ($totals['is_credit_factor']) {
            $factor->totalFactorItem()->create([
                'factor_id' => $factor->id,
                'total_factor_item_status_id' => 0,
                'status_mali' => 0,
            ]);
        }

        return $factor;
    }

    /**
     * @param $request
     * @param $user
     * @param $callcenter
     * @param $services
     * @param $payment_type
     * @param $result_factor_items
     * @param $status
     * @return mixed
     */
    private function createCreditFactor($request , $user , $callcenter , $services  , $payment_type , $result_factor_items , $status)
    {
        $price = $result_factor_items['credit_price'] + $result_factor_items['credit_price_tax'];

        $credit_factor = $callcenter->factors()->create([
            'confirm' => false,
            'user_id' => $user->id,
            'task_id' => $callcenter->id,
            'title' => 'register',
            'price' => $price,
            'tax' => $result_factor_items['credit_price_tax'],
            'count' => count($services),
            'status' => $price == 0 ? true : $status,
            'callcenter_id' => auth()->user()->id,
            'type' => $payment_type,
            'number_card' => $request->number_card,
            'payment_code' => $request->payment_code,
            'center_id' => auth()->user()->center_id,
            'representation_id' => auth()->user()->admin_representation ? auth()->user()->admin_representation->id : null,
            'expired_at' => now()->addMinutes(30),
        ]);
        //upload bill_image cash factor
        if ($request->url_bill_image) {
            \App\Helpers\MediaLibraryHelper::attachTo($credit_factor, $request->url_bill_image, 'bill_image');
        }

        $credit_factor->totalFactorItem()->create([
            'factor_id' => $credit_factor->id,
            'total_factor_item_status_id' => 0,
            'status_mali' => 0,
        ]);

        return $credit_factor;
    }

    /**
     * @param $request
     * @param $user
     * @param $callcenter
     * @param $services
     * @param $payment_type
     * @param $result_factor_items
     * @param $status
     * @return mixed
     */
    private function createCashFactor($request , $user , $callcenter , $services , $payment_type , $result_factor_items , $status)
    {
        $price = $result_factor_items['cash_price'] + $result_factor_items['cash_price_tax'];

        $cash_factor = $callcenter->factors()->create([
            'confirm' => false,
            'user_id' => $user->id,
            'task_id' => $callcenter->id,
            'title' => 'factor',
            'price' => $price,
            'tax' => $result_factor_items['cash_price_tax'],
            'count' => count($services),
            'callcenter_id' => auth()->user()->id,
            'type' => $payment_type,
            'status' => $price == 0 ? true : $status,
            'number_card' => $request->number_card,
            'payment_code' => $request->payment_code,
            'center_id' => auth()->user()->center_id,
            'representation_id' => auth()->user()->admin_representation_id ? auth()->user()->admin_representation_id : null,
            'expired_at' => now()->addMinutes(30),
        ]);
        //upload bill_image cash factor
        if ($request->url_bill_image) {
            \App\Helpers\MediaLibraryHelper::attachTo($cash_factor, $request->url_bill_image, 'bill_image');
        }
        return $cash_factor;
    }

    /**
     * @param $request
     * @param $services
     * @param $cash_factor
     * @param $credit_factor
     * @param $mobile_supervisor
     * @return array
     */
    private function createServiceFactorItems($request , $services , $cash_factor , $credit_factor , $mobile_supervisor)
    {
        $payment_type = $cash_factor ? $cash_factor->type : '';
        $payment_type = $credit_factor ? $credit_factor->type : '';

        $cash_factor_result['cash_factor_price'] = $cash_factor ? $cash_factor->price : 0;
        $cash_factor_result['cash_factor_status'] = $cash_factor ? $cash_factor->status : 0;
        $credit_factor_result['credit_factor_price'] = $credit_factor ? $credit_factor->price : 0;
        $credit_factor_result['credit_factor_status'] = $credit_factor ? $credit_factor->status : 0;

        foreach ($services as $i => $service_id) {
            if ($service_id == 'all' || !is_numeric($service_id)) {
                continue;
            }
            $service = Service::find($service_id);
            if (!$service) {
                continue;
            }
            $service_pay_type = (int) $service->pay_type;
            //same figure the factor total was built from, so line items and total agree
            $register_price = self::resolveRegisterPrice($request->service_register_price, $i, $service->price);
            $line_price = $payment_type == "supervisor"
                ? 0
                : $register_price + ($register_price * $service->tax) / 100;

            //create cash factorItems
            if($service_pay_type === 0)
            {
                $factor_item = $cash_factor->factorItems()->create([
                    'factoritemable_id' => $service_id,
                    'factoritemable_type' => Service::class,
                    'service_item_id' => self::requestItemAt($request->service_item, $i),
                    'count' => 1,
                    'price' => $line_price,
                    'center_id' => auth()->user()->center_id,
                    'type' => 0,
                    'purchase_type' => self::requestItemAt($request->service_purchase_type, $i),
                ]);
                $factor_item->update(['supervisor_id' => $factor_item->id]);
                if($cash_factor && $cash_factor->type == 'supervisor')
                {
                    $cash_factor_result = self::getCashFactorFieldIsSupervisorType($cash_factor , $mobile_supervisor , $factor_item);
                }
            }
            //create credit factorItems
            else
            {
                $total_factor_item = $credit_factor->totalFactorItemReg;
                $factor_item = $credit_factor->factorItems()->create([
                    'factoritemable_id' => $service_id,
                    'factoritemable_type' => Service::class,
                    'service_item_id' => self::requestItemAt($request->service_item, $i),
                    'total_factor_item_id' => $total_factor_item->id,
                    'count' => 1,
                    'price' => $line_price,
                    'center_id' => auth()->user()->center_id,
                    'type' => 1,
                    'purchase_type' => self::requestItemAt($request->service_purchase_type, $i),
                ]);
                $factor_item->update(['supervisor_id' => $factor_item->id]);

                if($credit_factor && $credit_factor->type == 'supervisor')
                {
                    $credit_factor_result = self::getCreditFactorFieldIsSupervisorType($credit_factor , $mobile_supervisor , $factor_item);
                }
            }
        }

        return ['cash_factor_status' => $cash_factor_result['cash_factor_status'] , 'cash_factor_price' => $cash_factor_result['cash_factor_price'] , 'credit_factor_status' => $credit_factor_result['credit_factor_status'] , 'credit_factor_price' => $credit_factor_result['credit_factor_price']];
    }

    /**
     * @param $result
     * @param $request
     * @param $categories
     * @param $cash_factor
     * @param $credit_factor
     * @param $mobile_supervisor
     * @return array
     */
    private function createCategoryFactorItems($result , $request , $categories , $cash_factor , $credit_factor , $mobile_supervisor )
    {
        //one factor per save, so both handles point at the same invoice
        $factor = $credit_factor ?: $cash_factor;
        $payment_type = $factor ? $factor->type : '';

        $cash_factor_result['cash_factor_price'] = $result['cash_factor_price'];
        $cash_factor_result['cash_factor_status'] = $result['cash_factor_status'];
        $credit_factor_result['credit_factor_price'] = $result['credit_factor_price'];
        $credit_factor_result['credit_factor_status'] = $result['credit_factor_status'];

        if ($factor) {
            foreach (self::groupProductRowsByCategory($categories) as $category_id => $indexes)
            {
                $category = Category::find($category_id);
                $product_rows = [];
                $line_price = 0;
                $category_pay_type = 0;

                foreach ($indexes as $i)
                {
                    $product = Product::find(self::requestItemAt($request->product, $i));
                    $attribute = ProductAttribute::find(self::requestItemAt($request->product_attribute, $i));
                    //same figure the factor total was built from, so lines and total agree
                    $register_price = self::resolveRegisterPrice(
                        $request->category_register_price,
                        $i,
                        self::defaultProductRegisterPrice($request, $i, $category)
                    );
                    $row_price = $payment_type == "supervisor"
                        ? 0
                        : $register_price + ($register_price * (optional($category)->tax ?: 0)) / 100;
                    $row_pay_type = $product && $product->pay_type !== null ? (int) $product->pay_type : 0;

                    //an installment product makes the whole category line an installment
                    //line, because total_factor_item_id is carried by the factor item
                    $category_pay_type = ($category_pay_type === 1 || $row_pay_type === 1) ? 1 : 0;
                    $line_price = $line_price + $row_price;

                    $product_rows[] = [
                        'product_id' => optional($product)->id,
                        'attribute_item_id' => optional($attribute)->attribute_item_id,
                        'product_attribute_id' => optional($attribute)->id,
                        'price' => $row_price,
                        'count' => 1,
                        'pay_type' => $row_pay_type,
                        'purchase_type' => self::requestItemAt($request->category_purchase_type, $i),
                        'center_id' => auth()->user()->center_id,
                    ];
                }

                $attributes = [
                    'factoritemable_id' => $category_id,
                    'factoritemable_type' => Category::class,
                    'count' => count($product_rows),
                    'price' => $line_price,
                    'center_id' => auth()->user()->center_id,
                    'type' => $category_pay_type,
                    //per-product delivery lives on each factor_item_products row; the line
                    //keeps the first row's value so existing readers still see something
                    'purchase_type' => $product_rows[0]['purchase_type'],
                ];

                if ($category_pay_type === 1) {
                    $attributes['total_factor_item_id'] = $factor->totalFactorItemReg->id;
                }

                $factor_item = $factor->factorItems()->create($attributes);
                $factor_item->update(['supervisor_id' => $factor_item->id]);
                //product identity and variant live here, one row per کالا row submitted
                $factor_item->products()->createMany($product_rows);

                if ($factor->type == 'supervisor')
                {
                    if ($category_pay_type === 1 && $result['credit_factor_price'] == 0) {
                        $credit_factor_result = self::getCreditFactorFieldIsSupervisorType($factor , $mobile_supervisor , $factor_item);
                    } elseif ($category_pay_type === 0 && $result['cash_factor_price'] == 0) {
                        $cash_factor_result = self::getCashFactorFieldIsSupervisorType($factor , $mobile_supervisor , $factor_item);
                    }
                }
            }
        }

        return ['cash_factor_status' => $cash_factor_result['cash_factor_status'] , 'cash_factor_price' => $cash_factor_result['cash_factor_price'] , 'credit_factor_status' => $credit_factor_result['credit_factor_status'] , 'credit_factor_price' => $credit_factor_result['credit_factor_price']];
    }

    /**
     * The submitted کالا rows bucketed by the category they were filed under, preserving each
     * row's index so the parallel product / variant / price inputs stay aligned.
     *
     * One factor item is created per bucket; the rows inside it become factor_item_products.
     *
     * @param $categories
     * @return array
     */
    private static function groupProductRowsByCategory($categories): array
    {
        $grouped = [];

        foreach ($categories as $i => $category_id) {
            //'all', or an untouched row still holding its placeholder text
            if ($category_id == 'all' || !is_numeric($category_id)) {
                continue;
            }
            $grouped[(int) $category_id][] = $i;
        }

        return $grouped;
    }

    /**
     * @param $callcenter
     * @param $result_factor_items
     * @param Factor $factor the single invoice this save produced
     * @param $services
     * @param $categories
     * @param $payment_type
     */
    private function sendSmsFactor($callcenter , $result_factor_items , Factor $factor, $services, $categories, $payment_type)
    {
        $services_title=implode(' _ ',Service::whereIn('id',$services)->get()->pluck('title')->toArray());

        if ($result_factor_items['sum_price_factors']) {
            event(new sendlinkFactor($callcenter->register, $factor, auth()->user()->family, $payment_type));
            $callcenter->update([
                'status' => $callcenter->status == 1 ? 1 : 0,
            ]);
        }
        //create history
        $description = "لینک  {$services_title} ارسال گردید و در انتظار پرداخت قرار دارد";
        History::create($callcenter , auth()->user()->id , null , 1 , $description);
    }

    /**
     * @param $request
     * @param $client
     * @param $task
     */
    private function sendSmsLinkSite($request , $client , $task,array $services)
    {
        $services_title=implode(' _ ',Service::whereIn('id',$services)->get()->pluck('title')->toArray());
        //create history
        $description = "لینک صفحه {$services_title} جهت مطالعه شرایط و اطلاعات خدمات ارسال گردید .";
        History::create($task , $task->user_id , null , 0 , $description);
        $services = Service::whereIn('id', $request->service)->get();
        $service_items = $request->service_item ? ServiceItem::whereIn('id', $request->service_item)->get() : collect();
        $count = max($services->count(), $service_items->count());
        for ($i = 0; $i < $count; $i++) {
            $service = $services->get($i);
            $service_item = $service_items->get($i);
            if ($service_item) {
                event(new fastserviceCallcenter($client, $service_item, $task, 'serviceItem'));
            } elseif ($service) {
                event(new fastserviceCallcenter($client, $service, $task, 'service'));
            }
        }
    }

    /**
     * Send the product study & payment page link, the کالا counterpart of sendSmsLinkSite().
     *
     * @param $request
     * @param $client
     * @param $task
     * @param array $indexes indexes into the submitted `product[]` rows
     */
    private function sendSmsLinkProduct($request , $client , $task , array $indexes)
    {
        $products = collect($indexes)
            ->map(fn($index) => Product::find(self::requestItemAt($request->product, $index)))
            ->filter()
            ->unique('id');

        if ($products->isEmpty()) {
            return;
        }

        $products_title = implode(' _ ', $products->pluck('name')->toArray());
        //create history
        $description = "لینک صفحه بررسی و پرداخت {$products_title} جهت مطالعه و پرداخت ارسال گردید .";
        History::create($task , $task->user_id , null , 0 , $description);

        foreach ($products as $product) {
            event(new fastserviceCallcenter($client, $product, $task, 'product'));
        }
    }

    private function sendSmsLinkCampign($request , $client , $task,array $services)
    {
        $services_title=implode(' _ ',Service::whereIn('id',$services)->get()->pluck('title')->toArray());
        //create history
        $description = "لینک صفحه {$services_title} جهت مطالعه شرایط و اطلاعات خدمات ارسال گردید .";
        History::create($task , $task->user_id , null , 0 , $description);
        $services=Service::whereIn('id',$request->service)->get();
        foreach ($services as $service ){
            event(new fastserviceCallcenter($client, $service ,$task, 'service'));
        }
    }

    /**
     * @param $cash_factor
     * @param $mobile_supervisor
     * @param $factor_item
     * @return array
     */
    private function getCashFactorFieldIsSupervisorType($cash_factor , $mobile_supervisor , $factor_item)
    {
        $result = FactorItem::setSupervisor($mobile_supervisor , $factor_item);
        if(isset($result['supervisor_factor_item']))
        {
            $cash_factor_price = 0;
            $cash_factor_status = true;
        }
        else
        {
            $cash_factor->type = 'online';
            $cash_factor_price = $cash_factor->price;
            $cash_factor_status = $cash_factor->status;
        }
        return ['cash_factor_status' => $cash_factor_status , 'cash_factor_price' => $cash_factor_price];
    }

    /**
     * @param $credit_factor
     * @param $mobile_supervisor
     * @param $factor_item
     * @return array
     */
    private function getCreditFactorFieldIsSupervisorType($credit_factor , $mobile_supervisor , $factor_item)
    {
        $result = FactorItem::setSupervisor($mobile_supervisor , $factor_item);

        if(isset($result['supervisor_factor_item']))
        {
            $credit_factor_price = 0;
            $credit_factor_status = true;
        }
        else
        {
            $credit_factor->type = 'online';
            $credit_factor_price = $credit_factor->price;
            $credit_factor_status = $credit_factor->status;
        }

        return ['credit_factor_status' => $credit_factor_status , 'credit_factor_price' => $credit_factor_price];
    }

    /**
     * @param $result
     * @param $cash_factor
     * @param $credit_factor
     */
    private function updateFactorFieldIsSupervisorType($result , $cash_factor , $credit_factor)
    {
        $cash_factor ? $cash_factor->update([
            'status' => $result['cash_factor_status'],
            'price' => $result['cash_factor_price']
        ]) : '';

        $credit_factor ? $credit_factor->update([
            'status' => $result['credit_factor_status'],
            'price' => $result['credit_factor_price']
        ]) : '';
    }

    /** Persist the one invoice after supervisor lookup has handled all of its lines. */
    private function updateInvoiceFactorForSupervisor(Factor $factor): void
    {
        if ($factor->type === 'supervisor') {
            $factor->update(['status' => true, 'price' => 0]);
            return;
        }

        //A failed supervisor lookup changes the in-memory type to online in the legacy
        //line-item helpers. Persist that fallback once for the shared factor.
        if ($factor->isDirty('type')) {
            $factor->save();
        }
    }

    /**
     * @param $callcenter
     * @param $result_factor_items
     */
    private function updateCallcenterFieldIsZeroPriceFactor($callcenter , $result_factor_items)
    {
        //update the callcenter status to paid(status == 3) when the factor price is zero.
        $callcenter->update([
            'status' => !$result_factor_items['credit_price'] && !$result_factor_items['cash_price'] ? 10 : $callcenter->status,
            'user_id' => !$result_factor_items['credit_price'] && !$result_factor_items['cash_price'] ? auth()->user()->id : $callcenter->user_id,
        ]);
    }

    /**
     * @param $callcenter
     * @param $payment_type
     */
    private function updateCallcenterFieldIsOnlinePayTypeFactor($callcenter , $payment_type)
    {
        $callcenter->update([
            'status' => ($payment_type == "online" || $payment_type == 'send_card_number' || $payment_type == 'send_shaba_number')? $callcenter->status : 3,
            'user_id' => ($payment_type == "online" || $payment_type == 'send_card_number' || $payment_type == 'send_shaba_number') ? $callcenter->user_id : auth()->user()->id,
        ]);
    }
    /**
     * Summary of closeTask
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function closeTask(Request $request)
    {
        $task = Callcenter::find($request->task);

        if($request->sms_close_task && auth()->user()->isAbleTo('close-task'))
        {
            //create history
            History::create($task , $task->user_id , null , 2 , $request->description);
            if($request->close_task_type == 'this_task')
            {
                $task->update(['status' => 5]);
                $body = [
                    'param1'=> $task->register->name.' '.$task->register->family,
                    'param2'=> $task->type == 1 ? Callcenter::setServiceWithoutFinancialPlansType($task->sms->sender_number, $task) : Callcenter::setServiceWithoutFinancialPlansType($task->register->phone, $task),
                ];
                SendSms::sendSingleSms('closetask',$task->register->phone,$body);
            }
            else
            {
                $task->register->callcenters()->whereIn('status',[0,1,2,4,6])->update(['status' => 5]);
            }

            return response([
                'operator' => true,
                'data'=> ['histories'=>Helper::userHistories($task->register)],
                'status'=>'success',
                'messages'=>'درخواست با موفقیت بسته شد.'
            ]);
        }
        return response([
            'operator' => true,
            'data'=> ['histories'=>Helper::userHistories($task->register)],
            'status'=>'Unauthorized',
            'messages'=>'شما مجاز به انجام این کار نیستید'
        ]);
    }
    /**
     * Summary of followedTask
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function followedTask(Request $request)
    {
        $task = Callcenter::find($request->task);

        if(auth()->user()->isAbleTo('followed-task'))
        {
            //create history
            History::create($task , $task->user_id , null , 2 , $request->description);
            $task->update(['status' => 10]);

            return response([
                'operator' => true,
                'data'=> ['histories'=>Helper::userHistories($task->register)],
                'status'=>'success',
                'messages'=>'وضعیت درخواست با موفقیت تغییر کرد.'
            ]);
        }
        return response([
            'operator' => true,
            'data'=> ['histories'=>Helper::userHistories($task->register)],
            'status'=>'Unauthorized',
            'messages'=>'شما مجاز به انجام این کار نیستید'
        ]);
    }
    /**
     * @param Factor $factor
     * @return void
     */
    private function getFactorRepresentation(Factor $factor)
    {
        $representation = null;
        $factor_items = $factor->factorItems;
        foreach($factor_items as $item)
        {
            //A representation is a service-level concept and Service::setRepresentation() is
            //typed to Service, so product (Category) lines are skipped rather than passed in.
            //Without this, any invoice containing a product raises a TypeError here.
            if (!$item->factoritemable instanceof Service)
            {
                continue;
            }
            $representation = Service::setRepresentation($item->factoritemable);
            if($representation == null)
            {
                break;
            }
        }
        self::changeFactorRepresentation($factor , $representation);
    }
    /**
     * @param Factor $factor
     * @param int $representation
     * @return void
     */
    private function changeFactorRepresentation(Factor $factor , $representation)
    {
        //change the factor representation if it has not been set before
        $factor->representation ? '' : $factor->update(['representation_id' => $representation]);
    }
    /**
     * @param Request $request
     * @param Callcenter $callcenter
     * @return void
     */
    private function suspensionBasedOnStandards(Request $request , Callcenter $callcenter)
    {
        if (!$callcenter->suspensionStandard()->exists()) {
            $suspension_standard = SuspensionStandard::first();
            $callcenter->suspensionStandard()->create(['suspension_standard_id' => $suspension_standard->id]);
        }
        else
        {
            $suspension_standard = self::getNextSuspensionStandard($callcenter);
            if(isset($suspension_standard->id))
            {
                $callcenter->suspensionStandard->update(['suspension_standard_id' => $suspension_standard->id]);
            }
            else
            {
                $request->task = $callcenter->id;
                $request->close_task_type = 'all_user_task';
                self::closeTask($request);
                $description = 'به دلیل عدم پاسخگویی کاربر، تمام درخواست های باز کاربر بسته شد.';
            }
        }
        //suspention task
        if(isset($suspension_standard->id))
        {
            $callcenter->update(['date_expire'=> now()->addMinutes($suspension_standard->waitting_time*60)]);
            $description = $suspension_standard->name .' تماس گرفته شد ارتباط با کاربر برقرار نشد.';
        }
        //create history
        $status = $request->reason == 0 ? 3 : 2;
        History::create($callcenter , $callcenter->user_id , $request->reason , $status , $description);
    }
    /**
     * @param Callcenter $callcenter
     * @return mixed
     */
    private function getNextSuspensionStandard(Callcenter $callcenter)
    {
        $next_suspension_standard_id = SuspensionStandard::where('id', '>', $callcenter->suspensionStandard->suspension_standard_id)->min('id');
        $next_suspension_standard = SuspensionStandard::find($next_suspension_standard_id);
        return $next_suspension_standard;
    }

}
