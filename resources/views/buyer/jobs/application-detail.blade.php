@extends('layouts.buyer')

@section('title', 'Application Details - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8 pb-20 sm:pb-8">
    <!-- Back Button -->
    <a href="{{ route('buyer.applications.index') }}" class="inline-flex items-center text-gray-600 hover:text-primary mb-4">
        <i class="fas fa-arrow-left mr-2"></i> Back to Applications
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">
            <!-- Job Details -->
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $application->job->title ?? 'Job Position' }}</h1>
                        <p class="text-gray-600 mt-1">
                            <i class="fas fa-building mr-1"></i>
                            {{ $application->job->vendor->business_name ?? 'Company' }}
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium self-start
                        @if($application->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($application->status == 'reviewed') bg-blue-100 text-blue-800
                        @elseif($application->status == 'shortlisted') bg-purple-100 text-purple-800
                        @elseif($application->status == 'accepted') bg-green-100 text-green-800
                        @elseif($application->status == 'rejected') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($application->status) }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    @if($application->job->category)
                    <div>
                        <span class="text-gray-500">Category</span>
                        <p class="font-medium">{{ $application->job->category->name }}</p>
                    </div>
                    @endif
                    @if($application->job->job_type)
                    <div>
                        <span class="text-gray-500">Job Type</span>
                        <p class="font-medium">{{ ucfirst($application->job->job_type) }}</p>
                    </div>
                    @endif
                    @if($application->job->location)
                    <div>
                        <span class="text-gray-500">Location</span>
                        <p class="font-medium">{{ $application->job->location }}</p>
                    </div>
                    @endif
                    @if($application->job->salary_range)
                    <div>
                        <span class="text-gray-500">Salary</span>
                        <p class="font-medium">{{ $application->job->salary_range }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Your Application -->
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Your Application</h2>

                @if($application->cover_letter)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Cover Letter</h3>
                    <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 whitespace-pre-wrap">{{ $application->cover_letter }}</div>
                </div>
                @endif

                @if($application->cv_path)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">CV/Resume</h3>
                    <a href="{{ asset('storage/' . $application->cv_path) }}" target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                        <i class="fas fa-file-pdf mr-2 text-red-500"></i> View CV
                    </a>
                </div>
                @endif

                @if($application->expected_salary)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Expected Salary</h3>
                    <p class="text-gray-900">UGX {{ number_format($application->expected_salary, 0) }}</p>
                </div>
                @endif

                @if($application->available_from)
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Available From</h3>
                    <p class="text-gray-900">{{ \Carbon\Carbon::parse($application->available_from)->format('M d, Y') }}</p>
                </div>
                @endif
            </div>

            <!-- Feedback (if any) -->
            @if($application->feedback)
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Employer Feedback</h2>
                <div class="bg-blue-50 rounded-lg p-4 text-sm text-blue-800">
                    {{ $application->feedback }}
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-4 sm:space-y-6">
            <!-- Application Status -->
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <h3 class="font-bold text-gray-900 mb-4">Application Timeline</h3>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-paper-plane text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-sm">Applied</p>
                            <p class="text-xs text-gray-500">{{ $application->created_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                    </div>

                    @if($application->reviewed_at)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-eye text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-sm">Reviewed</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($application->reviewed_at)->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                    @endif

                    @if($application->status == 'accepted')
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-sm text-green-600">Accepted!</p>
                            <p class="text-xs text-gray-500">Congratulations!</p>
                        </div>
                    </div>
                    @elseif($application->status == 'rejected')
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-times text-red-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-sm text-red-600">Not Selected</p>
                            <p class="text-xs text-gray-500">Keep trying!</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @if(in_array($application->status, ['pending', 'reviewed']))
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <h3 class="font-bold text-gray-900 mb-4">Actions</h3>
                <form action="{{ route('buyer.applications.withdraw', $application->id) }}" method="POST"
                      onsubmit="return confirm('Are you sure you want to withdraw this application?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 text-sm font-medium">
                        <i class="fas fa-times mr-2"></i> Withdraw Application
                    </button>
                </form>
            </div>
            @endif

            <!-- Job Link -->
            @if($application->job)
            <div class="bg-blue-50 rounded-xl p-4">
                <p class="text-sm text-blue-800 mb-3">Want to see the full job posting?</p>
                <a href="{{ route('jobs.show', $application->job) }}"
                   class="inline-flex items-center text-sm text-blue-600 hover:underline font-medium">
                    <i class="fas fa-external-link-alt mr-2"></i> View Job Listing
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
