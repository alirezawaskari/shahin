<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FactorItemsExport;
use App\Models\FactorItem;
use App\Http\Controllers\Controller;
use App\Http\Resources\FactorItemProductCollection;
use App\Models\FactorItemProduct;
use App\Models\User;
use App\Models\Province;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Category;

class FactorItemProductController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('Admin.factoritemproduct.all' , compact('categories'));
    }

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

    public function filter(Request $request)
    {
        $factor_items = FactorItemProduct::filter()->latest();

        if ($request->exel == 'true') {
            return (new FactorItemsExport($factor_items))->download('factorItems.xlsx');
        }

        return new FactorItemProductCollection($factor_items->paginate(20));
    }

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
