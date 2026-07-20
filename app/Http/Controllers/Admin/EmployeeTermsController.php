<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Models\Context;
use App\Models\Employee;
use App\Models\EmployeeTerm;
use Illuminate\Http\Request;

class EmployeeTermsController extends Controller
{
    const PREFIX_TERMS_KEY_IN_CONTEXT_TABLE = EmployeeTerm::LEGACY_CONTEXT_PREFIX;

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $role_categories_with_terms = EmployeeTerm::rolesWithTerms();

        return view('Admin.rules.employeeTermsIndex', compact('role_categories_with_terms'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // A genuinely new file was chosen (vs. a bare submit that only migrates the legacy
        // file old->Spatie). Only a NEW file invalidates prior agreements.
        $isNewUpload = $request->filled('url_terms_file');

        $term = EmployeeTerm::firstOrCreate(['role_category' => $request->role_category]);

        // Wire the pre-existing Context file onto the model as its legacy source (only if it
        // has none yet) via a no-event update, so AdoptsLegacyMedia can adopt it on save().
        // NOT a bulk backfill: it runs one category at a time, at the moment of migration.
        if (empty($term->getRawOriginal('file_path'))) {
            $context = Context::fetchByKey(self::PREFIX_TERMS_KEY_IN_CONTEXT_TABLE . $request->role_category);
            if ($context && $context->value) {
                EmployeeTerm::where('id', $term->id)->update(['file_path' => $context->value]);
                $term->refresh();
            }
        }

        // Attach the newly uploaded PDF (ajax temp media id) into Spatie. If none was uploaded,
        // this is a no-op and the save below instead adopts the legacy file_path.
        MediaLibraryHelper::attachTo($term, $request->url_terms_file, 'terms_file');

        // Fire AdoptsLegacyMedia: with new media present it skips adoption (new file wins);
        // with no new upload it adopts the legacy file_path into Spatie (touch-to-migrate).
        $term->save();

        // New terms => every employee in this role category must re-agree. A plain migrate
        // (old->new, same document) must NOT reset agreements, so guard on $isNewUpload.
        if ($isNewUpload) {
            Employee::whereHas('user', function ($q) use ($request) {
                $q->whereHas('roles', function ($q2) use ($request) {
                    $q2->where('category', $request->role_category);
                });
            })->update(['is_terms_accept' => 0]);
        }

        return back();
    }
}
