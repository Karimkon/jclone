@extends('layouts.admin')

@section('title', 'Pending Vendors - JClone Admin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pending Vendor Applications</h1>
            <p class="text-gray-600">Review and approve new vendor registrations</p>
        </div>
        <div class="bg-indigo-50 px-4 py-2 rounded-lg">
            <span class="text-indigo-700 font-bold">{{ $pendingVendors->total() }}</span>
            <span class="text-gray-600">pending applications</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <p class="text-sm text-gray-600">Pending Review</p>
            <p class="text-2xl font-bold">{{ $pendingVendors->count() }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <p class="text-sm text-gray-600">Today's Applications</p>
            <p class="text-2xl font-bold">{{ \App\Models\VendorProfile::whereDate('created_at', today())->count() }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <p class="text-sm text-gray-600">Approved This Week</p>
            <p class="text-2xl font-bold">{{ \App\Models\VendorProfile::where('vetting_status', 'approved')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count() }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
            <p class="text-sm text-gray-600">Rejected This Week</p>
            <p class="text-2xl font-bold">{{ \App\Models\VendorProfile::where('vetting_status', 'rejected')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count() }}</p>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documents</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pendingVendors as $vendor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-store text-indigo-600"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $vendor->business_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $vendor->user->email }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($vendor->vendor_type == 'local_retail') bg-blue-100 text-blue-800
                                @elseif($vendor->vendor_type == 'china_supplier') bg-green-100 text-green-800
                                @else bg-purple-100 text-purple-800 @endif">
                                {{ str_replace('_', ' ', ucfirst($vendor->vendor_type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                @php
                                    $docCount = $vendor->documents->count();
                                    $verifiedCount = $vendor->documents->where('status', 'verified')->count();
                                @endphp
                                <span class="text-sm text-gray-900">{{ $docCount }} files</span>
                                @if($verifiedCount > 0)
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                        {{ $verifiedCount }} verified
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $vendor->created_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.vendors.show', $vendor) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-eye mr-1"></i> Review
                                </a>
                                <form action="{{ route('admin.vendors.approve', $vendor) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="text-green-600 hover:text-green-900"
                                            onclick="return confirm('Approve this vendor?')">
                                        <i class="fas fa-check mr-1"></i> Approve
                                    </button>
                                </form>
                                <button type="button"
                                        onclick="showRejectModal({{ $vendor->id }}, '{{ $vendor->business_name }}')"
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-times mr-1"></i> Reject
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="text-gray-400">
                                <i class="fas fa-clipboard-check text-4xl mb-3"></i>
                                <p class="text-lg">No pending applications</p>
                                <p class="text-sm mt-1">All vendor applications have been reviewed</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($pendingVendors->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $pendingVendors->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Reject Vendor Application</h3>
        <p class="text-gray-600 mb-4">You are rejecting: <span id="vendorName" class="font-semibold"></span></p>
        
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Reason for rejection *</label>
                <textarea name="reason" rows="4" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                          placeholder="Explain why this application is being rejected..."></textarea>
                <p class="text-sm text-gray-500 mt-1">This will be sent to the vendor</p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeRejectModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Confirm Reject
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showRejectModal(vendorId, vendorName) {
        document.getElementById('vendorName').textContent = vendorName;
        document.getElementById('rejectForm').action = `/admin/vendors/${vendorId}/reject`;
        document.getElementById('rejectModal').classList.remove('hidden');
    }
    
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.getElementById('rejectForm').reset();
    }
    
    // Close modal when clicking outside
    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRejectModal();
        }
    });
</script>
@endsection