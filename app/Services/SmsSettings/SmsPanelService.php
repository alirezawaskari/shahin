<?php

namespace App\Services\SmsSettings;

use App\Models\GhorfeOnlineList;
use App\Models\Setting;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SmsPanelService
{
    /**
     * List panels with optional filters.
     */
    public function list(array $filters = [], ?GhorfeOnlineList $business = null): LengthAwarePaginator
    {
        $query = Setting::where('key', 'sms_panel')->latest();

        if ($business) {
            $query->where(function ($q) use ($business) {
                $q->where('settingable_id', $business->id)
                    ->orWhereNull('settingable_id');
            });
        }

        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('value', 'like', "%{$search}%");
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create a new SMS panel.
     */
    public function create(array $data, ?GhorfeOnlineList $business = null): Setting
    {
        $name = $data['name'];
        $slug = $data['slug'] ?? Str::slug($name);
        $type = $data['type'] ?? 'normal-advertising';
        $status = $data['status'] ?? 'active';
        $description = $data['description'] ?? null;
        $connectionInfo = $data['connection_info'] ?? [];
        $sendSettings = $data['send_settings'] ?? [];
        $receiveSettings = $data['receive_settings'] ?? [];
        $deliverySettings = $data['delivery_settings'] ?? [];
        $businessId = $data['ghorfe_online_list_id'] ?? $business?->id;

        $payload = [
            'name' => $name,
            'slug' => $slug,
            'connection_info' => $connectionInfo,
            'send_settings' => $sendSettings,
            'receive_settings' => $receiveSettings,
            'delivery_settings' => $deliverySettings,
            'description' => $description,
        ];

        $panel = Setting::create([
            'key' => 'sms_panel',
            'type' => $type,
            'status' => $status,
            'settingable_type' => $businessId ? GhorfeOnlineList::class : null,
            'settingable_id' => $businessId,
            'value' => json_encode($payload),
        ]);

        $this->syncToSetting($panel);

        return $panel;
    }

    /**
     * Update an existing SMS panel.
     */
    public function update(Setting $panel, array $data): Setting
    {
        $currentVal = json_decode($panel->getRawOriginal('value'), true) ?: [];

        $name = $data['name'] ?? $panel->name;
        $slug = $data['slug'] ?? $panel->slug ?? Str::slug($name);
        $type = $data['type'] ?? $panel->type;
        $status = $data['status'] ?? $panel->status;
        $description = $data['description'] ?? $panel->description;

        $currentVal['name'] = $name;
        $currentVal['slug'] = $slug;
        $currentVal['description'] = $description;

        if (isset($data['connection_info'])) {
            $currentVal['connection_info'] = $data['connection_info'];
        }
        if (isset($data['send_settings'])) {
            $currentVal['send_settings'] = $data['send_settings'];
        }
        if (isset($data['receive_settings'])) {
            $currentVal['receive_settings'] = $data['receive_settings'];
        }
        if (isset($data['delivery_settings'])) {
            $currentVal['delivery_settings'] = $data['delivery_settings'];
        }

        $businessId = array_key_exists('ghorfe_online_list_id', $data) ? $data['ghorfe_online_list_id'] : $panel->settingable_id;

        $panel->update([
            'type' => $type,
            'status' => $status,
            'settingable_type' => $businessId ? GhorfeOnlineList::class : null,
            'settingable_id' => $businessId,
            'value' => json_encode($currentVal),
        ]);

        $this->syncToSetting($panel);

        return $panel;
    }

    /**
     * Delete an SMS panel.
     */
    public function delete(Setting $panel): bool
    {
        return (bool) $panel->delete();
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Setting $panel): Setting
    {
        $panel->status = ($panel->status === 'active') ? 'inactive' : 'active';
        $panel->save();
        $this->syncToSetting($panel);

        return $panel;
    }

    /**
     * Sync panel config to Setting polymorphic table for compatibility with legacy readers.
     */
    public function syncToSetting(Setting $panel): void
    {
        $keyPrefix = 'sms_panel_' . Str::snake($panel->name);

        $keys = [
            "{$keyPrefix}_status" => $panel->status,
            "{$keyPrefix}_type" => $panel->type,
            "{$keyPrefix}_connection" => json_encode($panel->connection_info),
            "{$keyPrefix}_delivery" => json_encode($panel->delivery_settings),
        ];

        $ownerType = $panel->settingable_type;
        $ownerId = $panel->settingable_id;

        foreach ($keys as $key => $val) {
            Setting::updateOrCreate(
                [
                    'key' => $key,
                    'settingable_type' => $ownerType,
                    'settingable_id' => $ownerId,
                ],
                [
                    'value' => $val,
                ]
            );
        }
    }
}
