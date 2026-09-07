<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContractFolder;
use Illuminate\Http\Request;

/**
 * پوشه های قرارداد — the folders that organise the contract bank.
 */
class ContractFolderController extends Controller
{
    private const RULES = [
        'title' => 'required|string|max:191',
        'parent_id' => 'nullable|integer|exists:contract_folders,id',
        'description' => 'nullable|string|max:500',
        'position' => 'nullable|integer',
    ];

    private const MESSAGES = [
        'title.required' => 'لطفا عنوان پوشه را وارد کنید',
        'parent_id.exists' => 'پوشه والد انتخاب شده معتبر نیست',
    ];

    public function index(Request $request)
    {
        $folders = ContractFolder::withCount('contracts')
            ->with('parent')
            ->orderBy('position')
            ->orderBy('title');

        $queries = [];

        if ($request->filled('title')) {
            $folders->where('title', 'LIKE', '%' . $request->title . '%');
            $queries['title'] = $request->title;
        }

        if ($request->has('is_active') && $request->is_active !== 'all') {
            $folders->where('is_active', (int) $request->is_active);
            $queries['is_active'] = $request->is_active;
        } elseif ($request->has('is_active')) {
            $queries['is_active'] = 'all';
        }

        $folders = $folders->paginate(15)->appends($queries);

        return view('Admin.contractFolder.all', compact('folders', 'queries'));
    }

    public function create()
    {
        return view('Admin.contractFolder.create', [
            'parents' => ContractFolder::options(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(self::RULES, self::MESSAGES);

        ContractFolder::create([
            'title' => $request->title,
            'parent_id' => $request->parent_id ?: null,
            'description' => $request->description,
            'position' => (int) $request->position,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect('/dashboard/contractFolder')->with(['create' => 'success']);
    }

    public function edit(ContractFolder $contractFolder)
    {
        return view('Admin.contractFolder.edit', [
            'folder' => $contractFolder,
            // A folder may not be its own parent.
            'parents' => array_diff_key(ContractFolder::options(), [$contractFolder->id => true]),
        ]);
    }

    public function update(Request $request, ContractFolder $contractFolder)
    {
        $request->validate(self::RULES, self::MESSAGES);

        if ((int) $request->parent_id === $contractFolder->id) {
            return redirect()->back()->withInput()
                ->withErrors(['parent_id' => 'پوشه نمی تواند والد خودش باشد']);
        }

        $contractFolder->update([
            'title' => $request->title,
            'parent_id' => $request->parent_id ?: null,
            'description' => $request->description,
            'position' => (int) $request->position,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect('/dashboard/contractFolder')->with(['update' => 'success']);
    }

    public function destroy(ContractFolder $contractFolder)
    {
        // Contracts outlive their folder. This is a soft delete, so the FK never
        // fires; the folder relation just resolves to null behind the SoftDeletes
        // scope and the contracts read as «دسته‌بندی نشده» in the bank.
        if ($contractFolder->children()->exists()) {
            return redirect()->back()
                ->withErrors(['folder' => 'ابتدا زیرپوشه های این پوشه را حذف کنید']);
        }

        $contractFolder->delete();

        return redirect()->back()->with(['delete' => 'success']);
    }
}
