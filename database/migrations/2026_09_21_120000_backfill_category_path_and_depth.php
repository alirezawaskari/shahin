<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Category::$fillable did not include `path` or `depth`, so every category created from the
 * panel was saved with both NULL even though the create form posts them. Code that reads the
 * tree (findAllFather, findFather, the product wizard's کالا/خدمت detection, …) then got it
 * wrong — e.g. a new service category was treated as a goods category.
 *
 * This fills in only the missing column(s) of non-root rows, from their parent. It runs in
 * passes so a child is repaired after its (possibly also broken) parent. Root rows
 * (parent_id = 0) are left alone: their path is NULL by design.
 */
return new class extends Migration
{
    public function up(): void
    {
        do {
            $rows = DB::table('categories as c')
                ->join('categories as p', 'p.id', '=', 'c.parent_id')
                ->where('c.parent_id', '!=', 0)
                ->where(fn($q) => $q->whereNull('c.path')->orWhere('c.path', '')->orWhereNull('c.depth'))
                // only once the parent itself is complete (a root, or has path + depth)
                ->where(fn($q) => $q->where('p.parent_id', 0)
                    ->orWhere(fn($q) => $q->whereNotNull('p.path')->where('p.path', '!=', '')->whereNotNull('p.depth')))
                ->select('c.id', 'c.path', 'c.depth', 'p.id as parent', 'p.path as parent_path',
                    'p.depth as parent_depth', 'p.parent_id as grandparent')
                ->get();

            foreach ($rows as $row) {
                $parent_is_root = (int) $row->grandparent === 0;
                $update = [];

                if ($row->path === null || $row->path === '') {
                    $update['path'] = $parent_is_root ? (string) $row->parent : $row->parent_path . '/' . $row->parent;
                }
                if ($row->depth === null) {
                    $update['depth'] = $parent_is_root ? 1 : (int) $row->parent_depth + 1;
                }

                DB::table('categories')->where('id', $row->id)->update($update);
            }
        } while ($rows->isNotEmpty());
    }

    public function down(): void
    {
        // Nothing to undo: the NULLs were the bug.
    }
};
