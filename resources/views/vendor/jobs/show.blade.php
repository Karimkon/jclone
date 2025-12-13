@extends('layouts.vendor')

@section('title', $job->title . ' - Vendor Dashboard')
@section('page_title', 'Job Details')

@section('content')
<div class="mb-6">
    <a href="{{ route('vendor.jobs.index') }}" class="text-indigo-600 hover:underline">
        <i class="fas fa-arrow-left mr-2"></i> Back to Job Listings
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Job Details -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $job->title }}</h1>
                    <div class="flex flex-wrap items-center gap-3 mt-2 text-sm text-gray-500">
                        <span><i class="fas fa-map-marker-alt mr-1"></i> {{ $job->city }}</span>
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">{{ $job->job_type_label }}</span>
                        @if($job->is_remote)
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs"><i class="fas fa-globe mr-1"></i> Remote</span>
                        @endif
                    </div>
                </div>
                
                <div class="flex gap-2">
                    @php
                        $statusColors = [
                            'active' => 'bg-green-100 text-green-700',
                            'paused' => 'bg-yellow-100 text-yellow-700',
                            'closed' => 'bg-gray-100 text-gray-700',
                            'filled' => 'bg-blue-100 text-blue-700',
                        ];
                    @endphp
                    <span class="px-3 py-1 text-sm rounded-full {{ $statusColors[$job->status] ?? 'bg-gray-100' }}">
                        {{ ucfirst($job->status) }}
                    </span>
                    @if($job->is_urgent)
                    <span class="px-3 py-1 bg-red-100 text-red-700 text-sm rounded-full">Urgent</span>
                    @endif
                </div>
            </div>
            
            <!-- Key Info -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t">
                <div>
                    <span class="text-sm text-gray-500 block">Salary</span>
                    <span class="font-semibold text-green-600">{{ $job->formatted_salary }}</span>
                </div>
                <div>
                    <span class="text-sm text-gray-500 block">Experience</span>
                    <span class="font-medium text-gray-800">{{ ucfirst($job->experience_level) }}</span>
                </div>
                <div>
                    <span class="text-sm text-gray-500 block">Vacancies</span>
                    <span class="font-medium text-gray-800">{{ $job->vacancies }}</span>
                </div>
                <div>
                    <span class="text-sm text-gray-500 block">Deadline</span>
                    <span class="font-medium {{ $job->is_expired ? 'text-red-600' : 'text-gray-800' }}">
                        {{ $job->deadline ? $job->deadline->format('M d, Y') : 'Open' }}
                    </span>
                </div>
            </div>
            
            <!-- Description -->
            <div class="mt-6 pt-6 border-t">
                <h3 class="font-semibold text-gray-800 mb-2">Description</h3>
                <div class="text-gray-600 prose max-w-none">
                    {!! nl2br(e($job->description)) !!}
                </div>
            </div>
            
            <!-- Requirements -->
            @if($job->requirements && count($job->requirements) > 0)
            <div class="mt-6 pt-6 border-t">
                <h3 class="font-semibold text-gray-800 mb-2">Requirements</h3>
                <ul class="space-y-1">
                    @foreach($job->requirements as $req)
                    <li class="flex items-start gap-2 text-gray-600">
                        <i class="fas fa-check text-green-500 mt-1"></i>
                        <span>{{ $req }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <!-- Actions -->
            <div class="flex items-center gap-3 mt-6 pt-6 border-t">
                <a href="{{ route('vendor.jobs.edit', $job) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-edit mr-2"></i> Edit Job
                </a>
                <form action="{{ route('vendor.jobs.toggle', $job) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 {{ $job->status === 'active' ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600' }} text-white rounded-lg">
                        <i class="fas {{ $job->status === 'active' ? 'fa-pause' : 'fa-play' }} mr-2"></i>
                        {{ $job->status === 'active' ? 'Pause' : 'Activate' }}
                    </button>
                </form>
                <a href="{{ route('jobs.show', $job->slug) }}" target="_blank" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-external-link-alt mr-2"></i> View Public Page
                </a>
            </div>
        </div>
        
        <!-- Applications -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Applications ({{ $job->applications_count }})
                    </h2>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('vendor.jobs.show', ['id' => $job->id, 'status' => 'pending']) }}" 
                           class="px-3 py-1 text-sm rounded-full {{ request('status') == 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Pending
                        </a>
                        <a href="{{ route('vendor.jobs.show', ['id' => $job->id, 'status' => 'shortlisted']) }}" 
                           class="px-3 py-1 text-sm rounded-full {{ request('status') == 'shortlisted' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Shortlisted
                        </a>
                        <a href="{{ route('vendor.jobs.show', ['id' => $job->id]) }}" 
                           class="px-3 py-1 text-sm rounded-full {{ !request('status') ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            All
                        </a>
                    </div>
                </div>
            </div>
            
            @if($applications->count() > 0)
            <div class="divide-y">
                @foreach($applications as $application)
                <div class="p-6 hover:bg-gray-50">
                    <div class="flex items-start gap-4">
                        <!-- Avatar -->
                        <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-indigo-600 font-semibold text-lg">
                                {{ strtoupper(substr($application->applicant_name, 0, 1)) }}
                            </span>
                        </div>
                        
                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $application->applicant_name }}</h3>
                                    <div class="flex flex-wrap items-center gap-3 mt-1 text-sm text-gray-500">
                                        <span><i class="fas fa-envelope mr-1"></i> {{ $application->applicant_email }}</span>
                                        @if($application->applicant_phone)
                                        <span><i class="fas fa-phone mr-1"></i> {{ $application->applicant_phone }}</span>
                                        @endif
                                    </div>
                                </div>
                                
                                @php
                                    $appStatusColors = [
                                        'pending' => 'bg-orange-100 text-orange-700',
                                        'reviewed' => 'bg-blue-100 text-blue-700',
                                        'shortlisted' => 'bg-purple-100 text-purple-700',
                                        'interviewed' => 'bg-indigo-100 text-indigo-700',
                                        'offered' => 'bg-teal-100 text-teal-700',
                                        'hired' => 'bg-green-100 text-green-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <span class="px-3 py-1 text-sm rounded-full {{ $appStatusColors[$application->status] ?? 'bg-gray-100' }}">
                                    {{ $application->status_label }}
                                </span>
                            </div>
                            
                            @if($application->expected_salary)
                            <div class="mt-2 text-sm">
                                <span class="text-gray-500">Expected:</span>
                                <span class="font-medium text-gray-800">UGX {{ number_format($application->expected_salary) }}</span>
                            </div>
                            @endif
                            
                            @if($application->cover_letter)
                            <div class="mt-2 text-sm text-gray-600 line-clamp-2">
                                {{ $application->cover_letter }}
                            </div>
                            @endif
                            
                            <!-- Actions -->
                            <div class="flex items-center gap-2 mt-3">
                                <a href="{{ route('vendor.jobs.applications.show', ['jobId' => $job->id, 'applicationId' => $application->id]) }}" 
                                   class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                                    View Details
                                </a>
                                
                                @if($application->cv_path)
                                <a href="{{ route('vendor.jobs.applications.cv', ['jobId' => $job->id, 'applicationId' => $application->id]) }}" 
                                   class="px-3 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                                    <i class="fas fa-download mr-1"></i> CV
                                </a>
                                @endif
                                
                                @if($application->status === 'pending')
                                <form action="{{ route('vendor.jobs.applications.status', ['jobId' => $job->id, 'applicationId' => $application->id]) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="shortlisted">
                                    <button type="submit" class="px-3 py-1.5 bg-purple-100 text-purple-700 text-sm rounded-lg hover:bg-purple-200">
                                        <i class="fas fa-star mr-1"></i> Shortlist
                                    </button>
                                </form>
                                <form action="{{ route('vendor.jobs.applications.status', ['jobId' => $job->id, 'applicationId' => $application->id]) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="px-3 py-1.5 bg-red-100 text-red-700 text-sm rounded-lg hover:bg-red-200">
                                        <i class="fas fa-times mr-1"></i> Reject
                                    </button>
                                </form>
                                @endif
                            </div>
                            
                            <div class="mt-2 text-xs text-gray-400">
                                Applied {{ $application->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="p-4 border-t">
                {{ $applications->links() }}
            </div>
            @else
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Applications Yet</h3>
                <p class="text-gray-500">Applications will appear here once candidates apply</p>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Stats -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Application Stats</h3>
            
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Total Applications</span>
                    <span class="font-semibold text-gray-900">{{ $job->applications_count }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Pending Review</span>
                    <span class="font-semibold text-orange-600">{{ $job->applications()->where('status', 'pending')->count() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Shortlisted</span>
                    <span class="font-semibold text-purple-600">{{ $job->applications()->where('status', 'shortlisted')->count() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Interviewed</span>
                    <span class="font-semibold text-indigo-600">{{ $job->applications()->where('status', 'interviewed')->count() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Hired</span>
                    <span class="font-semibold text-green-600">{{ $job->applications()->where('status', 'hired')->count() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Rejected</span>
                    <span class="font-semibold text-red-600">{{ $job->applications()->where('status', 'rejected')->count() }}</span>
                </div>
            </div>
        </div>
        
        <!-- Job Info -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Job Information</h3>
            
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Posted</span>
                    <span class="text-gray-800">{{ $job->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Views</span>
                    <span class="text-gray-800">{{ $job->views_count ?? 0 }}</span>
                </div>
                @if($job->category)
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Category</span>
                    <span class="text-gray-800">{{ $job->category->name }}</span>
                </div>
                @endif
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Application Method</span>
                    <span class="text-gray-800">{{ ucfirst(str_replace('_', ' ', $job->application_method)) }}</span>
                </div>
            </div>
        </div>
        
        <!-- Contact Info -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Contact Details</h3>
            
            <div class="space-y-2 text-sm">
                @if($job->contact_email)
                <div class="flex items-center gap-2 text-gray-600">
                    <i class="fas fa-envelope w-5"></i>
                    <span>{{ $job->contact_email }}</span>
                </div>
                @endif
                @if($job->contact_phone)
                <div class="flex items-center gap-2 text-gray-600">
                    <i class="fas fa-phone w-5"></i>
                    <span>{{ $job->contact_phone }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Quick Actions</h3>
            
            <div class="space-y-2">
                <button onclick="copyJobLink()" class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">
                    <i class="fas fa-link mr-2"></i> Copy Job Link
                </button>
                <a href="https://wa.me/?text={{ urlencode($job->title . ' - Apply now: ' . route('jobs.show', $job->slug)) }}" 
                   target="_blank"
                   class="w-full block text-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm">
                    <i class="fab fa-whatsapp mr-2"></i> Share on WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function copyJobLink() {
    navigator.clipboard.writeText('{{ route('jobs.show', $job->slug) }}');
    alert('Job link copied to clipboard!');
}
</script>
@endsection