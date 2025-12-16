@extends('layouts.admin')

@section('title', 'Contact Message Details - Admin Dashboard')
@section('page-title', 'Contact Message')

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary-600">
                <i class="fas fa-home mr-2"></i>
                Dashboard
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                <a href="{{ route('admin.contact-messages.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-primary-600 md:ml-2">
                    Contact Messages
                </a>
            </div>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Message Details</span>
            </div>
        </li>
    </ol>
</nav>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Message Details Card -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b bg-gradient-to-r from-blue-50 to-indigo-50">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Contact Message</h1>
                    <p class="text-sm text-gray-600">Submitted {{ $message->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-3 py-1 text-xs rounded-full 
                        @if($message->status == 'new') bg-red-100 text-red-800
                        @elseif($message->status == 'read') bg-blue-100 text-blue-800
                        @elseif($message->status == 'responded') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800 @endif font-medium">
                        {{ ucfirst($message->status) }}
                    </span>
                    <span class="px-3 py-1 text-xs rounded-full 
                        @if($message->contact_type == 'buyer') bg-blue-100 text-blue-800
                        @elseif($message->contact_type == 'vendor') bg-purple-100 text-purple-800
                        @elseif($message->contact_type == 'support') bg-yellow-100 text-yellow-800
                        @elseif($message->contact_type == 'partner') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800 @endif font-medium">
                        {{ ucfirst($message->contact_type) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Message Content -->
        <div class="p-6 space-y-6">
            <!-- Sender Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Sender</h3>
                    <p class="text-lg font-semibold text-gray-900">{{ $message->name }}</p>
                    <a href="mailto:{{ $message->email }}" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-envelope mr-1"></i> {{ $message->email }}
                    </a>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Subject</h3>
                    <p class="text-lg font-semibold text-gray-900">{{ $message->subject }}</p>
                </div>
            </div>

            <!-- Message Body -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Message</h3>
                <div class="prose max-w-none text-gray-800">
                    {!! nl2br(e($message->message)) !!}
                </div>
            </div>

            <!-- Metadata -->
            @if($message->meta)
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Additional Information</h3>
                <div class="space-y-2">
                    @foreach($message->meta as $key => $value)
                        @if(is_string($value))
                        <div class="flex">
                            <span class="text-sm font-medium text-gray-700 w-32">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                            <span class="text-sm text-gray-600 flex-1">{{ $value }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Admin Actions -->
            <div class="border-t pt-6">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Actions</h3>
                <div class="flex flex-wrap gap-3">
                    <!-- Update Status Form -->
                    <form action="{{ route('admin.contact-messages.update-status', $message->id) }}" method="POST" class="inline">
                        @csrf
                        <div class="flex items-center space-x-2">
                            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="new" {{ $message->status == 'new' ? 'selected' : '' }}>New</option>
                                <option value="read" {{ $message->status == 'read' ? 'selected' : '' }}>Read</option>
                                <option value="responded" {{ $message->status == 'responded' ? 'selected' : '' }}>Responded</option>
                                <option value="archived" {{ $message->status == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
                                Update Status
                            </button>
                        </div>
                    </form>

                    <!-- Delete Form -->
                    <form action="{{ route('admin.contact-messages.destroy', $message->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this message?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                            <i class="fas fa-trash mr-1"></i> Delete
                        </button>
                    </form>

                    <!-- Back Button -->
                    <a href="{{ route('admin.contact-messages.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Messages
                    </a>
                </div>
            </div>

            <!-- Response Section (if responded) -->
            @if($message->status == 'responded' && isset($message->meta['admin_response']))
            <div class="border-t pt-6">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Admin Response</h3>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="prose max-w-none text-gray-800">
                        {!! nl2br(e($message->meta['admin_response'])) !!}
                    </div>
                    @if(isset($message->meta['responded_at']))
                    <p class="text-xs text-gray-500 mt-2">
                        Responded: {{ \Carbon\Carbon::parse($message->meta['responded_at'])->format('M d, Y \a\t h:i A') }}
                    </p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection