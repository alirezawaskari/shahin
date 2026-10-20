<?php

namespace App\Services\SmsSettings;

use App\Models\GhorfeOnlineList;
use App\Models\Setting;
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
        $all = Setting::query()
            ->where('key', 'sms_message_group')
            ->when($businessId, fn($q) => $q->where('settingable_id', $businessId))
            ->get();

        $roots = $all->filter(fn ($s) => empty($s->parent_id))->values();

        return new Collection($roots);
    }

    /**
     * Get flat list of all groups (e.g. for dropdowns).
     */
    public function options(?int $businessId = null): array
    {
        $roots = $this->tree($businessId);

        $options = [];
        foreach ($roots as $root) {
            $rootLabel = (!$businessId && $root->business) ? "{$root->name} ({$root->business->title})" : $root->name;
            $options[$root->id] = $rootLabel;
            foreach ($root->children as $child) {
                $options[$child->id] = "— {$child->name} ({$rootLabel})";
            }
        }

        return $options;
    }

    /**
     * Get IDs of promotional groups.
     */
    public function promotionalIds(?int $businessId = null): array
    {
        return Setting::query()
            ->where('key', 'sms_message_group')
            ->when($businessId, fn($q) => $q->where('settingable_id', $businessId))
            ->get()
            ->filter(fn ($s) => $s->isPromotional())
            ->pluck('id')
            ->all();
    }

    /**
     * Create a group or subgroup.
     */
    public function create(array $data, ?int $businessId = null): Setting
    {
        $slug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);

        $originalSlug = $slug;
        $counter = 1;
        while (Setting::where('key', 'sms_message_group')
            ->where('settingable_id', $businessId)
            ->where('value', 'like', '%"slug":"' . $slug . '"%')
            ->exists()) {
            $slug = "{$originalSlug}-" . $counter++;
        }

        return Setting::create([
            'key' => 'sms_message_group',
            'settingable_type' => GhorfeOnlineList::class,
            'settingable_id' => $businessId,
            'type' => 'group',
            'status' => 'active',
            'value' => json_encode([
                'name' => $data['name'],
                'slug' => $slug,
                'parent_id' => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
            ]),
        ]);
    }

    /**
     * Ensure default base groups exist for a specific business.
     */
    public function ensureDefaultGroupsForBusiness(int $businessId): void
    {
        if (Setting::where('key', 'sms_message_group')->where('settingable_id', $businessId)->exists()) {
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
            $parent = Setting::create([
                'key' => 'sms_message_group',
                'settingable_type' => GhorfeOnlineList::class,
                'settingable_id' => $businessId,
                'type' => 'group',
                'status' => 'active',
                'value' => json_encode([
                    'name' => $groupData['name'],
                    'slug' => $groupData['slug'],
                    'parent_id' => null,
                ]),
            ]);

            foreach ($groupData['subgroups'] as $sub) {
                Setting::create([
                    'key' => 'sms_message_group',
                    'settingable_type' => GhorfeOnlineList::class,
                    'settingable_id' => $businessId,
                    'type' => 'group',
                    'status' => 'active',
                    'value' => json_encode([
                        'name' => $sub['name'],
                        'slug' => $sub['slug'],
                        'parent_id' => $parent->id,
                    ]),
                ]);
            }
        }
    }

    /**
     * Update an existing group.
     */
    public function update(Setting $group, array $data): Setting
    {
        $slug = !empty($data['slug']) ? Str::slug($data['slug']) : $group->slug;
        $parentId = array_key_exists('parent_id', $data)
            ? (!empty($data['parent_id']) ? (int) $data['parent_id'] : null)
            : $group->parent_id;

        if ($parentId === $group->id) {
            throw ValidationException::withMessages([
                'parent_id' => ['یک گروه نمی‌تواند والد خودش باشد.'],
            ]);
        }

        $group->value = json_encode([
            'name' => $data['name'] ?? $group->name,
            'slug' => $slug,
            'parent_id' => $parentId,
        ]);
        $group->save();

        return $group;
    }

    /**
     * Delete a group or subgroup.
     */
    public function delete(Setting $group): bool
    {
        if ($group->templates_count > 0) {
            throw ValidationException::withMessages([
                'group' => ["گروه «{$group->name}» دارای قالب‌های پیامک فعال است و امکان حذف آن وجود ندارد."],
            ]);
        }

        foreach ($group->children as $child) {
            if ($child->templates_count > 0) {
                throw ValidationException::withMessages([
                    'group' => ["زیرگروه «{$child->name}» دارای قالب‌های پیامک است و امکان حذف گروه اصلی وجود ندارد."],
                ]);
            }
        }

        return (bool) $group->delete();
    }
}
