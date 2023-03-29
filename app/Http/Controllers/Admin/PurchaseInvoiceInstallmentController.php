<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoiceInstallment;

class PurchaseInvoiceInstallmentController extends Controller
{
    public function destroy(PurchaseInvoiceInstallment $purchaseInvoiceInstallment)
    {
        $purchase_invoice = $purchaseInvoiceInstallment->purchaseInvoice;
        if($purchase_invoice->status == 0 || $purchase_invoice->status == 1 || $purchase_invoice->status == 2)
        {
            $purchaseInvoiceInstallment->delete();
            return redirect()->back()->with([
                'delete' => 'success'
            ]);
        }
        else
        {
            return redirect()->back()->with([
                'failByMessage' => 'این فاکتور تایید شده است امکان حذف قسط وجود ندارد.'
            ]);
        }
    }
}
