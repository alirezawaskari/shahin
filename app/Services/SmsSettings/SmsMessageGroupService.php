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
    public function tree(): Collection
    {
        return SmsMessageGroup::query()
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->withCount('templates');
            }])
            ->withCount('templates')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get flat list of all groups (e.g. for dropdowns).
     */
    public function options(): array
    {
        $roots = SmsMessageGroup::whereNull('parent_id')
            ->with('children')
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
    public function create(array $data): SmsMessageGroup
    {
        $slug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);

        // Ensure unique slug within same parent level
        $originalSlug = $slug;
        $counter = 1;
        while (SmsMessageGroup::where('slug', $slug)->where('parent_id', $data['parent_id'] ?? null)->exists()) {
            $slug = "{$originalSlug}-" . $counter++;
        }

        return SmsMessageGroup::create([
            'name' => $data['name'],
            'slug' => $slug,
            'parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
        ]);
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
