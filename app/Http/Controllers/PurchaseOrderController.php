<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    /**
     * Tampilan Cetak Dokumen Purchase Order
     */
    public function print($id)
    {
        $purchaseOrder = PurchaseOrder::with(['supplier', 'branch', 'items.product'])->findOrFail($id);

        return view('purchase-orders.print', compact('purchaseOrder'));
    }

    /**
     * Tampilan Cetak Surat Jalan Purchase Order
     */
    public function suratJalan($id)
    {
        $purchaseOrder = PurchaseOrder::with(['supplier', 'branch', 'items.product'])->findOrFail($id);

        return view('purchase-orders.surat-jalan', compact('purchaseOrder'));
    }
}