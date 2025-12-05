<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::withCount('warehouseStocks')->get();
        return view('logistics.warehouses.index', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string'
        ]);
        $w = Warehouse::create($request->only(['name','code','country','city','address']));
        return redirect()->route('logistics.warehouses.index')->with('success','Warehouse created');
    }

    public function receive(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'quantity' => 'required|integer|min:1',
            'serial_number' => 'nullable|string'
        ]);

        $stock = WarehouseStock::create([
            'warehouse_id' => $warehouse->id,
            'listing_id' => $request->listing_id,
            'serial_number' => $request->serial_number,
            'quantity' => $request->quantity,
            'status' => 'available'
        ]);

        return response()->json(['message' => 'Stock received', 'stock' => $stock]);
    }
}
