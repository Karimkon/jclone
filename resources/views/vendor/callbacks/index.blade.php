@extends('layouts.vendor')

@section('page_title', 'Callback Requests')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Callback Requests</h2>
                <p class="text-gray-600 mt-1">Manage customer callback requests</p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-orange-600">{{ $pendingCount }}</div>
                <div class="text-sm text-gray-500">Pending Callbacks</div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="flex border-b">
            <button class="px-6 py-4 font-medium border-b-2 border-indigo-600 text-indigo-600">
                All ({{ $callbacks->total() }})
            </button>
            <button class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700">
                Pending ({{ $pendingCount }})
            </button>
            <button class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700">
                Contacted
            </button>
            <button class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700">
                Completed
            </button>
        </div>
    </div>

    <!-- Callbacks List -->
    <div class="space-y-4">
        @forelse($callbacks as $callback)
        <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div class="flex gap-4 flex-1">
                    <!-- Product Image -->
                    <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                        @if($callback->listing->images->first())
                        <img src="{{ asset('storage/' . $callback->listing->images->first()->path) }}" 
                             alt="{{ $callback->listing->title }}"
                             class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <i class="fas fa-image text-gray-300 text-2xl"></i>
                        </div>
                        @endif
                    </div>

                    <!-- Details -->
                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h3 class="font-semibold text-gray-800 mb-1">{{ $callback->name }}</h3>
                                <div class="flex items-center gap-4 text-sm text-gray-600">
                                    <span><i class="fas fa-phone mr-1"></i>{{ $callback->phone }}</span>
                                    <span><i class="fas fa-clock mr-1"></i>{{ $callback->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                {{ $callback->status === 'pending' ? 'bg-orange-100 text-orange-700' : '' }}
                                {{ $callback->status === 'contacted' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $callback->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $callback->status === 'cancelled' ? 'bg-gray-100 text-gray-700' : '' }}">
                                {{ ucfirst($callback->status) }}
                            </span>
                        </div>

                        <div class="text-sm text-gray-600 mb-3">
                            <span class="font-medium">Product:</span> {{ $callback->listing->title }}
                        </div>

                        @if($callback->message)
                        <div class="bg-gray-50 rounded-lg p-3 mb-3">
                            <p class="text-sm text-gray-700">
                                <i class="fas fa-comment-alt text-gray-400 mr-2"></i>{{ $callback->message }}
                            </p>
                        </div>
                        @endif

                        @if($callback->vendor_notes)
                        <div class="bg-blue-50 rounded-lg p-3 mb-3">
                            <p class="text-xs text-blue-600 font-medium mb-1">Your Notes:</p>
                            <p class="text-sm text-blue-800">{{ $callback->vendor_notes }}</p>
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex gap-2 mt-4">
                          @if($callback->status === 'pending')
<form action="{{ route('vendor.callbacks.update-status', $callback) }}" method="POST" class="inline">
    @csrf
    <input type="hidden" name="status" value="contacted">
    <button type="submit" 
            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
        <i class="fas fa-phone mr-2"></i>Mark as Contacted
    </button>
</form>
@endif

                            <button onclick="addNotes({{ $callback->id }})" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                                <i class="fas fa-sticky-note mr-2"></i>Add Notes
                            </button>

                            <a href="tel:{{ $callback->phone }}" 
                               class="px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-medium hover:bg-green-600 transition">
                                <i class="fas fa-phone-alt mr-2"></i>Call Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-phone-slash text-gray-300 text-3xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">No Callback Requests</h3>
            <p class="text-gray-500">You haven't received any callback requests yet</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($callbacks->hasPages())
    <div class="mt-6">
        {{ $callbacks->links() }}
    </div>
    @endif
</div>

<!-- Notes Modal -->
<div id="notesModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60" onclick="closeNotesModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-xl font-bold mb-4">Add Notes</h3>
            <form id="notesForm">
                <input type="hidden" id="callback_id">
                <textarea id="vendor_notes" 
                          rows="4" 
                          class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                          placeholder="Enter your notes about this callback..."></textarea>
                <div class="flex gap-3 mt-4">
                    <button type="button" onclick="closeNotesModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg">
                        Save Notes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function updateCallbackStatus(callbackId, status) {
    try {
        const response = await fetch(`/vendor/callbacks/${callbackId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status })
        });

        const data = await response.json();

        if (data.success) {
            showToast('Status updated successfully', 'success');
            location.reload();
        }
    } catch (error) {
        showToast('Failed to update status', 'error');
    }
}

function addNotes(callbackId) {
    document.getElementById('callback_id').value = callbackId;
    document.getElementById('notesModal').classList.remove('hidden');
}

function closeNotesModal() {
    document.getElementById('notesModal').classList.add('hidden');
    document.getElementById('notesForm').reset();
}

document.getElementById('notesForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const callbackId = document.getElementById('callback_id').value;
    const notes = document.getElementById('vendor_notes').value;
    
    try {
        const response = await fetch(`/vendor/callbacks/${callbackId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                status: 'contacted',
                vendor_notes: notes 
            })
        });

        const data = await response.json();

        if (data.success) {
            showToast('Notes added successfully', 'success');
            closeNotesModal();
            location.reload();
        }
    } catch (error) {
        showToast('Failed to save notes', 'error');
    }
});

function showToast(message, type = 'info') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
    };
    
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg z-50`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
@endpush