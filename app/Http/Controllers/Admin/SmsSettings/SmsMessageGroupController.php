<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Http\Controllers\Controller;
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
     * Display listing of message groups and subgroups.
     */
    public function index(): View
    {
        $business = ghorfe();
        $groups = $this->groupService->tree();
        $rootGroups = SmsMessageGroup::whereNull('parent_id')->orderBy('name')->get();

        return view('Admin.sms.settings.groups.index', compact('groups', 'rootGroups', 'business'));
    }

    /**
     * Show form for creating a new group/subgroup.
     */
    public function create(): View
    {
        $business = ghorfe();
        $rootGroups = SmsMessageGroup::whereNull('parent_id')->orderBy('name')->get();

        return view('Admin.sms.settings.groups.create', compact('rootGroups', 'business'));
    }

    /**
     * Store a newly created group/subgroup.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'nullable|string|max:191',
            'parent_id' => 'nullable|exists:sms_message_groups,id',
        ], [
            'name.required' => 'نام گروه پیامک الزامی است.',
        ]);

        $group = $this->groupService->create($validated);
        $typeLabel = $group->parent_id ? 'زیرگروه' : 'گروه';

        return redirect()->route('admin.sms.settings.groups.index')
            ->with('success', "{$typeLabel} «{$group->name}» با موفقیت افزوده شد.");
    }

    /**
     * Show form for editing a group/subgroup.
     */
    public function edit(SmsMessageGroup $group): View
    {
        $business = ghorfe();
        $rootGroups = SmsMessageGroup::whereNull('parent_id')
            ->where('id', '!=', $group->id)
            ->orderBy('name')
            ->get();

        return view('Admin.sms.settings.groups.edit', compact('group', 'rootGroups', 'business'));
    }

    /**
     * Update an existing group/subgroup.
     */
    public function update(Request $request, SmsMessageGroup $group): RedirectResponse
    {
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

        return redirect()->route('admin.sms.settings.groups.index')
            ->with('success', "گروه «{$group->name}» با موفقیت به‌روزرسانی شد.");
    }

    /**
     * Delete a group/subgroup.
     */
    public function destroy(SmsMessageGroup $group): RedirectResponse
    {
        $name = $group->name;
        try {
            $this->groupService->delete($group);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()->route('admin.sms.settings.groups.index')
            ->with('success', "گروه «{$name}» با موفقیت حذف شد.");
    }
}
