@extends('layouts.vendor')

@section('title', 'Job Listings - Vendor Dashboard')
@section('page_title', 'Job Listings')

@section('content')
<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Jobs</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                <i class="fas fa-briefcase text-indigo-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active Jobs</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Applications</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['total_applications'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-blue-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pending Review</p>
                <p class="text-2xl font-bold text-orange-600">{{ $stats['pending_applications'] }}</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <i class="fas fa-clock text-orange-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Actions Bar -->
<div class="bg-white rounded-lg p-4 shadow-sm mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('vendor.jobs.index') }}" 
               class="px-3 py-1.5 rounded-lg text-sm {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                All
            </a>
            <a href="{{ route('vendor.jobs.index', ['status' => 'active']) }}" 
               class="px-3 py-1.5 rounded-lg text-sm {{ request('status') == 'active' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Active
            </a>
            <a href="{{ route('vendor.jobs.index', ['status' => 'paused']) }}" 
               class="px-3 py-1.5 rounded-lg text-sm {{ request('status') == 'paused' ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Paused
            </a>
            <a href="{{ route('vendor.jobs.index', ['status' => 'closed']) }}" 
               class="px-3 py-1.5 rounded-lg text-sm {{ request('status') == 'closed' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Closed
            </a>
        </div>
        
        <a href="{{ route('vendor.jobs.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 inline-flex items-center">
            <i class="fas fa-plus mr-2"></i> Post New Job
        </a>
    </div>
</div>

<!-- Jobs List -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    @if($jobs->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applications</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deadline</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($jobs as $job)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-4">
                        <a href="{{ route('vendor.jobs.show', $job) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                            {{ $job->title }}
                        </a>
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-map-marker-alt mr-1"></i> {{ $job->city }}
                            @if($job->is_remote) <span class="ml-2 text-green-600"><i class="fas fa-globe mr-1"></i> Remote</span> @endif
                        </div>
                        <div class="text-sm text-gray-500 mt-1">{{ $job->formatted_salary }}</div>
                    </td>
                    <td class="px-4 py-4">
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">{{ $job->job_type_label }}</span>
                    </td>
                    <td class="px-4 py-4">
                        <a href="{{ route('vendor.jobs.show', $job) }}" class="text-indigo-600 hover:underline font-medium">
                            {{ $job->applications_count }} applications
                        </a>
                    </td>
                    <td class="px-4 py-4">
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-700',
                                'paused' => 'bg-yellow-100 text-yellow-700',
                                'closed' => 'bg-gray-100 text-gray-700',
                                'filled' => 'bg-blue-100 text-blue-700',
                                'draft' => 'bg-gray-100 text-gray-500',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$job->status] ?? 'bg-gray-100' }}">
                            {{ ucfirst($job->status) }}
                        </span>
                        @if($job->is_urgent)
                        <span class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded-full ml-1">Urgent</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-500">
                        @if($job->deadline)
                            {{ $job->deadline->format('M d, Y') }}
                            @if($job->is_expired)
                            <span class="text-red-500 text-xs block">Expired</span>
                            @endif
                        @else
                            No deadline
                        @endif
                    </td>
                    <td class="px-4 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('vendor.jobs.show', $job) }}" class="p-2 text-gray-500 hover:text-indigo-600" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('vendor.jobs.edit', $job) }}" class="p-2 text-gray-500 hover:text-blue-600" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('vendor.jobs.toggle', $job) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 text-gray-500 hover:text-yellow-600" title="{{ $job->status === 'active' ? 'Pause' : 'Activate' }}">
                                    <i class="fas {{ $job->status === 'active' ? 'fa-pause' : 'fa-play' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('vendor.jobs.destroy', $job) }}" method="POST" class="inline" onsubmit="return confirm('Delete this job listing?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-gray-500 hover:text-red-600" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="p-4 border-t">
        {{ $jobs->links() }}
    </div>
    @else
    <div class="text-center py-12">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-briefcase text-gray-400 text-2xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Job Listings Yet</h3>
        <p class="text-gray-500 mb-4">Start posting jobs to find talented candidates</p>
        <a href="{{ route('vendor.jobs.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i> Post Your First Job
        </a>
    </div>
    @endif
</div>
@endsection
