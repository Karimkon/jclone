@extends('layouts.vendor')

@section('title', 'Application Details - Vendor Dashboard')
@section('page_title', 'Application Details')

@section('content')
<div class="mb-6">
    <a href="{{ route('vendor.jobs.show', ['id' => $job->id]) }}" class="text-indigo-600 hover:underline">
        <i class="fas fa-arrow-left mr-2"></i> Back to {{ $job->title }}
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Applicant Info -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-indigo-600 font-bold text-2xl">
                        {{ strtoupper(substr($application->applicant_name, 0, 1)) }}
                    </span>
                </div>
                
                <div class="flex-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $application->applicant_name }}</h1>
                            <p class="text-gray-500">Applied for: {{ $job->title }}</p>
                        </div>
                        
                        @php
                            $statusColors = [
                                'pending' => 'bg-orange-100 text-orange-700',
                                'reviewed' => 'bg-blue-100 text-blue-700',
                                'shortlisted' => 'bg-purple-100 text-purple-700',
                                'interviewed' => 'bg-indigo-100 text-indigo-700',
                                'offered' => 'bg-teal-100 text-teal-700',
                                'hired' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <span class="px-4 py-2 text-sm font-medium rounded-full {{ $statusColors[$application->status] ?? 'bg-gray-100' }}">
                            {{ $application->status_label }}
                        </span>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="flex flex-wrap gap-4 mt-4">
                        <a href="mailto:{{ $application->applicant_email }}" class="flex items-center gap-2 text-gray-600 hover:text-indigo-600">
                            <i class="fas fa-envelope"></i>
                            <span>{{ $application->applicant_email }}</span>
                        </a>
                        @if($application->applicant_phone)
                        <a href="tel:{{ $application->applicant_phone }}" class="flex items-center gap-2 text-gray-600 hover:text-indigo-600">
                            <i class="fas fa-phone"></i>
                            <span>{{ $application->applicant_phone }}</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Application Details -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Application Details</h2>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <span class="text-sm text-gray-500 block">Applied On</span>
                    <span class="font-medium text-gray-800">{{ $application->created_at->format('M d, Y \a\t h:i A') }}</span>
                </div>
                @if($application->expected_salary)
                <div>
                    <span class="text-sm text-gray-500 block">Expected Salary</span>
                    <span class="font-medium text-gray-800">UGX {{ number_format($application->expected_salary) }}</span>
                </div>
                @endif
                @if($application->reviewed_at)
                <div>
                    <span class="text-sm text-gray-500 block">Reviewed On</span>
                    <span class="font-medium text-gray-800">{{ $application->reviewed_at->format('M d, Y') }}</span>
                </div>
                @endif
                @if($application->availability)
                <div>
                    <span class="text-sm text-gray-500 block">Availability</span>
                    <span class="font-medium text-gray-800">{{ $application->availability }}</span>
                </div>
                @endif
            </div>
            
            <!-- Cover Letter -->
            @if($application->cover_letter)
            <div class="border-t pt-4">
                <h3 class="font-medium text-gray-800 mb-2">Cover Letter</h3>
                <div class="bg-gray-50 rounded-lg p-4 text-gray-600">
                    {!! nl2br(e($application->cover_letter)) !!}
                </div>
            </div>
            @endif
        </div>
        
        <!-- CV/Resume -->
        @if($application->cv_path)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Resume / CV</h2>
            
            <div class="flex items-center justify-between bg-gray-50 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-pdf text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">{{ basename($application->cv_path) }}</p>
                        <p class="text-sm text-gray-500">Uploaded {{ $application->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                
                <a href="{{ route('vendor.jobs.applications.cv', ['jobId' => $job->id, 'applicationId' => $application->id]) }}" 
                   class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-download mr-2"></i> Download CV
                </a>
            </div>
        </div>
        @endif
        
        <!-- Notes -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Internal Notes</h2>
            
            <form action="{{ route('vendor.jobs.applications.status', ['jobId' => $job->id, 'applicationId' => $application->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="{{ $application->status }}">
                <textarea name="notes" rows="3" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                          placeholder="Add private notes about this applicant...">{{ $application->notes }}</textarea>
                <button type="submit" class="mt-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                    Save Notes
                </button>
            </form>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Update Status -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Update Status</h3>
            
            <form action="{{ route('vendor.jobs.applications.status', ['jobId' => $job->id, 'applicationId' => $application->id]) }}" method="POST">
                @csrf
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent mb-3">
                    <option value="pending" {{ $application->status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="reviewed" {{ $application->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                    <option value="shortlisted" {{ $application->status === 'shortlisted' ? 'selected' : '' }}>Shortlisted</option>
                    <option value="interviewed" {{ $application->status === 'interviewed' ? 'selected' : '' }}>Interviewed</option>
                    <option value="offered" {{ $application->status === 'offered' ? 'selected' : '' }}>Offered</option>
                    <option value="hired" {{ $application->status === 'hired' ? 'selected' : '' }}>Hired</option>
                    <option value="rejected" {{ $application->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Update Status
                </button>
            </form>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Quick Actions</h3>
            
            <div class="space-y-2">
                <a href="mailto:{{ $application->applicant_email }}?subject=Re: Your application for {{ $job->title }}" 
                   class="w-full block text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                    <i class="fas fa-envelope mr-2"></i> Send Email
                </a>
                
                @if($application->applicant_phone)
                <a href="tel:{{ $application->applicant_phone }}" 
                   class="w-full block text-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                    <i class="fas fa-phone mr-2"></i> Call Applicant
                </a>
                
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $application->applicant_phone) }}?text={{ urlencode('Hello ' . $application->applicant_name . ', regarding your application for ' . $job->title . '...') }}" 
                   target="_blank"
                   class="w-full block text-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm">
                    <i class="fab fa-whatsapp mr-2"></i> WhatsApp
                </a>
                @endif
                
                @if(!in_array($application->status, ['hired', 'rejected']))
                <form action="{{ route('vendor.jobs.applications.status', ['jobId' => $job->id, 'applicationId' => $application->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="hired">
                    <button type="submit" class="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm">
                        <i class="fas fa-check-circle mr-2"></i> Mark as Hired
                    </button>
                </form>
                
                <form action="{{ route('vendor.jobs.applications.status', ['jobId' => $job->id, 'applicationId' => $application->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit" class="w-full px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm">
                        <i class="fas fa-times-circle mr-2"></i> Reject Application
                    </button>
                </form>
                @endif
            </div>
        </div>
        
        <!-- Job Info -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Job Details</h3>
            
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-gray-500 block">Position</span>
                    <a href="{{ route('vendor.jobs.show', ['id' => $job->id]) }}" class="font-medium text-indigo-600 hover:underline">
                        {{ $job->title }}
                    </a>
                </div>
                <div>
                    <span class="text-gray-500 block">Salary Range</span>
                    <span class="font-medium text-gray-800">{{ $job->formatted_salary }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">Location</span>
                    <span class="font-medium text-gray-800">{{ $job->city }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">Total Applications</span>
                    <span class="font-medium text-gray-800">{{ $job->applications_count }}</span>
                </div>
            </div>
        </div>
        
        <!-- Timeline -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Timeline</h3>
            
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-paper-plane text-green-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">Application Submitted</p>
                        <p class="text-xs text-gray-500">{{ $application->created_at->format('M d, Y \a\t h:i A') }}</p>
                    </div>
                </div>
                
                @if($application->reviewed_at)
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-eye text-blue-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">Reviewed</p>
                        <p class="text-xs text-gray-500">{{ $application->reviewed_at->format('M d, Y \a\t h:i A') }}</p>
                    </div>
                </div>
                @endif
                
                @if($application->status === 'hired')
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">Hired</p>
                        <p class="text-xs text-gray-500">{{ $application->updated_at->format('M d, Y') }}</p>
                    </div>
                </div>
                @elseif($application->status === 'rejected')
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-times-circle text-red-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">Rejected</p>
                        <p class="text-xs text-gray-500">{{ $application->updated_at->format('M d, Y') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection