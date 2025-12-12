<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packing Slip - Order #{{ $order->order_number }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            padding: 20px; 
            max-width: 900px;
            margin: 0 auto;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #333; 
            padding-bottom: 10px; 
        }
        .section { margin-bottom: 15px; }
        .label { font-weight: bold; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 10px 0; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
            vertical-align: top;
        }
        th { 
            background-color: #f5f5f5; 
            font-weight: bold;
        }
        .footer { 
            margin-top: 30px; 
            padding-top: 10px; 
            border-top: 1px solid #333; 
        }
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .product-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .product-details {
            flex: 1;
        }
        .contact-info {
            margin-top: 5px;
            font-size: 11px;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-paid { background-color: #d1fae5; color: #065f46; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-shipped { background-color: #dbeafe; color: #1e40af; }
        .totals-table {
            width: 300px;
            margin-left: auto;
            margin-top: 15px;
        }
        .totals-table td {
            border: none;
            padding: 4px 8px;
        }
        .totals-table tr.total-row {
            border-top: 2px solid #333;
            font-weight: bold;
        }
        .shipping-info {
            background-color: #f8fafc;
            padding: 10px;
            border-radius: 4px;
            margin-top: 15px;
        }
        .print-controls {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }
        .print-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .print-buttons {
            display: flex;
            gap: 10px;
        }
        .print-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .print-btn.print {
            background: #3b82f6;
            color: white;
        }
        .print-btn.close {
            background: #6b7280;
            color: white;
        }
        .company-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .logo-icon {
            width: 40px;
            height: 40px;
            background: #3b82f6;
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            .print-controls { display: none; }
            .product-img {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <!-- Print Controls -->
    <div class="no-print print-controls">
        <div class="print-header">
            <div>
                <h2 style="margin: 0; color: #1e293b;">Packing Slip Preview</h2>
                <p style="margin: 5px 0 0 0; color: #64748b;">Order #{{ $order->order_number }}</p>
            </div>
            <div class="print-buttons">
                <button class="print-btn print" onclick="window.print()">
                    🖨️ Print Packing Slip
                </button>
                <button class="print-btn close" onclick="window.close()">
                    ✕ Close Window
                </button>
            </div>
        </div>
        <div style="color: #64748b; font-size: 11px; margin-top: 10px;">
            <strong>Tip:</strong> Print this slip and include it in your shipment package.
        </div>
    </div>
    
    <!-- Company Header -->
    <div class="company-logo">
        <div class="logo-icon">
            📦
        </div>
        <div>
            <h1 style="margin: 0; font-size: 24px; color: #1e293b;">PACKING SLIP</h1>
            <p style="margin: 5px 0 0 0; color: #64748b;">
                {{ Auth::user()->vendorProfile->business_name ?? 'Your Store' }}
            </p>
        </div>
    </div>
    
    <!-- Order Information -->
    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
        <div>
            <div><strong>Order #:</strong> {{ $order->order_number }}</div>
            <div><strong>Date:</strong> {{ $order->created_at->format('F d, Y h:i A') }}</div>
            <div>
                <strong>Status:</strong> 
                <span class="status-badge status-{{ $order->status }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
        </div>
        <div style="text-align: right;">
            @php
                $totalItems = $order->items->sum('quantity');
            @endphp
            <div><strong>Total Items:</strong> {{ $totalItems }}</div>
            <div><strong>Package:</strong> #{{ $order->id }}</div>
            <div><strong>Slip Generated:</strong> {{ now()->format('M d, Y') }}</div>
        </div>
    </div>
    
    <!-- Sender & Receiver -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        <!-- Sender (Vendor) -->
        <div style="border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px;">
            <div style="font-weight: bold; color: #3b82f6; margin-bottom: 10px; font-size: 14px;">
                🏢 SHIP FROM
            </div>
            <div style="font-weight: bold; font-size: 13px;">
                {{ Auth::user()->vendorProfile->business_name ?? 'Your Store' }}
            </div>
            <div>{{ Auth::user()->vendorProfile->address ?? 'Your Address' }}</div>
            <div>{{ Auth::user()->vendorProfile->city ?? '' }}, {{ Auth::user()->vendorProfile->country ?? '' }}</div>
            <div class="contact-info">
                📞 {{ Auth::user()->vendorProfile->phone ?? 'Phone: N/A' }}<br>
                ✉️ {{ Auth::user()->email }}
            </div>
        </div>
        
        <!-- Receiver (Customer) -->
        <div style="border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px;">
            <div style="font-weight: bold; color: #10b981; margin-bottom: 10px; font-size: 14px;">
                👤 SHIP TO
            </div>
            <div style="font-weight: bold; font-size: 13px;">
                {{ $order->buyer->name ?? 'Customer' }}
            </div>
            @php $meta = $order->meta ?? []; @endphp
            @if(isset($meta['shipping_address']))
            <div>{{ $meta['shipping_address'] }}</div>
            <div>{{ $meta['shipping_city'] ?? '' }}, {{ $meta['shipping_country'] ?? '' }}</div>
            <div>{{ $meta['shipping_postal_code'] ?? '' }}</div>
            @endif
            <div class="contact-info">
                📞 {{ $order->buyer->phone ?? 'Phone: N/A' }}<br>
                ✉️ {{ $order->buyer->email ?? 'Email: N/A' }}
            </div>
        </div>
    </div>
    
    <!-- Shipping Information -->
    @if($meta && isset($meta['shipping_info']))
    <div class="shipping-info">
        <div style="font-weight: bold; margin-bottom: 8px; color: #6366f1;">🚚 SHIPPING DETAILS</div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
            <div><strong>Carrier:</strong> {{ ucfirst($meta['shipping_info']['carrier'] ?? 'N/A') }}</div>
            <div><strong>Tracking #:</strong> {{ $meta['shipping_info']['tracking_number'] ?? 'Not assigned' }}</div>
            <div><strong>Estimated Delivery:</strong> 
                @if(isset($meta['shipping_info']['estimated_delivery']))
                    {{ \Carbon\Carbon::parse($meta['shipping_info']['estimated_delivery'])->format('M d, Y') }}
                @else
                    Not specified
                @endif
            </div>
        </div>
    </div>
    @endif
    
    <!-- Items Table -->
    <div style="margin-top: 20px;">
        <div style="font-weight: bold; margin-bottom: 10px; color: #1e293b; font-size: 14px;">
            📋 ORDER ITEMS ({{ $order->items->count() }} items, {{ $totalItems }} units)
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">Image</th>
                    <th>Product Details</th>
                    <th style="width: 80px;">SKU</th>
                    <th style="width: 60px;">Qty</th>
                    <th style="width: 80px;">Unit Price</th>
                    <th style="width: 100px;">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                <tr>
                    <td>
                        @if($item->listing && $item->listing->images->first())
                        <img src="{{ asset('storage/' . $item->listing->images->first()->path) }}" 
                             alt="{{ $item->title }}" 
                             class="product-img">
                        @else
                        <div style="width: 50px; height: 50px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                            📦
                        </div>
                        @endif
                    </td>
                    <td>
                        <div class="product-info">
                            <div class="product-details">
                                <div style="font-weight: bold;">{{ $item->title }}</div>
                                @if($item->listing)
                                <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                                    {{ Str::limit($item->listing->description ?? '', 100) }}
                                </div>
                                @endif
                                @if($item->listing && $item->listing->origin)
                                <div style="font-size: 10px; margin-top: 3px;">
                                    <span style="background: #e0f2fe; color: #0369a1; padding: 1px 4px; border-radius: 2px;">
                                        {{ ucfirst($item->listing->origin) }}
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-family: monospace; font-size: 11px;">
                            {{ $item->listing->sku ?? 'N/A' }}
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <div style="font-weight: bold;">{{ $item->quantity }}</div>
                    </td>
                    <td style="text-align: right;">
                        ${{ number_format($item->unit_price, 2) }}
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        ${{ number_format($item->line_total, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Order Totals -->
        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td style="text-align: right;">${{ number_format($order->subtotal, 2) }}</td>
            </tr>
            @if($order->shipping > 0)
            <tr>
                <td>Shipping:</td>
                <td style="text-align: right;">${{ number_format($order->shipping, 2) }}</td>
            </tr>
            @endif
            @if($order->taxes > 0)
            <tr>
                <td>Taxes:</td>
                <td style="text-align: right;">${{ number_format($order->taxes, 2) }}</td>
            </tr>
            @endif
            @if($order->platform_commission > 0)
            <tr>
                <td>Platform Fee:</td>
                <td style="text-align: right;">${{ number_format($order->platform_commission, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td><strong>ORDER TOTAL:</strong></td>
                <td style="text-align: right;"><strong>${{ number_format($order->total, 2) }}</strong></td>
            </tr>
        </table>
    </div>
    
    <!-- Payment Information -->
    @if($order->payments->count() > 0)
    <div style="margin-top: 20px; padding: 10px; background: #f0f9ff; border-radius: 6px;">
        <div style="font-weight: bold; margin-bottom: 8px; color: #0369a1;">💳 PAYMENT INFORMATION</div>
        @foreach($order->payments as $payment)
        <div style="display: flex; justify-content: space-between; padding: 4px 0;">
            <div>
                {{ ucfirst($payment->provider) }} - 
                <span style="color: {{ $payment->status == 'completed' ? '#059669' : '#d97706' }};">
                    {{ ucfirst($payment->status) }}
                </span>
            </div>
            <div><strong>${{ number_format($payment->amount, 2) }}</strong></div>
        </div>
        @endforeach
    </div>
    @endif
    
    <!-- Notes Section -->
    @if($meta && isset($meta['notes']))
    <div style="margin-top: 20px; padding: 10px; background: #fefce8; border-radius: 6px;">
        <div style="font-weight: bold; margin-bottom: 8px; color: #ca8a04;">📝 CUSTOMER NOTES</div>
        <div>{{ $meta['notes'] }}</div>
    </div>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 20px;">
            <div>
                <div style="font-weight: bold; margin-bottom: 8px; color: #1e293b;">📦 PACKING CHECKLIST</div>
                <div style="font-size: 11px;">
                    □ All items verified against order<br>
                    □ Items properly protected/packaged<br>
                    □ Documentation included<br>
                    □ Package sealed securely<br>
                    □ Shipping label applied correctly<br>
                    □ Tracking updated in system
                </div>
            </div>
            <div>
                <div style="font-weight: bold; margin-bottom: 8px; color: #1e293b;">✍️ PACKING DETAILS</div>
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="border: none; padding: 2px 0;"><strong>Packed By:</strong></td>
                        <td style="border: none; padding: 2px 0;">____________________</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px 0;"><strong>Date Packed:</strong></td>
                        <td style="border: none; padding: 2px 0;">____________________</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px 0;"><strong>Date Shipped:</strong></td>
                        <td style="border: none; padding: 2px 0;">____________________</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px 0;"><strong>Package Weight:</strong></td>
                        <td style="border: none; padding: 2px 0;">____________________</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Important Information -->
        <div style="text-align: center; font-size: 10px; color: #64748b; margin-top: 20px; padding-top: 15px; border-top: 1px dashed #cbd5e1;">
            <div style="font-weight: bold; margin-bottom: 5px;">IMPORTANT INFORMATION</div>
            <div>
                • Keep this packing slip for your records • Include this slip with any returns<br>
                • Report any discrepancies within 48 hours • Contact support for assistance
            </div>
            <div style="margin-top: 10px; font-size: 9px;">
                Generated by {{ config('app.name') }} on {{ now()->format('F d, Y \a\t h:i A') }}
            </div>
        </div>
    </div>
    
    <!-- Print Instructions -->
    <div class="no-print" style="margin-top: 30px; padding: 15px; background: #fffbeb; border-radius: 8px; border: 1px solid #fde68a;">
        <div style="font-weight: bold; color: #92400e; margin-bottom: 8px;">💡 PRINTING TIPS</div>
        <div style="font-size: 11px; color: #92400e;">
            • Print at 100% scale on A4 or Letter paper<br>
            • Use "Fit to page" option in print settings<br>
            • For thermal printers, test print first<br>
            • Consider printing two copies (one for package, one for records)
        </div>
    </div>

    <script>
        // Add CSS for status badges
        document.addEventListener('DOMContentLoaded', function() {
            const style = document.createElement('style');
            style.textContent = `
                .status-paid { background-color: #d1fae5; color: #065f46; }
                .status-pending { background-color: #fef3c7; color: #92400e; }
                .status-confirmed { background-color: #dbeafe; color: #1e40af; }
                .status-shipped { background-color: #e0e7ff; color: #3730a3; }
                .status-delivered { background-color: #dcfce7; color: #166534; }
                .status-cancelled { background-color: #fee2e2; color: #991b1b; }
                .status-processing { background-color: #fef3c7; color: #92400e; }
            `;
            document.head.appendChild(style);
        });
        
        // Auto-print option (uncomment to enable)
        // setTimeout(() => window.print(), 1000);
        
        // Handle print event
        window.addEventListener('afterprint', function() {
            console.log('Packing slip printed');
        });
    </script>
</body>
</html>