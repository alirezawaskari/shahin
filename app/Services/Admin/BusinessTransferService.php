<?php

namespace App\Services\Admin;

use App\Models\Callcenter;
use App\Models\Employee;
use App\Models\Factor;
use App\Models\FactorItem;
use App\Models\GhorfeOnlineList;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BusinessTransferService
{
    /**
     * Move selected records atomically. Pivot ownership is replaced with sync(), never
     * appended, so the original record remains single and no stale owner is retained.
     *
     * @param  array<int, int|string>  $ids
     * @return array{count: int, destination: GhorfeOnlineList}
     */
    public function transfer(
        string $entity,
        array $ids,
        int $destinationBusinessId,
        ?int $actorBusinessId = null
    ): array {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        return DB::transaction(function () use ($entity, $ids, $destinationBusinessId, $actorBusinessId) {
            // Status can change after request validation. Re-check and lock the active
            // destination inside the same transaction as every source record.
            $destination = GhorfeOnlineList::active()
                ->whereKey($destinationBusinessId)
                ->lockForUpdate()
                ->first();

            if (! $destination) {
                throw ValidationException::withMessages([
                    'destination_business_id' => 'کسب و کار مقصد معتبر یا فعال نیست.',
                ]);
            }

            $result = match ($entity) {
                'factor' => $this->transferManyToMany(Factor::class, $ids, $destination, $actorBusinessId),
                'user' => $this->transferManyToMany(User::class, $ids, $destination, $actorBusinessId),
                'callcenter' => $this->transferManyToMany(Callcenter::class, $ids, $destination, $actorBusinessId),
                'employee' => $this->transferEmployees($ids, $destination, $actorBusinessId),
                'factor_item' => $this->transferFactorItems($ids, $destination, $actorBusinessId),
                default => throw ValidationException::withMessages([
                    'entity' => 'نوع رکورد انتخاب شده برای انتقال معتبر نیست.',
                ]),
            };

            return $result + ['destination' => $destination];
        }, 3);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, int>  $ids
     * @return array{count: int}
     */
    private function transferManyToMany(
        string $modelClass,
        array $ids,
        GhorfeOnlineList $destination,
        ?int $actorBusinessId
    ): array {
        $query = $modelClass::query()
            ->with('ghorfeOnlineLists:id')
            ->whereKey($ids);

        if ($actorBusinessId !== null) {
            $query
                ->whereHas('ghorfeOnlineLists', fn ($businessQuery) => $businessQuery
                    ->whereKey($actorBusinessId))
                ->has('ghorfeOnlineLists', '=', 1);
        }

        /** @var EloquentCollection<int, Model> $records */
        $records = $query->lockForUpdate()->get();

        $this->assertEveryRecordExists($records, $ids, $actorBusinessId !== null);

        // Validate the entire set before the first write. One bad row therefore rolls back
        // the whole selection instead of producing a partially moved batch.
        foreach ($records as $record) {
            $this->assertTransferAllowed(
                $record->ghorfeOnlineLists->modelKeys(),
                $destination->getKey(),
                $actorBusinessId
            );
        }

        foreach ($records as $record) {
            $record->ghorfeOnlineLists()->sync([$destination->getKey()]);
        }

        return ['count' => $records->count()];
    }

    /** @param array<int, int> $ids @return array{count: int} */
    private function transferEmployees(
        array $ids,
        GhorfeOnlineList $destination,
        ?int $actorBusinessId
    ): array {
        $query = Employee::query()->whereKey($ids);

        if ($actorBusinessId !== null) {
            $query->where('ghorfe_online_list_id', $actorBusinessId);
        }

        $employees = $query->lockForUpdate()->get();
        $this->assertEveryRecordExists($employees, $ids, $actorBusinessId !== null);

        foreach ($employees as $employee) {
            $currentIds = $employee->ghorfe_online_list_id
                ? [(int) $employee->ghorfe_online_list_id]
                : [];
            $this->assertTransferAllowed($currentIds, $destination->getKey(), $actorBusinessId);
        }

        Employee::query()->whereKey($employees->modelKeys())->update([
            'ghorfe_online_list_id' => $destination->getKey(),
            'updated_at' => now(),
        ]);

        return ['count' => $employees->count()];
    }

    /**
     * A پرونده inherits business ownership from its Factor. Lock every selected پرونده,
     * then replace each distinct parent Factor association once.
     *
     * @param  array<int, int>  $ids
     * @return array{count: int}
     */
    private function transferFactorItems(
        array $ids,
        GhorfeOnlineList $destination,
        ?int $actorBusinessId
    ): array {
        $query = FactorItem::query()->whereKey($ids);

        if ($actorBusinessId !== null) {
            $query->whereHas('factor', fn ($factorQuery) => $factorQuery
                ->whereHas('ghorfeOnlineLists', fn ($businessQuery) => $businessQuery
                    ->whereKey($actorBusinessId))
                ->has('ghorfeOnlineLists', '=', 1));
        }

        $items = $query->lockForUpdate()->get();
        $this->assertEveryRecordExists($items, $ids, $actorBusinessId !== null);

        $factorIds = $items->pluck('factor_id')->filter()->unique()->values()->all();
        if (count($factorIds) === 0 || $items->contains(fn (FactorItem $item) => ! $item->factor_id)) {
            throw ValidationException::withMessages([
                'ids' => 'حداقل یکی از پرونده‌های انتخاب شده فاکتور معتبر ندارد.',
            ]);
        }

        // Business ownership lives on Factor, not FactorItem. Refuse a partial selection
        // instead of silently moving unselected sibling cases that share the same factor.
        $allSiblingIds = FactorItem::query()
            ->whereIn('factor_id', $factorIds)
            ->lockForUpdate()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();
        $selectedIds = collect($ids)->sort()->values()->all();

        if ($allSiblingIds !== $selectedIds) {
            throw ValidationException::withMessages([
                'ids' => 'برای انتقال پرونده، همه پرونده‌های وابسته به همان فاکتور را انتخاب کنید.',
            ]);
        }

        $factors = Factor::query()
            ->with('ghorfeOnlineLists:id')
            ->whereKey($factorIds)
            ->lockForUpdate()
            ->get();

        if ($factors->count() !== count($factorIds)) {
            throw ValidationException::withMessages([
                'ids' => 'حداقل یکی از پرونده‌های انتخاب شده دیگر معتبر نیست.',
            ]);
        }

        foreach ($factors as $factor) {
            $this->assertTransferAllowed(
                $factor->ghorfeOnlineLists->modelKeys(),
                $destination->getKey(),
                $actorBusinessId
            );
        }

        foreach ($factors as $factor) {
            $factor->ghorfeOnlineLists()->sync([$destination->getKey()]);
        }

        return ['count' => $items->count()];
    }

    /**
     * @param  EloquentCollection<int, Model>  $records
     * @param  array<int, int>  $ids
     */
    private function assertEveryRecordExists(
        EloquentCollection $records,
        array $ids,
        bool $hideExistence = false
    ): void
    {
        if ($records->count() !== count($ids)) {
            if ($hideExistence) {
                throw new AuthorizationException(
                    'امکان انتقال رکورد متعلق یا مشترک با کسب و کار دیگر وجود ندارد.'
                );
            }

            throw ValidationException::withMessages([
                'ids' => 'حداقل یکی از رکوردهای انتخاب شده معتبر نیست یا حذف شده است.',
            ]);
        }
    }

    /** @param array<int, int|string> $currentIds */
    private function assertTransferAllowed(
        array $currentIds,
        int $destinationBusinessId,
        ?int $actorBusinessId
    ): void {
        $currentIds = array_values(array_map('intval', $currentIds));

        if ($actorBusinessId !== null
            && (count($currentIds) !== 1 || $currentIds[0] !== $actorBusinessId)) {
            throw new AuthorizationException(
                'امکان انتقال رکورد متعلق یا مشترک با کسب و کار دیگر وجود ندارد.'
            );
        }

        if (in_array($destinationBusinessId, $currentIds, true)) {
            throw ValidationException::withMessages([
                'destination_business_id' => 'کسب و کار فعلی رکورد نمی‌تواند مقصد انتقال باشد.',
            ]);
        }
    }
}
