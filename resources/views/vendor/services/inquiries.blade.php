@extends('layouts.vendor')

@section('title', 'Service Inquiries - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Service Inquiries</h1>
            <p class="text-gray-600">Customer inquiries about your services</p>
        </div>
        <a href="{{ route('vendor.services.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-list mr-2"></i> My Services
        </a>
    </div>

    @if($inquiries->count() > 0)
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inquiry</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($inquiries as $inquiry)
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $inquiry->service->title ?? 'Service Deleted' }}</div>
                        <div class="text-sm text-gray-500 truncate max-w-xs">{{ $inquiry->message }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">{{ $inquiry->user->name ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-500">{{ $inquiry->user->email ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $inquiry->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            {{ $inquiry->status === 'new' ? 'bg-blue-100 text-blue-800' : 
                               ($inquiry->status === 'contacted' ? 'bg-yellow-100 text-yellow-800' : 
                               ($inquiry->status === 'converted' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                            {{ ucfirst($inquiry->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <form method="POST" action="{{ route('vendor.services.update-inquiry-status', $inquiry->id) }}" class="inline">
                            @csrf
                            <select name="status" onchange="this.form.submit()" class="text-sm border border-gray-300 rounded px-2 py-1">
                                <option value="new" {{ $inquiry->status == 'new' ? 'selected' : '' }}>New</option>
                                <option value="contacted" {{ $inquiry->status == 'contacted' ? 'selected' : '' }}>Contacted</option>
                                <option value="converted" {{ $inquiry->status == 'converted' ? 'selected' : '' }}>Converted</option>
                                <option value="closed" {{ $inquiry->status == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="bg-white px-4 py-3 border-t border-gray-200">
            {{ $inquiries->links() }}
        </div>
    </div>
    @else
    <div class="text-center py-12">
        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <i class="fas fa-question-circle text-gray-400 text-3xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No inquiries yet</h3>
        <p class="text-gray-500">Customer inquiries will appear here.</p>
    </div>
    @endif
</div>
@endsection