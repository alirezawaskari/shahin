<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TotalFactorItem;

class TotalFactorItemContractSettlementController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirm(Request $request)
    {
        $total_factor_item = TotalFactorItem::find($request->total_factor_item_id);
        $status_mali = $total_factor_item->status_mali;
        if ($total_factor_item->status_mali == 7 && auth()->user()->hasRole('mali|superadmin')) {
            $status_mali = 8;
            $mali_id = auth()->user()->id;
            $total_factor_item->totalFactorItemContractSettlement->update([
                'mali_id' => $mali_id,
            ]);
        }

        if ($total_factor_item->status_mali == 8 && auth()->user()->hasRole('superadmin')) {
            $status_mali = 9;
            $superadmin_id = auth()->user()->id;
            $total_factor_item->totalFactorItemContractSettlement->update([
                'superadmin_id' => $superadmin_id,
            ]);
        }
        $total_factor_item->update(['status_mali' => $status_mali]);

        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }
}
