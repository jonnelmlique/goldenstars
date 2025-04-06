<?php

namespace App\Http\Controllers;

use App\Models\WarehouseInventory;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function print($id)
    {
        $inventory = WarehouseInventory::findOrFail($id);
        return view('print.barcode', compact('inventory'));
    }

    public function printMultiple(Request $request)
    {
        $ids = explode(',', $request->ids);
        $inventories = WarehouseInventory::whereIn('id', $ids)->get();

        if ($inventories->isEmpty()) {
            return redirect()->back()->with('error', 'No items found to print');
        }

        return view('print.barcodes', compact('inventories'));
    }
}
