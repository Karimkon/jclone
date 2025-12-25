@extends('layouts.buyer')

@section('title', 'My Job Applications - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8 pb-20 sm:pb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold">My Job Applications</h1>
            <p class="text-sm text-gray-600 mt-1">Track your job applications</p>
        </div>
        <a href="{{ route('jobs.index') }}" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
            <i class="fas fa-search mr-2"></i> Browse Jobs
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                <option value="shortlisted" {{ request('status') == 'shortlisted' ? 'selected' : '' }}>Shortlisted</option>
                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if(request('status'))
            <a href="{{ route('buyer.applications.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>
    </div>

    @if($applications->count() > 0)
    <!-- Mobile Card View -->
    <div class="sm:hidden space-y-4">
        @foreach($applications as $application)
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900">{{ Str::limit($application->job->title ?? 'Job', 40) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-building mr-1"></i>
                        {{ $application->job->vendor->business_name ?? 'Company' }}
                    </p>
                </div>
                <span class="px-2 py-1 rounded-full text-xs font-medium flex-shrink-0 ml-2
                    @if($application->status == 'pending') bg-yellow-100 text-yellow-800
                    @elseif($application->status == 'reviewed') bg-blue-100 text-blue-800
                    @elseif($application->status == 'shortlisted') bg-purple-100 text-purple-800
                    @elseif($application->status == 'accepted') bg-green-100 text-green-800
                    @elseif($application->status == 'rejected') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst($application->status) }}
                </span>
            </div>

            <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                <span><i class="fas fa-calendar mr-1"></i> {{ $application->created_at->format('M d, Y') }}</span>
                @if($application->job->category)
                <span><i class="fas fa-tag mr-1"></i> {{ $application->job->category->name }}</span>
                @endif
            </div>

            <div class="flex justify-between items-center pt-3 border-t">
                <a href="{{ route('buyer.applications.show', $application->id) }}"
                   class="text-primary text-sm font-medium">
                    <i class="fas fa-eye mr-1"></i> View Details
                </a>
                @if(in_array($application->status, ['pending', 'reviewed']))
                <form action="{{ route('buyer.applications.withdraw', $application->id) }}" method="POST"
                      onsubmit="return confirm('Are you sure you want to withdraw this application?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 text-sm">
                        <i class="fas fa-times mr-1"></i> Withdraw
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Desktop Table View -->
    <div class="hidden sm:block bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applied</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($applications as $application)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ Str::limit($application->job->title ?? 'Job', 40) }}</div>
                            @if($application->job->category)
                            <div class="text-xs text-gray-500">{{ $application->job->category->name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $application->job->vendor->business_name ?? 'Company' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $application->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                @if($application->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($application->status == 'reviewed') bg-blue-100 text-blue-800
                                @elseif($application->status == 'shortlisted') bg-purple-100 text-purple-800
                                @elseif($application->status == 'accepted') bg-green-100 text-green-800
                                @elseif($application->status == 'rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($application->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('buyer.applications.show', $application->id) }}"
                                   class="text-primary hover:text-indigo-700 text-sm">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                @if(in_array($application->status, ['pending', 'reviewed']))
                                <form action="{{ route('buyer.applications.withdraw', $application->id) }}" method="POST"
                                      onsubmit="return confirm('Withdraw this application?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                        <i class="fas fa-times mr-1"></i> Withdraw
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $applications->links() }}
        </div>
        @endif
    </div>

    <!-- Mobile Pagination -->
    @if($applications->hasPages())
    <div class="sm:hidden mt-4">
        {{ $applications->links() }}
    </div>
    @endif

    @else
    <!-- Empty State -->
    <div class="bg-white rounded-xl shadow-sm p-8 text-center">
        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
            <i class="fas fa-file-alt text-gray-400 text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">No applications yet</h3>
        <p class="text-gray-600 mb-6">Start applying to jobs to see your applications here</p>
        <a href="{{ route('jobs.index') }}"
           class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
            <i class="fas fa-search mr-2"></i> Browse Jobs
        </a>
    </div>
    @endif
</div>
@endsection
