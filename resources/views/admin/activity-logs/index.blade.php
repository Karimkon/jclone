@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Activity Logs</h1>
            <p class="text-gray-600">System activity and user actions</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
            <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Activity
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Event
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Time
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($logs as $log)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="mr-3">
                                        <i class="{{ $log->icon ?? 'fas fa-circle' }} {{ $log->color ?? 'text-gray-500' }}"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $log->description }}</div>
                                        @if($log->subject)
                                        <div class="text-sm text-gray-500">
                                            Related to: {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->causer)
                                <div class="text-sm text-gray-900">{{ $log->causer->name }}</div>
                                <div class="text-sm text-gray-500">{{ $log->causer_type }}</div>
                                @else
                                <span class="text-sm text-gray-500">System</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $log->event == 'order_created' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $log->event == 'vendor_approved' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $log->event == 'vendor_rejected' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $log->event == 'order_cancelled' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ !in_array($log->event, ['order_created', 'vendor_approved', 'vendor_rejected', 'order_cancelled']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ str_replace('_', ' ', $log->event) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                                <div class="text-xs text-gray-400">
                                    {{ \App\Services\ActivityLogger::timeAgo($log->created_at) }}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-history text-4xl text-gray-300 mb-3"></i>
                                <p class="text-lg">No activity logs found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection