@extends('layouts.vendor')

@section('title', 'My Orders - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Order Management</h1>
            <p class="text-gray-600">Manage and track customer orders</p>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Export Button -->
            <button onclick="exportOrders()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-download mr-2"></i> Export
            </button>
            <!-- Refresh Button -->
            <button onclick="window.location.reload()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-sync-alt mr-2"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <p class="text-sm text-gray-600">Total Orders</p>
            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <p class="text-sm text-gray-600">Pending</p>
            <p class="text-2xl font-bold">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <p class="text-sm text-gray-600">Paid</p>
            <p class="text-2xl font-bold">{{ $stats['paid'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
            <p class="text-sm text-gray-600">Processing</p>
            <p class="text-2xl font-bold">{{ $stats['processing'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-indigo-500">
            <p class="text-sm text-gray-600">Revenue</p>
            <p class="text-2xl font-bold">UGX {{ number_format($stats['revenue'], 2) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('vendor.orders.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg p-2">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Order # or customer" 
                       class="w-full border border-gray-300 rounded-lg p-2">
            </div>

            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="w-full border border-gray-300 rounded-lg p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="w-full border border-gray-300 rounded-lg p-2">
            </div>

            <!-- Action Buttons -->
            <div class="md:col-span-4 flex justify-end space-x-3 mt-2">
                <button type="reset" onclick="window.location='{{ route('vendor.orders.index') }}'" 
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Clear
                </button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $order->order_number }}
                            </div>
                            @if($order->meta && isset($order->meta['tracking_number']))
                            <div class="text-xs text-gray-500">
                                Track: {{ $order->meta['tracking_number'] }}
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $order->buyer->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $order->buyer->email ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $order->items->count() }} items
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">
                                UGX {{ number_format($order->total, 2) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'paid' => 'bg-blue-100 text-blue-800',
                                    'processing' => 'bg-purple-100 text-purple-800',
                                    'shipped' => 'bg-indigo-100 text-indigo-800',
                                    'delivered' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('vendor.orders.show', $order) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                @if(in_array($order->status, ['paid', 'processing']))
                                <button onclick="showStatusModal('{{ $order->id }}')" 
                                        class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-shipping-fast mr-1"></i> Ship
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-400">
                                <i class="fas fa-shopping-cart text-4xl mb-3"></i>
                                <p class="text-lg">No orders yet</p>
                                <p class="text-sm mt-1">Start listing products to receive orders</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Update Order Status</h3>
        <p class="text-gray-600 mb-4">Order: <span id="orderNumber" class="font-semibold"></span></p>
        
        <form id="statusForm" method="POST">
            @csrf
            @method('POST')
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">New Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg p-2">
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Tracking Number</label>
                <input type="text" name="tracking_number" 
                       class="w-full border border-gray-300 rounded-lg p-2"
                       placeholder="Optional">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Notes</label>
                <textarea name="notes" rows="3" 
                          class="w-full border border-gray-300 rounded-lg p-2"
                          placeholder="Add notes for customer"></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeStatusModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    Update Status
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Status Modal Functions
    function showStatusModal(orderId) {
        // Set form action
        document.getElementById('statusForm').action = `/vendor/orders/${orderId}/status`;
        document.getElementById('statusModal').classList.remove('hidden');
        
        // You could fetch order details via AJAX here
        // For now, just show the modal
    }
    
    function closeStatusModal() {
        document.getElementById('statusModal').classList.add('hidden');
        document.getElementById('statusForm').reset();
    }
    
    // Close modal when clicking outside
    document.getElementById('statusModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeStatusModal();
        }
    });
    
    // Export orders function
    function exportOrders() {
        const params = new URLSearchParams(window.location.search);
        params.append('export', 'true');
        
        fetch(`{{ route('vendor.orders.index') }}?${params}`)
            .then(response => response.json())
            .then(data => {
                // Create CSV content
                let csvContent = "data:text/csv;charset=utf-8,";
                csvContent += "Order Number,Date,Customer,Status,Items,Subtotal,Shipping,Total\n";
                
                data.orders.forEach(order => {
                    csvContent += `${order['Order Number']},${order.Date},${order['Buyer Name']},`;
                    csvContent += `${order.Status},${order['Items Count']},${order.Subtotal},`;
                    csvContent += `${order.Shipping},${order.Total}\n`;
                });
                
                // Create download link
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", `orders_export_${new Date().toISOString().slice(0,10)}.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            })
            .catch(error => {
                console.error('Export failed:', error);
                alert('Failed to export orders. Please try again.');
            });
    }
</script>
@endsection