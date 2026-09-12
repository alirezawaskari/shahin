<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractHistory;
use App\Models\ContractUsage;
use App\Models\Product;
use App\Models\Role;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Attaches a contract to a service / role / product for one usage location.
 *
 * Deliberately separate from ServiceController@update: that form posts a partial
 * field set and drops anything it does not know about, so the association must not
 * ride along with it.
 */
class ContractUsageController extends Controller
{
    /**
     * The owner types the UI may address, keyed by the short name used in the form.
     */
    private const OWNER_TYPES = [
        'service' => Service::class,
        'role' => Role::class,
        'product' => Product::class,
    ];

    /**
     * mode=none    -> detach (no contract here)
     * mode=existing-> attach a contract from the bank
     * mode=new     -> create a bank contract and attach it in one post
     */
    public function store(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:none,existing,new',
            'location' => 'required|string',
            'owner_type' => 'nullable|in:' . implode(',', array_keys(self::OWNER_TYPES)),
            'owner_id' => 'nullable|integer',
            'contract_id' => 'required_if:mode,existing|nullable|integer|exists:contracts,id',
            'title' => 'required_if:mode,new|nullable|string|max:191',
            'type' => 'required_if:mode,new|nullable|integer',
            'contract_folder_id' => 'nullable|integer|exists:contract_folders,id',
            'contract_number' => 'nullable|string|max:100',
            'contract_date' => 'nullable|date',
            'content' => $this->contractBodyRule(),
            // «استفاده از قرارداد موجود» edit-in-place fields.
            'edit_contract_id' => 'nullable|integer',
            'edit_title' => 'required_with:edit_contract_id|nullable|string|max:191',
            'edit_type' => 'nullable|integer',
            'edit_content' => $this->contractBodyRule(),
        ], [
            'mode.required' => 'لطفا وضعیت قرارداد را مشخص کنید',
            'contract_id.required_if' => 'لطفا قرارداد مورد نظر را انتخاب کنید',
            'title.required_if' => 'لطفا عنوان قرارداد جدید را وارد کنید',
            'type.required_if' => 'لطفا نوع قرارداد جدید را انتخاب کنید',
            'edit_title.required_with' => 'عنوان قرارداد نمی تواند خالی باشد',
        ]);

        $location = $request->location;

        if (! ContractUsage::isValidLocation($location)) {
            return redirect()->back()->withErrors(['location' => 'محل استفاده معتبر نیست']);
        }

        [$ownerType, $ownerId] = $this->resolveOwner($request);

        if (! $this->locationAcceptsOwner($location, $ownerType)) {
            return redirect()->back()->withErrors(['location' => 'این محل استفاده برای مورد انتخاب شده معتبر نیست']);
        }

        // «بدون قرارداد» — drop whatever is attached here and stop.
        if ($request->mode === 'none') {
            $this->detach($location, $ownerType, $ownerId);

            return redirect()->back()->with(['contract_usage' => 'detached']);
        }

        DB::transaction(function () use ($request, $location, $ownerType, $ownerId) {
            $contractId = $request->mode === 'new'
                ? $this->createContract($request)->id
                : (int) $request->contract_id;

            if ($request->mode === 'existing') {
                $this->applyContractEdits($request, $contractId);
            }

            // One contract per owner per location: replace rather than stack, so the
            // display points never have to guess between two active rows.
            $this->detach($location, $ownerType, $ownerId);

            ContractUsage::create([
                'contract_id' => $contractId,
                'usable_type' => $ownerType,
                'usable_id' => $ownerId,
                'location' => $location,
                'contract_number' => $request->contract_number,
                'contract_date' => $request->contract_date ?: null,
                'is_active' => 1,
                'position' => 0,
            ]);
        });

        return redirect()->back()->with(['contract_usage' => 'success']);
    }

    public function destroy(ContractUsage $contractUsage)
    {
        // Hard delete: see detach() — a soft-deleted row keeps its slot in the
        // unique index and would block re-attaching the same contract later.
        $contractUsage->forceDelete();

        return redirect()->back()->with(['contract_usage' => 'detached']);
    }

    /**
     * Contracts inside one folder, for the folder -> contract cascade in the
     * association widget. Folder «0» / absent means the uncategorised ones.
     */
    public function byFolder(Request $request)
    {
        $folderId = $request->input('folder_id');

        $contracts = Contract::query()
            ->when($folderId, fn ($q) => $q->where('contract_folder_id', $folderId))
            ->when(! $folderId, fn ($q) => $q->whereNull('contract_folder_id'))
            ->orderBy('title')
            ->get(['id', 'title', 'type']);

        return response()->json([
            'status' => true,
            'contracts' => $contracts->map(fn ($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'type' => Contract::TYPE[$c->type] ?? '',
            ])->values(),
        ]);
    }

    /**
     * Saves edits made to an existing contract from inside the association widget.
     *
     * The `edit_contract_id` interlock is the safety catch: the widget's javascript
     * writes it only after it has actually filled the edit fields with that contract's
     * current text. If the fetch never ran (script blocked, ajax failed, an old cached
     * page), the id does not match the selected contract and the edits are skipped —
     * so an empty textarea can never blank out a real contract's body. It also means
     * merely re-picking a contract from the dropdown and saving is a no-op.
     *
     * Editing here changes the bank contract everywhere it is used; the widget says so
     * out loud whenever the contract has more than this one usage.
     */
    private function applyContractEdits(Request $request, int $contractId): void
    {
        if ((int) $request->input('edit_contract_id') !== $contractId) {
            return;
        }

        $contract = Contract::find($contractId);

        if (! $contract) {
            return;
        }

        $contract->update([
            'title' => $request->edit_title,
            'type' => $request->edit_type ?: $contract->type,
            'content' => $request->edit_content,
        ]);

        // singleFile() collection: a new upload replaces the old original.
        if ($request->edit_file_path) {
            MediaLibraryHelper::attachTo($contract, $request->edit_file_path, 'file');

            ContractHistory::create([
                'contract_id' => $contract->id,
                'user_id' => Auth::user()->id,
                'file' => $contract->file,
            ]);
        }
    }

    /**
     * Validation for a contract body.
     *
     * `contracts.content` is MySQL TEXT — 65,535 **bytes**, not characters. Laravel's
     * `max:` counts characters, so a Persian contract (2 bytes per character in UTF-8)
     * would clear a character limit and then be silently truncated by MySQL, losing the
     * end of a legal document. Count bytes instead.
     */
    private function contractBodyRule(): array
    {
        return ['nullable', 'string', function ($attribute, $value, $fail) {
            if (strlen((string) $value) > 65535) {
                $fail('متن قرارداد طولانی تر از حد مجاز است. لطفا آن را کوتاه تر کنید یا فایل قرارداد را بارگذاری کنید.');
            }
        }];
    }

    /**
     * One contract in full, so «استفاده از قرارداد موجود» can edit it in place
     * instead of sending the user off to the contract bank.
     *
     * usages_count is part of the payload on purpose: the widget warns that a
     * shared contract is about to be changed everywhere it is used.
     */
    public function contractDetail(Contract $contract)
    {
        $contract->loadCount('usages');

        return response()->json([
            'status' => true,
            'contract' => [
                'id' => $contract->id,
                'title' => $contract->title,
                'type' => $contract->type,
                'content' => $contract->content,
                'contract_folder_id' => $contract->contract_folder_id,
                'usages_count' => $contract->usages_count,
                'has_file' => $contract->hasOriginalFile(),
                'file_url' => $contract->hasOriginalFile() ? $contract->file : null,
            ],
        ]);
    }

    /**
     * The «ایجاد قرارداد جدید» branch: a real bank contract, so it is reusable
     * everywhere afterwards rather than being trapped in this one association.
     */
    private function createContract(Request $request): Contract
    {
        $contract = Contract::create([
            'title' => $request->title,
            'type' => $request->type,
            'status' => $request->input('status', 1),
            'content' => $request->input('content'),
            'contract_folder_id' => $request->contract_folder_id ?: null,
        ]);

        if ($request->file_path) {
            MediaLibraryHelper::attachTo($contract, $request->file_path, 'file');
        }

        ContractHistory::create([
            'contract_id' => $contract->id,
            'user_id' => Auth::user()->id,
            'file' => $contract->file,
        ]);

        return $contract;
    }

    /**
     * @return array{0: ?string, 1: ?int} morph type and id, both null for a
     *                                    location-wide contract.
     */
    private function resolveOwner(Request $request): array
    {
        $ownerType = self::OWNER_TYPES[$request->owner_type] ?? null;
        $ownerId = $request->owner_id ? (int) $request->owner_id : null;

        // Half an owner is not an owner — treat it as location-wide.
        if (! $ownerType || ! $ownerId) {
            return [null, null];
        }

        return [$ownerType, $ownerId];
    }

    private function locationAcceptsOwner(string $location, ?string $ownerType): bool
    {
        return in_array($ownerType, ContractUsage::LOCATION_OWNERS[$location] ?? [], true);
    }

    /**
     * Removes every usage row for this owner+location.
     *
     * Hard delete on purpose: the unique index counts soft-deleted rows, so a
     * soft delete here would block ever re-attaching that same contract to the
     * same owner and location. The row is a pure relation — the audit trail that
     * matters lives in contract_histories.
     *
     * MySQL also treats NULLs as distinct in that index, so the location-wide row
     * (owner NULL) is de-duplicated here rather than by the constraint.
     */
    private function detach(string $location, ?string $ownerType, ?int $ownerId): void
    {
        ContractUsage::query()
            ->location($location)
            ->when($ownerType, fn ($q) => $q->for($ownerType, $ownerId))
            ->when(! $ownerType, fn ($q) => $q->global())
            ->forceDelete();
    }
}
