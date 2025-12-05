<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Listing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.listing_id' => 'required|exists:listings,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping' => 'nullable|numeric|min:0',
            'address' => 'required|string'
        ]);

        $buyer = $request->user();

        DB::beginTransaction();
        try {
            $order = Order::create([
                'order_number' => 'ORD-'.Str::upper(Str::random(8)),
                'buyer_id' => $buyer->id,
                'vendor_profile_id' => $request->items[0]['vendor_profile_id'] ?? null, // support single-vendor orders for MVP
                'status' => 'pending',
                'subtotal' => 0,
                'shipping' => $request->input('shipping', 0),
                'taxes' => 0,
                'platform_commission' => 0,
                'total' => 0,
                'meta' => ['shipping_address' => $request->address]
            ]);

            $subtotal = 0;
            foreach ($request->input('items') as $item) {
                $listing = Listing::lockForUpdate()->findOrFail($item['listing_id']);

                if ($listing->stock < $item['quantity']) {
                    DB::rollBack();
                    return response()->json(['error' => "Insufficient stock for {$listing->title}"], 422);
                }

                $lineTotal = round($listing->price * $item['quantity'], 2);
                $order->orderItems()->create([
                    'listing_id' => $listing->id,
                    'title' => $listing->title,
                    'quantity' => $item['quantity'],
                    'unit_price' => $listing->price,
                    'line_total' => $lineTotal
                ]);

                // reserve/decrement stock in listing & warehouse logic (simple decrement here)
                $listing->decrement('stock', $item['quantity']);

                $subtotal += $lineTotal;
            }

            $order->subtotal = $subtotal;
            $order->platform_commission = round(0.08 * $subtotal, 2); // example 8%
            $order->taxes = 0; // taxes will be calculated at checkout if required
            $order->total = round($subtotal + $order->shipping + $order->taxes + $order->platform_commission, 2);
            $order->save();

            DB::commit();

            return response()->json(['order' => $order], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Order create failed: '.$e->getMessage());
            return response()->json(['error' => 'Unable to create order'], 500);
        }
    }

    public function show(Order $order)
    {
        $order->load('orderItems.listing','buyer','vendor');
        return response()->json($order);
    }
}
