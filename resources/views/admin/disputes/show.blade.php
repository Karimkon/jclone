@extends('layouts.admin')

@section('title', 'Dispute #' . $dispute->id)
@section('page-title', 'Dispute Details')
@section('page-description', 'View and manage dispute #' . $dispute->id)

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.disputes.index') }}" class="inline-flex items-center text-gray-600 hover:text-indigo-600 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Disputes
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Dispute Info Card -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Dispute #{{ $dispute->id }}</h2>
                    @php
                        $statusColors = [
                            'open' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                            'in_review' => 'bg-purple-100 text-purple-800 border-purple-300',
                            'resolved' => 'bg-green-100 text-green-800 border-green-300',
                            'escalated' => 'bg-red-100 text-red-800 border-red-300',
                        ];
                    @endphp
                    <span class="px-4 py-2 text-sm font-bold rounded-full border {{ $statusColors[$dispute->status] ?? 'bg-gray-100 text-gray-800 border-gray-300' }}">
                        {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Created</p>
                        <p class="font-medium">{{ $dispute->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Last Updated</p>
                        <p class="font-medium">{{ $dispute->updated_at->diffForHumans() }}</p>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-100">
                    <h3 class="font-semibold text-gray-800 mb-3">Reason for Dispute</h3>
                    <p class="text-gray-700 bg-gray-50 p-4 rounded-lg">
                        {{ $dispute->reason ?? 'No reason provided' }}
                    </p>
                </div>

                @if($dispute->description)
                <div class="mt-4">
                    <h3 class="font-semibold text-gray-800 mb-3">Description</h3>
                    <p class="text-gray-700 bg-gray-50 p-4 rounded-lg whitespace-pre-line">
                        {{ $dispute->description }}
                    </p>
                </div>
                @endif
            </div>

            <!-- Order Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Order Information</h2>

                @if($dispute->order)
                <div class="flex items-center justify-between p-4 bg-indigo-50 rounded-lg mb-4">
                    <div>
                        <p class="font-bold text-indigo-800">Order #{{ $dispute->order->order_number }}</p>
                        <p class="text-sm text-indigo-600">{{ $dispute->order->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-indigo-800">UGX {{ number_format($dispute->order->total, 0) }}</p>
                        <span class="text-xs px-2 py-1 bg-indigo-200 text-indigo-800 rounded-full">
                            {{ ucfirst($dispute->order->status) }}
                        </span>
                    </div>
                </div>

                <!-- Order Items -->
                @if($dispute->order->items && $dispute->order->items->count() > 0)
                <div class="space-y-3">
                    @foreach($dispute->order->items as $item)
                    <div class="flex items-center gap-4 p-3 border border-gray-200 rounded-lg">
                        @if($item->listing && $item->listing->images && $item->listing->images->first())
                        <img src="{{ asset('storage/' . $item->listing->images->first()->path) }}"
                             alt="{{ $item->listing->title ?? 'Product' }}"
                             class="w-16 h-16 object-cover rounded-lg">
                        @else
                        <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                        @endif
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">{{ $item->listing->title ?? 'Product Unavailable' }}</p>
                            <p class="text-sm text-gray-500">Qty: {{ $item->quantity }} x UGX {{ number_format($item->price, 0) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-800">UGX {{ number_format($item->quantity * $item->price, 0) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
                @else
                <p class="text-gray-500">Order information not available.</p>
                @endif
            </div>

            <!-- Comments Section -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Comments & Activity</h2>

                @php
                    $comments = $dispute->meta['comments'] ?? [];
                @endphp

                @if(count($comments) > 0)
                <div class="space-y-4 mb-6">
                    @foreach($comments as $comment)
                    <div class="p-4 {{ $comment['is_internal'] ?? false ? 'bg-yellow-50 border-yellow-200' : 'bg-gray-50 border-gray-200' }} border rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-gray-800">{{ $comment['user_name'] ?? 'Unknown' }}</span>
                            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($comment['created_at'])->diffForHumans() }}</span>
                        </div>
                        <p class="text-gray-700">{{ $comment['comment'] }}</p>
                        @if($comment['is_internal'] ?? false)
                        <span class="mt-2 inline-block text-xs bg-yellow-200 text-yellow-800 px-2 py-0.5 rounded">Internal Note</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 mb-4">No comments yet.</p>
                @endif

                <!-- Add Comment Form -->
                <form action="{{ route('admin.disputes.comment', $dispute) }}" method="POST" class="border-t border-gray-200 pt-4">
                    @csrf
                    <div class="mb-3">
                        <textarea name="comment" rows="3" required
                                  placeholder="Add a comment..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            Internal note (not visible to customer)
                        </label>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-paper-plane mr-2"></i> Add Comment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Parties Involved -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-4">Parties Involved</h3>

                <!-- Raised By -->
                <div class="mb-4 pb-4 border-b border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Raised By</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-indigo-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $dispute->raisedBy->name ?? 'Unknown' }}</p>
                            <p class="text-sm text-gray-500">{{ $dispute->raisedBy->email ?? '' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Buyer -->
                @if($dispute->order && $dispute->order->buyer)
                <div class="mb-4 pb-4 border-b border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Buyer</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-shopping-bag text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $dispute->order->buyer->name }}</p>
                            <p class="text-sm text-gray-500">{{ $dispute->order->buyer->email }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Vendor -->
                @if($dispute->order && $dispute->order->vendorProfile)
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Vendor</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-store text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $dispute->order->vendorProfile->business_name }}</p>
                            <p class="text-sm text-gray-500">{{ $dispute->order->vendorProfile->user->email ?? '' }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Update Status -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-4">Update Status</h3>

                <form action="{{ route('admin.disputes.updateStatus', $dispute) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="open" {{ $dispute->status == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_review" {{ $dispute->status == 'in_review' ? 'selected' : '' }}>In Review</option>
                            <option value="resolved" {{ $dispute->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="escalated" {{ $dispute->status == 'escalated' ? 'selected' : '' }}>Escalated</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Resolution Type</label>
                        <select name="resolution_type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select resolution type...</option>
                            <option value="refund_buyer">Full Refund to Buyer</option>
                            <option value="pay_vendor">Pay Vendor</option>
                            <option value="partial_refund">Partial Refund</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Refund Amount (if applicable)</label>
                        <input type="number" name="refund_amount" min="0" step="0.01"
                               placeholder="0.00"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Resolution Notes</label>
                        <textarea name="resolution" rows="3"
                                  placeholder="Describe the resolution..."
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                    </div>

                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium">
                        <i class="fas fa-save mr-2"></i> Update Dispute
                    </button>
                </form>
            </div>

            <!-- Request Evidence -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-4">Request Evidence</h3>

                <form action="{{ route('admin.disputes.requestEvidence', $dispute) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <textarea name="message" rows="3" required
                                  placeholder="Describe what evidence you need..."
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deadline (days)</label>
                        <input type="number" name="deadline_days" min="1" max="30" value="7" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <button type="submit" class="w-full px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition font-medium">
                        <i class="fas fa-file-upload mr-2"></i> Request Evidence
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
