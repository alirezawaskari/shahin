<?php

namespace App\Services\SmsSettings;

use App\Models\GhorfeOnlineList;
use App\Models\Setting;
use DomainException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SmsSenderNumberService
{
    /**
     * List sender numbers.
     * Enforces: "A business only sees its own assigned numbers".
     */
    public function list(?GhorfeOnlineList $business = null, array $filters = []): LengthAwarePaginator
    {
        if ($business) {
            $query = $business->senderNumbers();
        } else {
            $query = Setting::where('key', 'sms_sender_number');
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
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('sms_panel', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%");
            });
        }

        if (empty($business) && !empty($filters['ghorfe_id']) && $filters['ghorfe_id'] !== 'all') {
            $query->where(function ($q) use ($filters) {
                $q->where('settingable_id', $filters['ghorfe_id'])
                  ->orWhere('value', 'like', '%"business_ids":%' . $filters['ghorfe_id'] . '%');
            });
        }

        return $query->latest('id')->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Determine whether the given business has access to this sender number.
     */
    public function canAccess(Setting $senderNumber, ?GhorfeOnlineList $business = null): bool
    {
        if (!$business) {
            return true; // SuperAdmin has access to all
        }

        if ($senderNumber->settingable_id == $business->id) {
            return true;
        }

        return $senderNumber->businesses->contains('id', $business->id);
    }

    /**
     * Create a new sender number and assign businesses.
     *
     * @throws ValidationException
     */
    public function create(array $data, array $businessIds = []): Setting
    {
        $primaryId = !empty($businessIds) ? reset($businessIds) : null;

        try {
            $senderNumber = Setting::create([
                'key' => 'sms_sender_number',
                'settingable_type' => $primaryId ? GhorfeOnlineList::class : null,
                'settingable_id' => $primaryId,
                'number' => $data['number'],
                'type' => $data['type'],
                'line_type' => $data['line_type'] ?? (($data['type'] === 'otp') ? 'service' : 'normal'),
                'sms_panel' => $data['sms_panel'],
                'status' => $data['status'] ?? 'active',
                'contract_status' => $data['contract_status'] ?? 'تایید شده',
                'description' => $data['description'] ?? null,
            ]);
        } catch (DomainException $e) {
            throw ValidationException::withMessages([
                'type' => [$e->getMessage()],
            ]);
        }

        if (!empty($businessIds)) {
            $this->assignBusinesses($senderNumber, $businessIds);
        }

        return $senderNumber;
    }

    /**
     * Update an existing sender number and sync businesses.
     *
     * @throws ValidationException
     */
    public function update(Setting $senderNumber, array $data, ?array $businessIds = null): Setting
    {
        try {
            $updateData = [
                'number' => $data['number'] ?? $senderNumber->number,
                'type' => $data['type'] ?? $senderNumber->type,
                'sms_panel' => $data['sms_panel'] ?? $senderNumber->sms_panel,
                'status' => $data['status'] ?? $senderNumber->status,
                'contract_status' => $data['contract_status'] ?? $senderNumber->contract_status,
                'description' => $data['description'] ?? $senderNumber->description,
            ];

            if (array_key_exists('line_type', $data)) {
                $updateData['line_type'] = $data['line_type'];
            }

            $senderNumber->update($updateData);
        } catch (DomainException $e) {
            throw ValidationException::withMessages([
                'type' => [$e->getMessage()],
            ]);
        }

        if ($businessIds !== null) {
            $this->assignBusinesses($senderNumber, $businessIds);
        }

        return $senderNumber;
    }

    /**
     * Delete a sender number.
     */
    public function delete(Setting $senderNumber): bool
    {
        return (bool) $senderNumber->delete();
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Setting $senderNumber): Setting
    {
        $senderNumber->status = ($senderNumber->status === 'active') ? 'inactive' : 'active';
        $senderNumber->save();

        return $senderNumber;
    }

    /**
     * Assign businesses to the sender number.
     */
    public function assignBusinesses(Setting $senderNumber, array $businessIds): void
    {
        $businessIds = array_values(array_filter($businessIds));
        $primaryId = $businessIds[0] ?? null;

        $senderNumber->settingable_type = $primaryId ? GhorfeOnlineList::class : null;
        $senderNumber->settingable_id = $primaryId;

        $val = $senderNumber->attributes['value'] ?? null;
        $data = [];
        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $data = json_decode($val, true) ?: [];
        }
        $data['business_ids'] = $businessIds;
        $senderNumber->value = json_encode($data);
        $senderNumber->save();
    }
}
