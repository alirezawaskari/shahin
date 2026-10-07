<?php

namespace App\Services\SmsSettings;

use App\Models\BusinessSenderNumber;
use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
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
            $query = $business->senderNumbers()->withPivot('assigned_at');
        } else {
            $query = SenderNumber::with('businesses');
        }

        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('sender_numbers.type', $filters['type']);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('sender_numbers.status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('sender_numbers.number', 'like', "%{$search}%")
                    ->orWhere('sender_numbers.sms_panel', 'like', "%{$search}%")
                    ->orWhere('sender_numbers.description', 'like', "%{$search}%");
            });
        }

        if (empty($business) && !empty($filters['ghorfe_id']) && $filters['ghorfe_id'] !== 'all') {
            $query->whereHas('businesses', function ($q) use ($filters) {
                $q->where('ghorfe_online_lists.id', $filters['ghorfe_id']);
            });
        }

        return $query->latest('sender_numbers.id')->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Determine whether the given business has access to this sender number.
     */
    public function canAccess(SenderNumber $senderNumber, ?GhorfeOnlineList $business = null): bool
    {
        if (!$business) {
            return true; // SuperAdmin has access to all
        }

        return $senderNumber->businesses()->where('ghorfe_online_lists.id', $business->id)->exists();
    }

    /**
     * Create a new sender number and assign businesses.
     *
     * @throws ValidationException
     */
    public function create(array $data, array $businessIds = []): SenderNumber
    {
        try {
            $senderNumber = SenderNumber::create([
                'number' => $data['number'],
                'type' => $data['type'],
                'sms_panel' => $data['sms_panel'],
                'status' => $data['status'] ?? 'active',
                'contract_status' => $data['contract_status'] ?? null,
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
    public function update(SenderNumber $senderNumber, array $data, ?array $businessIds = null): SenderNumber
    {
        try {
            $senderNumber->update([
                'number' => $data['number'] ?? $senderNumber->number,
                'type' => $data['type'] ?? $senderNumber->type,
                'sms_panel' => $data['sms_panel'] ?? $senderNumber->sms_panel,
                'status' => $data['status'] ?? $senderNumber->status,
                'contract_status' => $data['contract_status'] ?? $senderNumber->contract_status,
                'description' => $data['description'] ?? $senderNumber->description,
            ]);
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
    public function delete(SenderNumber $senderNumber): bool
    {
        return (bool) $senderNumber->delete();
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(SenderNumber $senderNumber): SenderNumber
    {
        $senderNumber->status = ($senderNumber->status === 'active') ? 'inactive' : 'active';
        $senderNumber->save();

        return $senderNumber;
    }

    /**
     * Assign businesses to the sender number.
     */
    public function assignBusinesses(SenderNumber $senderNumber, array $businessIds): void
    {
        $syncData = [];
        $now = now();
        foreach ($businessIds as $id) {
            if ($id) {
                $syncData[$id] = ['assigned_at' => $now];
            }
        }

        $senderNumber->businesses()->sync($syncData);
    }
}
