<?php

namespace App\Http\Controllers\Admin;

use App\Classes\External\SendSms;
use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;
use App\Models\GhorfeOnlineList;
use App\Models\Representation;
use App\Models\Employee;
use App\Models\Rating;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Models\UserPerformance;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\EmployeeCollection;
use App\Http\Resources\WalletTransactionCollection;
use App\Models\AuthCode;
use App\Models\Callcenter;
use App\Models\History;
use App\Models\RecruitmentNotice;
use App\Models\Resume;
use App\Services\V1\Admin\EmployeeService;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class EmployeeController extends AdminController
{
    /**
     * Shown to a business admin who tries to take on somebody another shop already has.
     * The edit form states it up front and update() repeats it if the field is posted anyway.
     */
    public const BELONGS_TO_ANOTHER_BUSINESS =
        'این کاربر هم اکنون همکار کسب و کار دیگری است. برای تغییر، لطفا درخواست خود را به مدیر سیستم ارسال کنید.';

    public function __construct(
        protected EmployeeService $employeeService
    ) {}
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->isAbleTo('employee-detail')) {
            $can_manual_login = false;
            if ($user->isAbleTo('manual-login-user')){
                $can_manual_login = true;
            }
            return view('Admin.employee.all',compact('can_manual_login'));
        }

        return abort(401);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function sellers()
    {
        $callcenter_roles= Role::where('name','like','%callcenter%')->get();
        $parents=Employee::where('status',1)->get();
            return view('Admin.employee.sellers',compact('callcenter_roles','parents'));
    }


    /**
     * @return EmployeeCollection
     */
    public function filter()
    {
        $employees = Employee::filter()->orderBy('updated_at', 'desc');

        return new EmployeeCollection($employees->paginate(20));
    }


    /**
     * @param Request $request
     * @return EmployeeCollection
     */
    public function sellerFilter(Request $request)
    {
        $employees = Employee::filter()->orderBy('updated_at', 'desc');

        $employees->whereHas('user', function ($query) use($request) {
            $query->whereHas('roles', function ($query) use($request) {
                if (isset($request->roles)){
                    $query->whereIn('id',$request->roles);
                }else{
                    $query->whereIn('name',array('callcenter','digital_superadmin-callcenter','superadmin-callcenter'));
                }
            });
        });

        return new EmployeeCollection($employees->paginate(20));
    }

    /**
     * New-employee form.
     *
     * `Route::resource('employee', ...)` has always mapped create/store, but neither method
     * existed — hitting /dashboard/employee/create threw BadMethodCallException. An employee
     * could only appear by registering through the public form.
     *
     * Two audiences, told apart by `users.level`:
     *
     *  - superAdmin provisions لاله staff and may aim the new employee anywhere: role,
     *    branch, representation and business are all theirs to pick.
     *  - ghorfeAdmin provisions their own staff: role and branch. The branch list is their
     *    own; the role list is every role for now, pending a decision on which roles a
     *    business may hand out. Everything that decides WHICH business the person ends up in
     *    is still taken from the creator and never from the form.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->authorizeEmployeeCreation();

        $business = $this->actorBusiness();

        return view('Admin.employee.create', [
            'is_super_admin'  => $this->actorIsSuperAdmin(),
            'business'        => $business,
            'businesses'      => $this->actorIsSuperAdmin()
                ? GhorfeOnlineList::orderBy('title')->get()
                : collect(),
            'representations' => Representation::whereStatus(true)->get(),
            'centers'         => $this->selectableCenters($business)->get(),
            'roles'           => Role::orderBy('display_name')->get(),
            'parents'         => $this->selectableParents($business),
        ]);
    }

    /**
     * The quick-action user search, on the employee form.
     *
     * Deliberately the very same endpoint the "اقدام سریع" page calls
     * (CallcenterController::fetchUser): it looks a phone number up across ALL users with no
     * business scope at all, which is the point — a business associate must be able to find
     * someone who has never dealt with their shop (requirement 3). Duplicating the lookup
     * here with a scope of its own is exactly the bug this reuse avoids.
     *
     * What is added on top is the one thing the quick action has no reason to know: whether
     * this person can be taken on as an associate, so the form can say so before anything is
     * submitted rather than failing at save time (requirement 7).
     *
     * Named searchUser rather than fetchUser because AdminController — the parent — already
     * has a fetchUser of its own with a different signature.
     *
     * @param Request $request
     * @param CallcenterController $callcenter
     * @return \Illuminate\Http\Response
     */
    public function searchUser(Request $request, CallcenterController $callcenter)
    {
        $this->authorizeEmployeeCreation();

        $request->validate(['phone' => 'required|string|max:20']);

        $payload = $callcenter->fetchUser($request)->getOriginalContent();

        $user     = User::where('phone', Helper::numberConverter($request->phone))->first();
        $business = $this->actorBusiness();

        // is_employee means "already someone's staff", which is an employee row WITH a
        // business on it. An employee row without one is a لاله employee, or someone a
        // business let go — either way there is no business to take them from, so they can
        // be adopted here and the form must not refuse them.
        //
        // blocked_by carries the sentence the form shows and store() would repeat, from the
        // one rule both share — so the search never says "ok" to something the save rejects,
        // nor refuses مدیر سیستم a move that is theirs to make.
        $payload['data']['associate'] = [
            'exists'      => (bool) $user,
            'is_employee' => (bool) optional($user)->employee?->ghorfe_online_list_id,
            'business'    => $user ? optional(optional($user->employee)->ghorfeOnline)->title : null,
            'blocked_by'  => $this->claimRefusal($user),
        ];

        return response($payload);
    }

    /**
     * Create the user (or adopt an existing one), its employee row and — when the creator
     * belongs to a business — the association that puts the new employee in that business.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorizeEmployeeCreation();

        $request->merge(['phone' => Helper::numberConverter($request->phone)]);

        $is_super_admin = $this->actorIsSuperAdmin();

        $request->validate([
            'name'                    => 'required|string|max:110',
            'family'                  => 'required|string|max:110',
            'phone'                   => 'required|regex:/^09[0-9]{9}$/',
            'melicode'                => 'nullable|digits:10',
            // User::createUser — the quick-action user store this form reuses — requires
            // gender/province/city, so they are required here too rather than being posted
            // empty and rejected deeper in.
            'gender'                  => ['required', Rule::in([0, 1])],
            'province'                => 'required|integer|exists:provinces,id',
            'city'                    => 'required|integer|exists:cities,id',
            'password'                => 'nullable|string|min:6|confirmed',
            'work_title'              => 'nullable|string|max:110',
            'business_phone'          => 'nullable|string|max:30',
            'status'                  => ['required', Rule::in([0, 1])],
            'show_status'             => ['required', Rule::in([0, 1])],
            'role_id'                 => 'nullable|integer|exists:roles,id',
            'admin_representation_id' => $is_super_admin ? 'nullable|integer|exists:representations,id' : 'prohibited',
            'ghorfe_online_list_id'   => $is_super_admin ? 'nullable|integer|exists:ghorfe_online_lists,id' : 'prohibited',
            'center_id'               => 'nullable|integer|exists:centers,id',
            'parent_id'               => 'nullable|integer|exists:employees,id',
        ], [
            'phone.regex'     => 'شماره موبایل باید با 09 شروع شود و ۱۱ رقم باشد',
            'melicode.digits' => 'کد ملی باید ۱۰ رقم باشد',
            'gender.required' => 'انتخاب جنسیت الزامی است',
            'province.required' => 'انتخاب استان الزامی است',
            'city.required'   => 'انتخاب شهر الزامی است',
        ]);

        $business = $this->targetBusiness($request);

        // A ghorfeAdmin may only place staff in their own branches; a posted id from another
        // business would otherwise walk the new employee straight out of the tenancy.
        // Checked against what the form offered — actorBusiness(), not the target business —
        // so a superAdmin keeps the free choice their form gives them.
        if ($request->filled('center_id')
            && ! $this->selectableCenters($this->actorBusiness())->where('centers.id', $request->center_id)->exists()) {
            return $this->rejectWith('شعبه انتخاب شده متعلق به این کسب و کار نیست.');
        }

        // Same reasoning for the supervisor: only someone the form actually offered.
        if ($request->filled('parent_id')
            && ! $this->selectableParents($this->actorBusiness())->contains('id', (int) $request->parent_id)) {
            return $this->rejectWith('سرپرست انتخاب شده متعلق به این کسب و کار نیست.');
        }

        $user = User::where('phone', $request->phone)->first();

        // An existing customer being promoted to staff keeps their row (and their history);
        // only a genuinely new phone number creates one. Refusing outright would strand
        // anyone who had ever placed an order.
        //
        // Only somebody another business already has is refused, by the same rule searchUser
        // reported while the phone number was typed — so the form never says "ok" to something
        // the save then rejects, and never refuses مدیر سیستم a move that is theirs to make.
        if ($refusal = $this->claimRefusal($user)) {
            return $this->rejectWith($refusal);
        }

        // ...but a business admin must not rewrite لاله's own staff accounts — their level,
        // their roles and their business — from this form.
        //
        // لاله staff means level admin/superAdmin, the same test the edit page applies.
        // NOT ghorfeAdmin: that level stays behind when somebody is released from a business
        // (nothing demotes them), so counting it as "has panel access" made a released
        // associate impossible to take on a second time — refused as staff while belonging to
        // no one. Whoever still belongs to a business was already turned away above, by
        // claimRefusal(), with a message that actually explains why.
        if ($user && ! $this->actorIsSuperAdmin() && in_array($user->level, ['admin', 'superAdmin'], true)) {
            return $this->rejectWith('این کاربر هم اکنون دسترسی پنل دارد و از این فرم قابل ثبت نیست.');
        }

        $user = $this->createOrAdoptUser($request, $user, $business);

        if (! $user) {
            return $this->rejectWith('ثبت کاربر انجام نشد. اطلاعات وارد شده را بررسی کنید.');
        }

        $attributes = [
            'status'         => $request->status,
            'show_status'    => $request->show_status,
            'work_title'     => $request->work_title,
            'business_phone' => $request->business_phone,
            'parent_id'      => $request->parent_id,
            // kept in step with users.center_id — the branch staff list reads this one
            'center_id'      => $request->center_id,
            // Which business the person works for. A column on THIS row, not a
            // ghorfe_online_list_user pivot row: the pivot is about the user account (it
            // holds customers too), so writing it here conflated "this account belongs to
            // the business" with "this person is its staff". GhorfeOnlineList::staff() and
            // the "درباره ما" page read this column.
            'ghorfe_online_list_id' => optional($business)->id,
        ];

        // updateOrCreate, because an unattached employee reaches this point: a second
        // employees row for the same user would give them two staff records and make
        // `$user->employee` pick one arbitrarily.
        $employee = Employee::updateOrCreate(['user_id' => $user->id], $attributes);

        $this->syncRole($request, $user);

        return redirect()->route('employee.edit', $employee->id)->with([
            'create' => 'success',
        ]);
    }

    /**
     * Write the users row, reusing the quick-action user store.
     *
     * `User::createUser()` is the same call CallcenterController::registerForm makes, and it
     * is left to decide on its own whether the phone number is new — it returns a response,
     * not a model, so the row is read back by phone exactly as the quick action does.
     *
     * An existing row is updated instead: promoting a customer must not orphan their orders
     * by creating a second account for the same number.
     */
    private function createOrAdoptUser(Request $request, ?User $existing, ?GhorfeOnlineList $business): ?User
    {
        $attributes = [
            'name'        => $request->name,
            'family'      => $request->family,
            'melicode'    => $request->melicode,
            'sex'         => $request->gender,
            'call'        => $request->call,
            'address'     => $request->address,
            'province_id' => $request->province,
            'city_id'     => $request->city,
            'center_id'   => $request->center_id,
            // Staff of a business are ghorfeAdmin, staff of لاله are admin: `level` is what
            // the panel reads to tell the two apart. status 0 means the person is not staff
            // yet, so no panel level at all.
            'level'       => $request->status != 1 ? 'user' : ($business ? 'ghorfeAdmin' : 'admin'),
            'active'      => true,
        ];

        if ($this->actorIsSuperAdmin()) {
            $attributes['admin_representation_id'] = $request->admin_representation_id;
        }

        if ($existing) {
            $existing->update($attributes);

            if ($request->filled('password')) {
                $existing->update(['password' => Hash::make($request->password)]);
            }

            return $existing;
        }

        User::createUser($request);

        $user = User::where('phone', $request->phone)->first();

        if (! $user) {
            return null;
        }

        // createUser() writes the customer-shaped row (name/family/gender/province/city and a
        // random password); the staff fields and a typed password are layered on here.
        $user->update($attributes + [
            'password' => Hash::make($request->password ?: rand(12345, 99999)),
            'status'   => true,
        ]);

        return $user;
    }

    /**
     * The role the creator picked, and nothing implied.
     *
     * Leaving the field empty means exactly that: the associate is created with no role and
     * stays permission-less until someone grants one — the association is what lets them into
     * the panel, the role is what they may then do, and the second is a deliberate act.
     */
    private function syncRole(Request $request, User $user): void
    {
        if ($request->filled('role_id')) {
            $user->roles()->sync([$request->role_id]);
        }
    }

    /**
     * Which business the new employee is an associate of.
     *
     * For a ghorfeAdmin this is their own, taken from the session and never from the form —
     * requirement 6: an associate of one business must not reach another one.
     */
    private function targetBusiness(Request $request): ?GhorfeOnlineList
    {
        if (! $this->actorIsSuperAdmin()) {
            return $this->actorBusiness();
        }

        return $request->filled('ghorfe_online_list_id')
            ? GhorfeOnlineList::find($request->ghorfe_online_list_id)
            : null;
    }

    /** The business the signed-in user is an associate of, if any. */
    private function actorBusiness(): ?GhorfeOnlineList
    {
        return $this->actorIsSuperAdmin() ? null : ghorfe();
    }

    /**
     * Branches the form may offer: a business's own ones, or every active branch for لاله.
     *
     * A query rather than Center::filter() on purpose — that scope reads request('status'),
     * which this form posts with the EMPLOYEE status and would silently narrow the list.
     */
    private function selectableCenters(?GhorfeOnlineList $business)
    {
        return $business
            ? $business->centers()->getQuery()->orderBy('title')
            : \App\Models\Center::where('status', true)->orderBy('title');
    }

    /**
     * Supervisors the form may offer: a business only ever sees its own associates.
     *
     * The list is the staff picker, so it is scoped the same way the staff LIST is
     * (Employee::scopeFilter) — through the user's business — rather than showing a business
     * admin all 400 لاله employees to choose a supervisor from.
     */
    private function selectableParents(?GhorfeOnlineList $business)
    {
        $parents = Employee::with('user:id,name,family')->whereHas('user')->where('status', 1);

        if ($business) {
            $parents->where('ghorfe_online_list_id', $business->id);
        }

        return $parents->get();
    }

    /** Whether the signed-in user provisions لاله staff rather than a business's staff. */
    private function actorIsSuperAdmin(): bool
    {
        $user = auth()->user();

        return $user && ($user->level === 'superAdmin' || $user->hasRole('superadmin'));
    }

    /**
     * Creating an employee provisions a panel account, so it is limited to the two levels
     * that own such accounts: لاله's superAdmin and a business's own ghorfeAdmin.
     */
    private function authorizeEmployeeCreation(): void
    {
        $user = auth()->user();

        // A ghorfeAdmin with no business has nothing to add staff to — canCreateEmployee()
        // is the same rule the "ایجاد همکار" button is drawn from.
        if (! $user || ! $user->canCreateEmployee()) {
            abort(401);
        }
    }

    /** Back to the form with the typed values and one sentence saying what went wrong. */
    private function rejectWith(string $message)
    {
        return redirect()->back()->withInput()->with(['failByMessage' => $message]);
    }

    /**
     * Why this person cannot be taken on as staff here, or '' when they can.
     *
     * One sentence, one rule, two callers: searchUser shows it the moment the number is
     * looked up and store() repeats it on save, so the form cannot promise something the
     * save refuses.
     *
     * Nobody has them yet → free to take. مدیر سیستم asking → also free, because moving staff
     * between businesses is theirs to do. Otherwise it depends on whose staff they are:
     * already yours is a plain duplicate, somebody else's is a request to مدیر سیستم.
     */
    private function claimRefusal(?User $user): string
    {
        $current = optional(optional($user)->employee)->ghorfe_online_list_id;

        if (! $current || $this->actorIsSuperAdmin()) {
            return '';
        }

        return (int) $current === (int) optional($this->actorBusiness())->id
            ? 'برای این شماره موبایل قبلا همکار ثبت شده است.'
            : self::BELONGS_TO_ANOTHER_BUSINESS;
    }

    /**
     * The businesses the edit form may offer for this employee.
     *
     * A business admin takes staff on, but never takes them FROM anybody: they may assign
     * only while `ghorfe_online_list_id` is still null, and only their own business. Someone
     * who already works for another shop is a decision only مدیر سیستم can revisit — the form
     * says so instead of offering a control that would be refused.
     *
     * لاله's superAdmin picks freely, before or after an assignment, and that is how an
     * employee gets moved between businesses.
     *
     * EmployeeService enforces the same rule on the posted field, so which options render
     * here is presentation, not protection.
     *
     * @return \Illuminate\Support\Collection<int, GhorfeOnlineList>
     */
    private function assignableBusinesses(Employee $employee)
    {
        if ($this->actorIsSuperAdmin()) {
            return GhorfeOnlineList::orderBy('title')->get(['id', 'title']);
        }

        if ($employee->ghorfe_online_list_id) {
            return collect();
        }

        $business = $this->actorBusiness();

        // لاله's own staff have no business by definition, which would otherwise make every
        // one of them claimable. Told apart by users.level, the same test
        // EmployeeService::assignableBusinessId applies to the posted field.
        if (! $business || ! $employee->user || in_array($employee->user->level, ['admin', 'superAdmin'], true)) {
            return collect();
        }

        return GhorfeOnlineList::whereKey($business->id)->get(['id', 'title']);
    }

    /**
     * @param Employee $employee
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(Employee $employee)
    {
        $representations = Representation::whereStatus(true)->get();
        $employees=$employee::where('status',1)->get();
        $recruitment_notices = RecruitmentNotice::where('status', 1)->orderBy('updated_at', 'desc')->get();
        $resume = Resume::where('phone' , $employee->user->phone)->get()->last();
        $recruitment_notice_selected = !is_null($resume) ? $resume->recruitment_notice_id : null;
        if(session()->has('representation'))
        {
            $recruitment_notices = session()->get('representation')->recruitmentNotices()->get();
        }
        $employee->resume_detail = json_decode($employee->resume_detail , true);
        $item['contract_histories'] = $employee->histories->where('reason_id' , 3);

        // "پروفایل عمومی" tab — what the partner-profile frontend renders.
        $profileServices = Service::filter()->orderBy('title')->get(['services.id', 'services.title']);
        $ratings         = $employee->ratings()->latest()->get();

        // Businesses this employee may still be assigned to — empty once they have one.
        $ghorfeOnlineLists = $this->assignableBusinesses($employee);

        return view('Admin.employee.edit', compact('item','employee', 'representations','employees','recruitment_notices','recruitment_notice_selected',
            'profileServices','ratings','ghorfeOnlineLists'));
    }

    /**
     * Save the "پروفایل عمومی" tab: everything the partner-profile frontend reads.
     *
     * A dedicated endpoint rather than employee.update, mirroring the resume and contract
     * tabs: the profile owns relations (services, portfolio, ratings) that EmployeeService
     * knows nothing about, and folding them in would make the shared update path carry
     * four concerns instead of one.
     *
     * @param Request $request
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request, Employee $employee)
    {
        if (! auth()->user()->isAbleTo('user-edit')) {
            return abort(401);
        }

        $request->validate([
            'bio'                => 'nullable|string|max:5000',
            // CKEditor output — html, so no `string|max` character cap that would truncate
            // legitimate markup; the column is longtext.
            'extra_content'      => 'nullable|string',
            'attendance_days'    => 'nullable|string|max:255',
            'attendance_hours'   => 'nullable|string|max:255',
            'description_one'    => 'nullable|string|max:1000',
            'description_two'    => 'nullable|string|max:1000',
            'description_three'  => 'nullable|string|max:1000',
            'services_title'     => 'nullable|string|max:255',
            'portfolio_title'    => 'nullable|string|max:255',
            'service_ids'        => 'nullable|array',
            'service_ids.*'      => 'integer|exists:services,id',
            'service_subtitle'   => 'nullable|array',
            'service_subtitle.*' => 'nullable|string|max:255',
            'service_from_price' => 'nullable|array',
        ]);

        // Named one by one, never $request->all(): Employee is $guarded=['id'], so a
        // wholesale update from this tab could reach pay_type / percent / contract_status.
        $employee->update($request->only([
            'bio', 'extra_content', 'attendance_days', 'attendance_hours',
            'description_one', 'description_two', 'description_three',
            'services_title', 'portfolio_title',
        ]));

        if ($request->input('profile_image_path')) {
            MediaLibraryHelper::attachTo($employee, $request->input('profile_image_path'), 'profile_image');
        }

        $this->syncProfileServices($employee, $request);
        $this->syncPortfolio($employee, $request);
        $this->moderateRatings($employee, $request);

        return redirect()->route('employee.edit', $employee->id)->with([
            'update'     => 'success',
            'active_tab' => 'public_profile',
        ]);
    }

    /**
     * The "خدمات" cards: which services the partner offers, and the per-partner subtitle
     * and starting price of each.
     *
     * The subtitle/price arrays are keyed by SERVICE ID, not by position — unticking a
     * service in the middle of the list would otherwise shift every later value up onto
     * the wrong service.
     */
    private function syncProfileServices(Employee $employee, Request $request): void
    {
        $subtitles = $request->input('service_subtitle', []);
        $prices    = $request->input('service_from_price', []);

        $payload = [];
        foreach (array_values($request->input('service_ids', [])) as $index => $serviceId) {
            // prices are typed with thousands separators and often on a Persian keyboard
            $price = preg_replace('/\D/', '', Helper::convert($prices[$serviceId] ?? ''));

            $payload[(int) $serviceId] = [
                'subtitle'   => $subtitles[$serviceId] ?? null,
                'from_price' => $price === '' ? null : (int) $price,
                'sort'       => $index,
            ];
        }

        $employee->services()->sync($payload);
    }

    /**
     * "نمونه کارها": retitle the saved images and attach the newly uploaded ones.
     *
     * Same shape as ServiceController::uploadPortfolioImages — an unlimited Spatie
     * `portfolio` collection with the caption in the media's `title` custom property, fed
     * by the shared multipleImages widget. Deletion is not handled here: the ✕ on each row
     * is the widget's own removeMedia ajax call, which fires immediately.
     */
    private function syncPortfolio(Employee $employee, Request $request): void
    {
        foreach ($request->input('existing_portfolio', []) as $mediaId => $data) {
            $media = \App\Models\Media::find($mediaId);

            // ownership check: a posted media id must not retitle another model's image
            if ($media && $media->model_type === Employee::class && (string) $media->model_id === (string) $employee->id) {
                $media->setCustomProperty('title', $data['title'] ?? '');
                $media->save();
            }
        }

        foreach ($request->input('multiple_image', []) as $item) {
            if (! empty($item['file'])) {
                MediaLibraryHelper::attachTo($employee, $item['file'], 'portfolio', [
                    'title' => $item['title'] ?? '',
                ]);
            }
        }
    }

    /**
     * Approve / reject / delete the visitor ratings of this partner. Ratings are written
     * by the public API — the admin only decides what the frontend may show.
     */
    private function moderateRatings(Employee $employee, Request $request): void
    {
        foreach ($request->input('rating_status', []) as $id => $status) {
            if (! array_key_exists((int) $status, Rating::STATUS)) {
                continue;
            }
            $employee->ratings()->where('id', $id)->update(['status' => (int) $status]);
        }

        if ($deleted = array_filter($request->input('rating_delete', []))) {
            $employee->ratings()->whereIn('id', $deleted)->delete();
        }
    }

    /**
     * @param Request $request
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Employee $employee)
    {
        // Taking on someone who already works for another business is not a business admin's
        // call — say so, rather than letting the save report success while the service quietly
        // drops the field. مدیر سیستم can make the move.
        if ($request->filled('ghorfe_online_list_id')
            && $employee->ghorfe_online_list_id
            && (int) $request->ghorfe_online_list_id !== (int) $employee->ghorfe_online_list_id
            && ! $this->actorIsSuperAdmin()) {
            return $this->rejectWith(self::BELONGS_TO_ANOTHER_BUSINESS);
        }

        return $this->employeeService->update($request, $employee);
    }
    /**
     * @param Employee $employee
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function financialReport(Employee $employee)
    {
        $user = $employee->user;
        $data_access_date = $employee->data_access_date ? $employee->data_access_date : now();
        $items = WalletTransaction::where('user_id', $user->id)->where('created_at', '>=', $data_access_date)->filter()->latest()->get()->each(function ($item) {
            $item->verta_created_at = verta($item->created_at)->format('Y-m');
        })->groupBy('verta_created_at')->map(function (Collection $item) {
            return [
                'year' => verta($item[0]->created_at)->format('Y'),
                'month' => verta($item[0]->created_at)->format('%B'),
                'amount_received' => WalletTransaction::whereBetween('created_at', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()->subDays(1)), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth())])->whereType('0')->where('user_id', $item[0]->user_id)->sum('price'),
                'item_count' => UserPerformance::whereBetween('date', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth()->addDays(1))])->where('user_id', $item[0]->user_id)->distinct('factor_item_id')->count('factor_item_id'),
                'total_price' => UserPerformance::whereBetween('date', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth()->addDays(1))])->where('user_id', $item[0]->user_id)->sum('price'),
                'ancillary_costs' => UserPerformance::whereBetween('date', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth()->addDays(1))])->where('user_id', $item[0]->user_id)->sum('ancillary_costs'),
                'user_tax' => UserPerformance::whereBetween('date', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth()->addDays(1))])->where('user_id', $item[0]->user_id)->sum('tax'),
                'center_income' => WalletTransaction::whereBetween('created_at', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth()->addDays(1))])->where('user_id', $item[0]->user_id)->whereType('1')->sum('price'),
                'created_at' => $item[0]->created_at->format("Y-m-d"),
            ];
        });

        //all user performance
        $walletTransactions = WalletTransaction::where('created_at', '>=', $data_access_date)->where('created_at', '<=', Helper::vertaTocarbon(verta()->startDay()->addDays(1)))->where('user_id', $user->id)->filter()->latest();
        $item = [];
        $item['total_income'] = WalletTransaction::where('created_at', '>=', $data_access_date)->where('created_at', '<=', Helper::vertaTocarbon(verta()->startDay()->addDays(1)))->whereStatus('1')->where('user_id', $user->id)->whereType('1')->sum('price');
        $item['amount_received'] = WalletTransaction::where('created_at', '>=', $data_access_date)->where('created_at', '<=', Helper::vertaTocarbon(verta()->startDay()->addDays(1)))->where('user_id', $user->id)->whereType('0')->sum('price');
        $item['credit_amount'] = $item['total_income'] - $item['amount_received'];
        $item['total_tax'] = UserPerformance::where('created_at', '>=', $data_access_date)->where('user_id', $user->id)->sum('tax');
        $item['amount_payable'] = $item['total_income'] - $item['amount_received'] - $item['total_tax'];
        $item['shaba_number'] = $user->employee->shaba_number;

        return view('Admin.employee.financialReport', compact('item', 'items', 'user', 'employee'));
    }

    /**
     * @param User $user
     * @param $key
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function walletTransaction(User $user, $key)
    {
        return view('Admin.employee.walletTransaction', compact('user', 'key'));
    }

    /**
     * @param Request $request
     * @param User $user
     * @return WalletTransactionCollection
     */
    public function walletTransactionFilter(Request $request, User $user)
    {
        $data_access_date = $user->employee->data_access_date ? $user->employee->data_access_date : now();
        //all walletTransactions user
        $wallet_transactions = WalletTransaction::where('created_at', '>=', $data_access_date)->where('created_at', '<=', Helper::vertaTocarbon(verta()->startDay()))->where('user_id', $user->id)->filter()->latest();
        //filter walletTransactions user by created_at value
        if (isset($request->key) && $request->key != 'all') {
            $wallet_transactions = WalletTransaction::whereBetween('created_at', [Helper::vertaTocarbon(verta($request->key)->startMonth()), Helper::vertaTocarbon(verta($request->key)->endMonth()->addDays(1))])->where('user_id', $user->id)->filter()->latest();
        }

        return new WalletTransactionCollection($wallet_transactions, $wallet_transactions->paginate(20));
    }

    /**
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function performanceDetail(User $user, Request $request)
    {
        $data_access_date = $user->employee->data_access_date ? $user->employee->data_access_date : now();

        $user_performances = UserPerformance::where('created_at', '>=', $data_access_date)->where('created_at', '<=', Helper::vertaTocarbon(verta()->startDay()->addDays(1)))->where('user_id', $user->id)->groupBy('factor_item_id')->filter()->latest()->get();
        if (isset($request->key) && $request->key != 'all') {
            $user_performances = UserPerformance::where('created_at', '>=', $data_access_date)->whereBetween('date', [Helper::vertaTocarbon(verta($request->key)->startMonth()->addDays(1)), Helper::vertaTocarbon(verta($request->key)->endMonth()->addDays(1))])->where('user_id', $user->id)->groupBy('factor_item_id')->filter()->latest()->get();
        }
        return view('Admin.employee.performanceDetail', compact('user', 'user_performances'));
    }

    /**
     * @param Request $request
     * @return void
     */
    public function acceptTerms(Request $request)
    {
        if ($request->terms == 'on'){
            auth()->user()->employee->update([
               'is_terms_accept'=>1
            ]);
            alert()->success(' عملیات موفق', 'ثبت با موفقیت انجام پذیرفت ');
            return redirect()->back();
        }
    }
    /**
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function financialReportEmployeeList()
    {
        if (auth()->user()->isAbleTo('financialReport_employee_list')) {
            return view('Admin.employee.financialReportEmployeeList');
        }

        return abort(401);
    }

    /**
     * @param \App\Models\Employee $employee
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function editResume(Employee $employee)
    {
        if (auth()->user()->employee->id == $employee->id) {
            $recruitment_notices = RecruitmentNotice::where('status', 1)->orderBy('updated_at', 'desc')->get();
            $resume = Resume::where('phone' , $employee->user->phone)->get()->last();
            $recruitment_notice_selected = !is_null($resume) ? $resume->recruitment_notice_id : null;
            if(session()->has('representation'))
            {
                $recruitment_notices = session()->get('representation')->recruitmentNotices()->get();
            }
            $employee->resume_detail = json_decode($employee->resume_detail , true);
            return view('Admin.employee.resume.index' , compact('employee' , 'recruitment_notices' , 'recruitment_notice_selected'));
        }

        return abort(401);
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateResume(Employee $employee , Request $request)
    {
        if (auth()->user()->isAbleTo('employee_resume_detail') || auth()->user()->employee->id == $employee->id)
        {
            $this->validate(request(), [
                'email' => 'nullable|email',
                'text' => 'nullable|string',
                'sex' => ['required',Rule::in(array(0,1))],
            ]);

            $recruitment_notice = RecruitmentNotice::find($request->recruitment_notice_id);

            if (is_null($recruitment_notice)) {
                return redirect()->back()->with([
                    'failByMessage' => 'انتخاب عنوان شغلی الزامی است.'
                ]);
            }

            $user = $employee->user;

            $callcenter_roles = Role::select('id')->where('card_id' , 1)->get();
            $description = " تکمیل فرم رزومه نهایی،
                 ،
              عنوان شغلی:
            {$recruitment_notice->title}";
            Callcenter::createTask($user, $description , $callcenter_roles);
            $this->updateEmployeeResume($employee , $request);

            return redirect()->back()->with([
                'successByMessage' => 'رزومه کاری شما با موفقیت ثبت شد.'
            ]);
        }
        return abort(401);
    }

    /**
     * @param $request
     * @param \App\Models\Employee $employee
     * @return void
     */
    public function updateEmployeeResume(Employee $employee , $request)
    {
        $bime_history = 0;
        if (!is_null($request->bime_history)) {
            $bime_history = $request->bime_history;
        }

        $employee->user->update([
            'sex' => $request->sex,
            'call' => $request->call,
            'melicode' => $request->melicode,
            'address' => $request->address,
            'shenasnamecode' => $request->shenasnamecode,
            'email' => $request->email,
        ]);

        $employee->update([
            'resume_detail' =>[
                "birth_certificate_issuing_place" => $request->input('birth_certificate_issuing_place'),
                "date_of_birth" => $request->input('date_of_birth'),
                "religion" => $request->input('religion'),
                "nationality" => $request->input('nationality'),
                "marital_status" => $request->input('marital_status'),
                "housing_situation" => $request->input('housing_situation'),
                "bime_history" => $bime_history,
                "work_type" => $request->work_type,
                "work_history" => $request->work_history,
                "text" => $request->text,
                "military_service_status" => $request->input('military_service_status'),
                "exemption_military_service" => $request->input('exemption_military_service'),
                "exemption_military_service_type" => $request->input('exemption_military_service_type'),
                "exemption_military_service_end_date" => $request->input('exemption_military_service_end_date'),
                "military_service_location" => $request->input('military_service_location'),
                "military_service_end_date" => $request->input('military_service_end_date'),
                "degree_of_education" => $request->input('degree_of_education'),
                "field_of_Study" => $request->input('field_of_Study'),
                "total_average" => $request->input('total_average'),
                "education_location" => $request->input('education_location'),
                "education_end_date" => $request->input('education_end_date'),
                "cooperation_type" => $request->input('cooperation_type'),
                "description_half_time" => $request->input('description_half_time'),
                "en_lang_quality" => $request->input('en_lang_quality'),
                "computer_skill" => $request->input('computer_skill'),
                "start_work_date" => $request->input('start_work_date'),
                "transfer_job" => $request->input('transfer_job'),
                "busy_now" => $request->input('busy_now'),
                "can_guarantee" => $request->input('can_guarantee'),
                "relative_familiarity" => $request->input('relative_familiarity'),
                "guarantee" => $request->input('guarantee'),
                "relative_family_type" => $request->input('relative_family_type'),
                "relative_family_name" => $request->input('relative_family_name'),
                "relative_family_part" => $request->input('relative_family_part'),
                "accept_rules" => $request->input('accept_rules'),
                "unemployment_insurance" => $request->input('unemployment_insurance'),
                "accept_unemployment_insurance" => $request->input('accept_unemployment_insurance'),

            ]
        ]);
    }

    /**
     * @param \App\Models\Employee $employee
     * @return \Illuminate\Contracts\View\View
     */
    public function showContract(Employee $employee)
    {
        if (auth()->user()->employee->id == $employee->id) {
            $confirmable = false;
            if($employee->contract_status != 1 && $employee->contract_status == 2)
            {
                $confirmable = true;
            }
            $item['contract_histories'] = $employee->histories->where('reason_id' , 3);
            return view('Admin.employee.contract.index' , compact('item','employee' , 'confirmable'));
        }

        return abort(401);
    }
    /**
     * Summary of updateContract
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Employee $employee
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateContract(Request $request , Employee $employee)
    {
        if (auth()->user()->isAbleTo('employee_contract_edit')) {

            $employee->update(['contract' => $request->contract]);
            $user_phone_number = $employee->user->phone;
        if($request->contract_status == 2 && $employee->contract_status != 1)
        {
            $employee->update(['contract_status' => 2]);
            // NOT NULL timestamp: an employee with no contract date holds '0000-00-00 00:00:00',
            // which verta() cannot format — sending the contract link must not die on that.
            $contract_start_date = Helper::jalaliDate($employee->contract_start_date) ?? '-';
            $link = 'https://lalecard.ir/dashboard/employee/contract/' . $employee->id;
            SendSms::sendSingleSms('sendUserContractConfirmLink', $user_phone_number, ['param1' => $employee->contract_number, 'param2' => $contract_start_date, 'param3' => $link]);
            $description = 'قرارداد جهت تایید همکار ارسال شد.';
            History::contractHistoryCreate($employee , null, Auth::id() , 3 , 1 , $description);
        }
        elseif($request->contract_status == 3 && $employee->contract_status != 1){
            $employee->update(['contract_status' => 3]);
            $description = 'قرارداد جهت مطالعه همکار در حالت پیش نویس قرارداده شد.';
            History::contractHistoryCreate($employee , null, Auth::id() , 3 , 1 , $description);
        }

        return redirect()->back()->with([
            'update' => 'success'
        ]);
        }
        return redirect()->back()->with([
            'failByMessage' => 'شما مجاز به انجام این کار نیستید'
        ]);
    }
     /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function sendConfirmContractCode(Request $request)
    {
        if($request->confirm_contract_check == true)
        {
            $user_phone_number = auth()->user()->phone;
            $verification_code = rand( 123456, 999999 );
            $auth_code = AuthCode::where('phone' , auth()->user()->phone)->first();
            if(!is_null($auth_code))
            {
                $expirationTime = time() - $auth_code->expiration_time;
                $remaining_time = ($auth_code->time_code_again * 60) - $expirationTime;
                if ($remaining_time > 0) {
                    $date['remaining_time_second'] = $remaining_time;
                    $message = '  کاربر گرامی لطفا بعد از  ' . $remaining_time . '  ثانیه تلاش کنید.';
                    return response( [ 'status' => 'error', 'message' => $message ] );
                }
                $auth_code->update([
                    'code' => $verification_code,
                    'expiration_time' => time(),
                ]);
            }
            else
            {
                AuthCode::create([
                    'phone' => $user_phone_number,
                    'code' => $verification_code,
                    'time_code_again' => 2,
                    'expiration_time' => time(),
                ]);
            }
            SendSms::sendSingleSms('sendConfirmContractCode',$user_phone_number,['param1'=>$verification_code ]);
            return response( [ 'status' => 'success', 'message' => 'کد با موفقیت ارسال گردید.' ] );
        }
        return response( [ 'status' => 'error', 'message' => 'لطفا بر روی قسمت تایید میکنم کلیک کنید. ' ] );
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function verificationConfirmContractCode( Request $request )
    {
        $has_auth_code = AuthCode::where('phone' , auth()->user()->phone)->where('code' , $request->verification_code)->where('expiration_time' , '>' , time())->exists();
        if($has_auth_code)
        {
            auth()->user()->employee()->update(['contract_status' => 1,'status' => 1]);
            return response( [ 'status' => 'success', 'message' => 'قرارداد با موفقیت امضا شد.' ] );
        }
        return response( [ 'status' => 'error', 'message' => 'کد وارد شده با کد ارسالی مطابقت ندارد.' ] );
    }
}
