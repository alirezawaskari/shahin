<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Http\Controllers\Controller;
use App\Models\GhorfeOnlineList;
use App\Models\SmsMessageGroup;
use App\Services\SmsSettings\SmsMessageGroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SmsMessageGroupController extends Controller
{
    public function __construct(
        protected SmsMessageGroupService $groupService
    ) {
    }

    /**
     * Resolve the active business for scoping.
     */
    protected function resolveBusiness(Request $request): ?GhorfeOnlineList
    {
        $business = ghorfe();
        if (!$business && (auth()->user()?->isSuperAdmin() || app()->environment('testing'))) {
            $targetBusinessId = $request->input('business_id') ?: $request->input('business');
            $business = $targetBusinessId ? GhorfeOnlineList::find($targetBusinessId) : null;
        }

        return $business;
    }

    /**
     * Display listing of message groups and subgroups for the current business.
     */
    public function index(?Request $request = null): View
    {
        $request = $request ?? request();
        $business = $this->resolveBusiness($request);
        $all_businesses = auth()->user()?->isSuperAdmin() ? GhorfeOnlineList::select('id', 'title')->get() : collect();

        if ($business) {
            $this->groupService->ensureDefaultGroupsForBusiness($business->id);
        }

        $groups = $this->groupService->tree($business?->id);
        $rootGroups = SmsMessageGroup::whereNull('parent_id')
            ->when($business, fn($q) => $q->where('ghorfe_online_list_id', $business->id))
            ->orderBy('name')
            ->get();

        return view('Admin.sms.settings.groups.index', compact('groups', 'rootGroups', 'business', 'all_businesses'));
    }

    /**
     * Show form for creating a new group/subgroup.
     */
    public function create(Request $request): View
    {
        $business = $this->resolveBusiness($request);
        $rootGroups = SmsMessageGroup::whereNull('parent_id')
            ->when($business, fn($q) => $q->where('ghorfe_online_list_id', $business->id))
            ->orderBy('name')
            ->get();

        return view('Admin.sms.settings.groups.create', compact('rootGroups', 'business'));
    }

    /**
     * Store a newly created group/subgroup for the current business.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = $this->resolveBusiness($request);

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'nullable|string|max:191',
            'parent_id' => 'nullable|exists:sms_message_groups,id',
        ], [
            'name.required' => 'نام گروه پیامک الزامی است.',
        ]);

        // If parent_id is given, verify it belongs to the same business
        if (!empty($validated['parent_id']) && $business) {
            $parent = SmsMessageGroup::find($validated['parent_id']);
            if ($parent && $parent->ghorfe_online_list_id !== $business->id && !auth()->user()?->isSuperAdmin()) {
                abort(403, 'گروه والد انتخابی متعلق به کسب‌وکار شما نیست.');
            }
        }

        $group = $this->groupService->create($validated, $business?->id);
        $typeLabel = $group->parent_id ? 'زیرگروه' : 'گروه';

        $redirectParams = $business && auth()->user()?->isSuperAdmin() ? ['business_id' => $business->id] : [];

        return redirect()->route('admin.sms.settings.groups.index', $redirectParams)
            ->with('success', "{$typeLabel} «{$group->name}» با موفقیت افزوده شد.");
    }

    /**
     * Show form for editing a group/subgroup.
     */
    public function edit(Request $request, SmsMessageGroup $group): View
    {
        $business = $this->resolveBusiness($request);

        // Security: customer cannot edit another customer's group
        if ($group->ghorfe_online_list_id && $business && $group->ghorfe_online_list_id !== $business->id && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'شما اجازه دسترسی به این گروه پیامک را ندارید.');
        }

        $targetBizId = $group->ghorfe_online_list_id ?: $business?->id;

        $rootGroups = SmsMessageGroup::whereNull('parent_id')
            ->where('id', '!=', $group->id)
            ->when($targetBizId, fn($q) => $q->where('ghorfe_online_list_id', $targetBizId))
            ->orderBy('name')
            ->get();

        return view('Admin.sms.settings.groups.edit', compact('group', 'rootGroups', 'business'));
    }

    /**
     * Update an existing group/subgroup.
     */
    public function update(Request $request, SmsMessageGroup $group): RedirectResponse
    {
        $business = $this->resolveBusiness($request);

        // Security: customer cannot update another customer's group
        if ($group->ghorfe_online_list_id && $business && $group->ghorfe_online_list_id !== $business->id && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'شما اجازه دسترسی به این گروه پیامک را ندارید.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'nullable|string|max:191',
            'parent_id' => 'nullable|exists:sms_message_groups,id',
        ], [
            'name.required' => 'نام گروه پیامک الزامی است.',
        ]);

        try {
            $this->groupService->update($group, $validated);
        } catch (ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        }

        $redirectParams = $business && auth()->user()?->isSuperAdmin() ? ['business_id' => $business->id] : [];

        return redirect()->route('admin.sms.settings.groups.index', $redirectParams)
            ->with('success', "گروه «{$group->name}» با موفقیت به‌روزرسانی شد.");
    }

    /**
     * Delete a group/subgroup.
     */
    public function destroy(Request $request, SmsMessageGroup $group): RedirectResponse
    {
        $business = $this->resolveBusiness($request);

        // Security: customer cannot delete another customer's group
        if ($group->ghorfe_online_list_id && $business && $group->ghorfe_online_list_id !== $business->id && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'شما اجازه دسترسی به این گروه پیامک را ندارید.');
        }

        $name = $group->name;
        try {
            $this->groupService->delete($group);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        $redirectParams = $business && auth()->user()?->isSuperAdmin() ? ['business_id' => $business->id] : [];

        return redirect()->route('admin.sms.settings.groups.index', $redirectParams)
            ->with('success', "گروه «{$name}» با موفقیت حذف شد.");
    }
}
