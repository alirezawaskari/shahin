<?php

namespace App\Services\SmsSettings;

use App\Models\GhorfeOnlineList;
use App\Models\Setting;
use App\Models\SmsPanel;
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
        $query = SmsPanel::query()->latest();

        if ($business) {
            $query->where(function ($q) use ($business) {
                $q->where('ghorfe_online_list_id', $business->id)
                    ->orWhereNull('ghorfe_online_list_id');
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
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create a new SMS panel.
     */
    public function create(array $data, ?GhorfeOnlineList $business = null): SmsPanel
    {
        $payload = [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'type' => $data['type'] ?? 'normal-advertising',
            'status' => $data['status'] ?? 'active',
            'connection_info' => $data['connection_info'] ?? [],
            'send_settings' => $data['send_settings'] ?? [],
            'receive_settings' => $data['receive_settings'] ?? [],
            'delivery_settings' => $data['delivery_settings'] ?? [],
            'description' => $data['description'] ?? null,
            'ghorfe_online_list_id' => $data['ghorfe_online_list_id'] ?? $business?->id,
        ];

        $panel = SmsPanel::create($payload);
        $this->syncToSetting($panel);

        return $panel;
    }

    /**
     * Update an existing SMS panel.
     */
    public function update(SmsPanel $panel, array $data): SmsPanel
    {
        $payload = [
            'name' => $data['name'] ?? $panel->name,
            'slug' => $data['slug'] ?? $panel->slug ?? Str::slug($data['name'] ?? $panel->name),
            'type' => $data['type'] ?? $panel->type,
            'status' => $data['status'] ?? $panel->status,
            'description' => $data['description'] ?? $panel->description,
        ];

        if (isset($data['connection_info'])) {
            $payload['connection_info'] = $data['connection_info'];
        }

        if (isset($data['send_settings'])) {
            $payload['send_settings'] = $data['send_settings'];
        }

        if (isset($data['receive_settings'])) {
            $payload['receive_settings'] = $data['receive_settings'];
        }

        if (isset($data['delivery_settings'])) {
            $payload['delivery_settings'] = $data['delivery_settings'];
        }

        if (array_key_exists('ghorfe_online_list_id', $data)) {
            $payload['ghorfe_online_list_id'] = $data['ghorfe_online_list_id'];
        }

        $panel->update($payload);
        $this->syncToSetting($panel);

        return $panel;
    }

    /**
     * Delete an SMS panel.
     */
    public function delete(SmsPanel $panel): bool
    {
        return (bool) $panel->delete();
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(SmsPanel $panel): SmsPanel
    {
        $panel->status = ($panel->status === 'active') ? 'inactive' : 'active';
        $panel->save();
        $this->syncToSetting($panel);

        return $panel;
    }

    /**
     * Sync panel config to Setting polymorphic table for compatibility with legacy readers.
     */
    public function syncToSetting(SmsPanel $panel): void
    {
        $keyPrefix = 'sms_panel_' . Str::snake($panel->name);

        $keys = [
            "{$keyPrefix}_status" => $panel->status,
            "{$keyPrefix}_type" => $panel->type,
            "{$keyPrefix}_connection" => json_encode($panel->connection_info),
            "{$keyPrefix}_delivery" => json_encode($panel->delivery_settings),
        ];

        $ownerType = $panel->ghorfe_online_list_id ? GhorfeOnlineList::class : null;
        $ownerId = $panel->ghorfe_online_list_id;

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
