<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FactorItemsExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\FactorItemProductCollection;
use App\Models\Category;
use App\Models\FactorItem;
use App\Models\FactorItemProduct;
use Illuminate\Http\Request;

class FactorItemProductController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $categories = Category::filter()->get();
        return view('Admin.factoritemproduct.all' , compact('categories'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function desktop()
    {
        $user = auth()->user();
        $detail = false;
        $edit = false;
        if ($user->isAbleTo('file-detail')) {
            $detail = true;
        }
        if ($user->isAbleTo('file-edit')) {
            $edit = true;
        }

        FactorItem::filter()->where('status_factor', 3)->where('expired_at', '<=', \Carbon\Carbon::now())->update(['status_factor' => 14]);

        return view('Admin.factoritem.suspension', compact('detail', 'edit'));
    }

    /**
     * @param Request $request
     * @return FactorItemProductCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter(Request $request)
    {
        $factor_items = FactorItemProduct::filter()->latest();

        if ($request->exel == 'true') {
            return (new FactorItemsExport($factor_items))->download('factorItems.xlsx');
        }

        return new FactorItemProductCollection($factor_items->paginate(20));
    }

    /**
     * @param Request $request
     * @return FactorItemCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterDesktop(Request $request)
    {
        if (auth()->user()->center_id) {
            $factor_items = FactorItem::where('status_factor', 10)->filter();
        }

        if ($request->exel == 'true') {
            return (new FactorItemsExport($factor_items))->download('factorItems.xlsx');
        }
        return new FactorItemCollection($factor_items->paginate(20));
    }

}
