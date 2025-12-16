@extends('layouts.admin')

@section('title', 'Contact Messages - Admin Dashboard')
@section('page-title', 'Contact Messages')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Messages</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-envelope text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">New Messages</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['new'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-bell text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Read Messages</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['read'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Responded</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['responded'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-reply text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-xl shadow-sm p-5">
        <form action="{{ route('admin.contact-messages.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Statuses</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                        <option value="responded" {{ request('status') == 'responded' ? 'selected' : '' }}>Responded</option>
                    </select>
                </div>
                
                <!-- Contact Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Type</label>
                    <select name="contact_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Types</option>
                        <option value="buyer" {{ request('contact_type') == 'buyer' ? 'selected' : '' }}>Buyer</option>
                        <option value="vendor" {{ request('contact_type') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                        <option value="support" {{ request('contact_type') == 'support' ? 'selected' : '' }}>Support</option>
                        <option value="partner" {{ request('contact_type') == 'partner' ? 'selected' : '' }}>Partner</option>
                        <option value="other" {{ request('contact_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by name, email, or subject..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
            </div>
            
            <div class="flex justify-between items-center">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
                
                <a href="{{ route('admin.contact-messages.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-redo mr-2"></i> Reset Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Messages Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-900">Contact Messages</h2>
            <div class="flex items-center space-x-2">
                <!-- Bulk Actions -->
                <form id="bulkActionForm" action="{{ route('admin.contact-messages.bulk-actions') }}" method="POST" class="flex items-center space-x-2">
                    @csrf
                    @method('POST')
                    <select name="action" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                        <option value="">Bulk Actions</option>
                        <option value="mark_read">Mark as Read</option>
                        <option value="mark_responded">Mark as Responded</option>
                        <option value="delete">Delete</option>
                    </select>
                    <input type="hidden" name="messages" id="bulkMessages">
                    <button type="submit" onclick="return confirm('Are you sure?')" 
                            class="px-3 py-1.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm">
                        Apply
                    </button>
                </form>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name/Email</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($messages as $message)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <input type="checkbox" name="message_ids[]" value="{{ $message->id }}" 
                                   class="message-checkbox rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div>
                                <div class="font-medium text-gray-900">{{ $message->name }}</div>
                                <div class="text-sm text-gray-500">{{ $message->email }}</div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm font-medium text-gray-900 truncate max-w-xs" title="{{ $message->subject }}">
                                {{ $message->subject }}
                            </div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($message->contact_type == 'buyer') bg-blue-100 text-blue-800
                                @elseif($message->contact_type == 'vendor') bg-purple-100 text-purple-800
                                @elseif($message->contact_type == 'support') bg-yellow-100 text-yellow-800
                                @elseif($message->contact_type == 'partner') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($message->contact_type) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($message->status == 'new') bg-red-100 text-red-800
                                @elseif($message->status == 'read') bg-blue-100 text-blue-800
                                @elseif($message->status == 'responded') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($message->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $message->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-sm">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.contact-messages.show', $message->id) }}" 
                                   class="px-3 py-1 bg-primary-100 text-primary-700 rounded hover:bg-primary-200">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                <form action="{{ route('admin.contact-messages.destroy', $message->id) }}" 
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Delete this message?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">
                                        <i class="fas fa-trash mr-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-gray-500">
                            <i class="fas fa-envelope-open-text text-4xl text-gray-300 mb-2"></i>
                            <p class="text-lg">No contact messages found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($messages->hasPages())
        <div class="px-5 py-4 border-t">
            {{ $messages->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bulk selection
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.message-checkbox');
    const bulkMessagesInput = document.getElementById('bulkMessages');
    
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkMessages();
    });
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkMessages);
    });
    
    function updateBulkMessages() {
        const selectedIds = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        bulkMessagesInput.value = JSON.stringify(selectedIds);
    }
    
    // Update bulk form action
    document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
        if (!bulkMessagesInput.value || bulkMessagesInput.value === '[]') {
            e.preventDefault();
            alert('Please select at least one message');
            return false;
        }
        return confirm('Are you sure about this bulk action?');
    });
});
</script>
@endsection