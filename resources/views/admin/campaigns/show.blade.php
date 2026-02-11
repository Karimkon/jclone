@extends('layouts.admin')

@section('title', $campaign->title . ' - Campaign')
@section('page-title', 'Campaign Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $campaign->title }}</h2>
                <div class="flex flex-wrap items-center gap-3 mt-2">
                    @php
                        $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-800',
                            'scheduled' => 'bg-yellow-100 text-yellow-800',
                            'sending' => 'bg-blue-100 text-blue-800',
                            'sent' => 'bg-green-100 text-green-800',
                            'failed' => 'bg-red-100 text-red-800',
                            'cancelled' => 'bg-gray-100 text-gray-600',
                        ];
                    @endphp
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$campaign->status] ?? '' }}">
                        {{ ucfirst($campaign->status) }}
                    </span>
                    <span class="text-sm text-gray-500">
                        @if($campaign->type === 'email')
                            <i class="fas fa-envelope text-blue-500"></i> Email
                        @else
                            <i class="fas fa-sms text-purple-500"></i> SMS
                        @endif
                    </span>
                    <span class="text-sm text-gray-500">
                        <i class="fas fa-users"></i> {{ ucfirst($campaign->audience) }}
                    </span>
                    @if($campaign->creator)
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-user"></i> {{ $campaign->creator->name }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @if(in_array($campaign->status, ['draft', 'scheduled']))
                    <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                @endif
                @if(in_array($campaign->status, ['scheduled', 'sending']))
                    <form action="{{ route('admin.campaigns.cancel', $campaign) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to cancel this campaign?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                    </form>
                @endif
                <form action="{{ route('admin.campaigns.duplicate', $campaign) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">
                        <i class="fas fa-copy mr-1"></i> Duplicate
                    </button>
                </form>
                <a href="{{ route('admin.campaigns.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Progress & Stats -->
    @if($campaign->total_recipients > 0)
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-sm text-gray-500">Total Recipients</p>
            <p class="text-2xl font-bold text-gray-900">{{ $campaign->total_recipients }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-sm text-gray-500">Sent</p>
            <p class="text-2xl font-bold text-green-600">{{ $recipientStats['sent'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-sm text-gray-500">Failed</p>
            <p class="text-2xl font-bold text-red-600">{{ $recipientStats['failed'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-orange-600">{{ $recipientStats['pending'] }}</p>
        </div>
    </div>

    <!-- Progress Bar -->
    @if(in_array($campaign->status, ['sending', 'sent', 'failed']))
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Progress</span>
            <span class="text-sm text-gray-500">{{ $campaign->getProgressPercentage() }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="h-3 rounded-full {{ $campaign->status === 'failed' ? 'bg-red-500' : 'bg-green-500' }} transition-all"
                 style="width: {{ $campaign->getProgressPercentage() }}%"></div>
        </div>
    </div>
    @endif
    @endif

    <!-- Timing Info -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-3">Timeline</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Created:</span>
                <span class="text-gray-900 ml-1">{{ $campaign->created_at->format('M d, Y H:i') }}</span>
            </div>
            @if($campaign->scheduled_at)
            <div>
                <span class="text-gray-500">Scheduled:</span>
                <span class="text-gray-900 ml-1">{{ $campaign->scheduled_at->format('M d, Y H:i') }}</span>
            </div>
            @endif
            @if($campaign->started_at)
            <div>
                <span class="text-gray-500">Started:</span>
                <span class="text-gray-900 ml-1">{{ $campaign->started_at->format('M d, Y H:i') }}</span>
            </div>
            @endif
            @if($campaign->completed_at)
            <div>
                <span class="text-gray-500">Completed:</span>
                <span class="text-gray-900 ml-1">{{ $campaign->completed_at->format('M d, Y H:i') }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Message Content -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-3">
            @if($campaign->type === 'email')
                Email Content
                @if($campaign->subject)
                    <span class="text-sm font-normal text-gray-500 ml-2">Subject: {{ $campaign->subject }}</span>
                @endif
            @else
                SMS Content
            @endif
        </h3>
        @if($campaign->type === 'email')
            <div class="border rounded-lg p-4">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $campaign->message }}</p>
            </div>
        @else
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $campaign->message }}</p>
                <p class="text-xs text-gray-500 mt-2">{{ strlen($campaign->message) }}/160 characters</p>
            </div>
        @endif
    </div>

    <!-- Recipients Table -->
    @if($campaign->recipients->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900">Recipients (showing last 100)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Channel</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Sent At</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($campaign->recipients as $recipient)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm">
                            @if($recipient->channel === 'email')
                                {{ $recipient->email }}
                            @else
                                {{ $recipient->phone }}
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ ucfirst($recipient->channel) }}</td>
                        <td class="px-6 py-3">
                            @php
                                $rStatusColors = [
                                    'pending' => 'bg-gray-100 text-gray-800',
                                    'sent' => 'bg-green-100 text-green-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'skipped' => 'bg-yellow-100 text-yellow-800',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $rStatusColors[$recipient->status] ?? '' }}">
                                {{ ucfirst($recipient->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500">
                            {{ $recipient->sent_at?->format('M d, H:i') ?? '-' }}
                        </td>
                        <td class="px-6 py-3 text-sm text-red-500">
                            {{ Str::limit($recipient->error_message, 50) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
