<?php

namespace App\Services\SmsSettings;

use App\Models\SmsMessageGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SmsMessageGroupService
{
    /**
     * Get hierarchical list of message groups with subgroups and template counts.
     */
    public function tree(?int $businessId = null): Collection
    {
        return SmsMessageGroup::query()
            ->whereNull('parent_id')
            ->when($businessId, fn($q) => $q->where('ghorfe_online_list_id', $businessId))
            ->with(['children' => function ($q) use ($businessId) {
                $q->when($businessId, fn($sq) => $sq->where('ghorfe_online_list_id', $businessId))
                  ->withCount('templates');
            }])
            ->withCount('templates')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get flat list of all groups (e.g. for dropdowns).
     */
    public function options(?int $businessId = null): array
    {
        $roots = SmsMessageGroup::whereNull('parent_id')
            ->when($businessId, fn($q) => $q->where('ghorfe_online_list_id', $businessId))
            ->with(['children' => function ($q) use ($businessId) {
                $q->when($businessId, fn($sq) => $sq->where('ghorfe_online_list_id', $businessId));
            }])
            ->orderBy('name')
            ->get();

        $options = [];
        foreach ($roots as $root) {
            $options[$root->id] = $root->name;
            foreach ($root->children as $child) {
                $options[$child->id] = "— {$child->name} ({$root->name})";
            }
        }

        return $options;
    }

    /**
     * Create a group or subgroup.
     */
    public function create(array $data, ?int $businessId = null): SmsMessageGroup
    {
        $slug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);

        // Ensure unique slug within same parent level and business
        $originalSlug = $slug;
        $counter = 1;
        while (SmsMessageGroup::where('slug', $slug)
            ->where('parent_id', $data['parent_id'] ?? null)
            ->where('ghorfe_online_list_id', $businessId)
            ->exists()) {
            $slug = "{$originalSlug}-" . $counter++;
        }

        return SmsMessageGroup::create([
            'name' => $data['name'],
            'slug' => $slug,
            'parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
            'ghorfe_online_list_id' => $businessId,
        ]);
    }

    /**
     * Ensure default base groups exist for a specific business.
     */
    public function ensureDefaultGroupsForBusiness(int $businessId): void
    {
        if (SmsMessageGroup::where('ghorfe_online_list_id', $businessId)->exists()) {
            return;
        }

        $defaultGroups = [
            [
                'name' => 'مالی',
                'slug' => 'mali',
                'subgroups' => [
                    ['name' => 'واریز پیش‌پرداخت', 'slug' => 'variz-pish-pardakht'],
                    ['name' => 'پرداخت فاکتور', 'slug' => 'pardakht-factor'],
                ],
            ],
            [
                'name' => 'حقوقی',
                'slug' => 'hoghoghi',
                'subgroups' => [
                    ['name' => 'اخطار', 'slug' => 'ekhtar'],
                    ['name' => 'ارجاع به حقوقی', 'slug' => 'erja-be-hoghoghi'],
                ],
            ],
            [
                'name' => 'پشتیبانی',
                'slug' => 'poshtibani',
                'subgroups' => [
                    ['name' => 'عدم پاسخگویی به تماس', 'slug' => 'adam-pasokhgooei-be-tamas'],
                ],
            ],
            [
                'name' => 'اجرایی',
                'slug' => 'ejraei',
                'subgroups' => [
                    ['name' => 'نوبت‌دهی', 'slug' => 'nobat-dehi'],
                    ['name' => 'تأیید جلسه', 'slug' => 'taeed-jalase'],
                ],
            ],
            [
                'name' => 'فروش',
                'slug' => 'foroush',
                'subgroups' => [
                    ['name' => 'پیگیری مشتری', 'slug' => 'peygiri-moshtari'],
                ],
            ],
        ];

        foreach ($defaultGroups as $groupData) {
            $parent = SmsMessageGroup::create([
                'ghorfe_online_list_id' => $businessId,
                'name' => $groupData['name'],
                'slug' => $groupData['slug'],
                'parent_id' => null,
            ]);

            foreach ($groupData['subgroups'] as $sub) {
                SmsMessageGroup::create([
                    'ghorfe_online_list_id' => $businessId,
                    'parent_id' => $parent->id,
                    'name' => $sub['name'],
                    'slug' => $sub['slug'],
                ]);
            }
        }
    }

    /**
     * Update an existing group.
     */
    public function update(SmsMessageGroup $group, array $data): SmsMessageGroup
    {
        $slug = !empty($data['slug']) ? Str::slug($data['slug']) : $group->slug;
        $parentId = array_key_exists('parent_id', $data)
            ? (!empty($data['parent_id']) ? (int) $data['parent_id'] : null)
            : $group->parent_id;

        // Prevent setting self as parent
        if ($parentId === $group->id) {
            throw ValidationException::withMessages([
                'parent_id' => ['یک گروه نمی‌تواند والد خودش باشد.'],
            ]);
        }

        $group->update([
            'name' => $data['name'] ?? $group->name,
            'slug' => $slug,
            'parent_id' => $parentId,
        ]);

        return $group;
    }

    /**
     * Delete a group or subgroup.
     * Guard against deleting groups that still contain active templates, or
     * that have historical SmsMessage rows referencing them.
     *
     * The sms_message_group_id FK on sms_messages is nullOnDelete(), and
     * SmsMessage::isConfidential() resolves purely through $this->group —
     * so deleting a مالی/حقوقی (confidential) group with historical messages
     * would null out their group_id and make those messages silently appear
     * non-confidential to everyone, retroactively. Block the delete instead,
     * the same way a group with templates is blocked.
     *
     * @throws ValidationException
     */
    public function delete(SmsMessageGroup $group): bool
    {
        if ($group->templates()->exists()) {
            throw ValidationException::withMessages([
                'group' => ["گروه «{$group->name}» دارای قالب‌های پیامک فعال است و امکان حذف آن وجود ندارد."],
            ]);
        }

        if ($group->messages()->exists()) {
            throw ValidationException::withMessages([
                'group' => ["گروه «{$group->name}» دارای پیامک‌های ثبت‌شده است و امکان حذف آن وجود ندارد."],
            ]);
        }

        // Check if any child subgroups have templates or messages
        foreach ($group->children as $child) {
            if ($child->templates()->exists()) {
                throw ValidationException::withMessages([
                    'group' => ["زیرگروه «{$child->name}» دارای قالب‌های پیامک است و امکان حذف گروه اصلی وجود ندارد."],
                ]);
            }

            if ($child->messages()->exists()) {
                throw ValidationException::withMessages([
                    'group' => ["زیرگروه «{$child->name}» دارای پیامک‌های ثبت‌شده است و امکان حذف گروه اصلی وجود ندارد."],
                ]);
            }
        }

        return (bool) $group->delete();
    }
}
