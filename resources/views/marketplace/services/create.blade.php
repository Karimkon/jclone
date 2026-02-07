@extends('layouts.app')

@section('title', $job->title . ' - BebaMart Jobs')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('jobs.index') }}" class="hover:text-emerald-600">Jobs</a>
            @if($job->category)
            <span class="mx-2">/</span>
            <a href="{{ route('jobs.index', ['category' => $job->category->id]) }}" class="hover:text-emerald-600">{{ $job->category->name }}</a>
            @endif
            <span class="mx-2">/</span>
            <span class="text-gray-800">{{ $job->title }}</span>
        </nav>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Header -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                            @if($job->vendor->logo)
                                <img src="{{ $job->vendor->logo }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-building text-gray-400 text-2xl"></i>
                            @endif
                        </div>
                        
                        <div class="flex-1">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900">{{ $job->title }}</h1>
                                    <p class="text-gray-600 mt-1">{{ $job->vendor->business_name }}</p>
                                </div>
                                
                                <div class="flex gap-2 flex-shrink-0">
                                    @if($job->is_urgent)
                                    <span class="px-3 py-1 bg-red-100 text-red-700 text-sm font-medium rounded-full">Urgent</span>
                                    @endif
                                    @if($job->is_featured)
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-sm font-medium rounded-full">Featured</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-4 mt-4 text-sm text-gray-500">
                                <span><i class="fas fa-map-marker-alt mr-1"></i> {{ $job->city }}@if($job->location), {{ $job->location }}@endif</span>
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">{{ $job->job_type_label }}</span>
                                @if($job->is_remote)
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs"><i class="fas fa-globe mr-1"></i> Remote OK</span>
                                @endif
                                <span><i class="fas fa-clock mr-1"></i> Posted {{ $job->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Key Info -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t">
                        <div>
                            <span class="text-sm text-gray-500 block">Salary</span>
                            <span class="font-semibold text-emerald-600">{{ $job->formatted_salary }}</span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500 block">Experience</span>
                            <span class="font-medium text-gray-800">{{ ucfirst($job->experience_level) }} Level</span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500 block">Vacancies</span>
                            <span class="font-medium text-gray-800">{{ $job->vacancies }} position(s)</span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500 block">Deadline</span>
                            <span class="font-medium {{ $job->is_expired ? 'text-red-600' : 'text-gray-800' }}">
                                @if($job->deadline)
                                    {{ $job->deadline->format('M d, Y') }}
                                    @if($job->is_expired) (Expired) @endif
                                @else
                                    Open until filled
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Job Description</h2>
                    <div class="prose max-w-none text-gray-600">
                        {!! nl2br(e($job->description)) !!}
                    </div>
                </div>
                
                <!-- Requirements -->
                @if($job->requirements && count($job->requirements) > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Requirements</h2>
                    <ul class="space-y-2">
                        @foreach($job->requirements as $req)
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-emerald-500 mt-1"></i>
                            <span class="text-gray-600">{{ $req }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                <!-- Responsibilities -->
                @if($job->responsibilities && count($job->responsibilities) > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Responsibilities</h2>
                    <ul class="space-y-2">
                        @foreach($job->responsibilities as $resp)
                        <li class="flex items-start gap-2">
                            <i class="fas fa-arrow-right text-emerald-500 mt-1"></i>
                            <span class="text-gray-600">{{ $resp }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                <!-- Benefits -->
                @if($job->benefits && count($job->benefits) > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Benefits</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($job->benefits as $benefit)
                        <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-full text-sm">
                            <i class="fas fa-gift mr-1"></i> {{ $benefit }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Apply Card -->
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-4">
                    @if($hasApplied)
                        <div class="text-center py-4">
                            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-check text-emerald-600 text-2xl"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-2">Application Submitted!</h3>
                            <p class="text-sm text-gray-600 mb-4">You applied {{ $application->created_at->diffForHumans() }}</p>
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">
                                Status: {{ $application->status_label }}
                            </span>
                            <a href="{{ route('buyer.applications.show', $application) }}" class="block mt-4 text-emerald-600 hover:underline text-sm">
                                View your application →
                            </a>
                        </div>
                    @elseif($job->is_expired)
                        <div class="text-center py-4">
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-times text-red-600 text-2xl"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-2">Application Closed</h3>
                            <p class="text-sm text-gray-600">This job listing has expired</p>
                        </div>
                    @elseif($job->status !== 'active')
                        <div class="text-center py-4">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-pause text-gray-400 text-2xl"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-2">Not Accepting Applications</h3>
                            <p class="text-sm text-gray-600">This position is currently {{ $job->status }}</p>
                        </div>
                    @else
                        <h3 class="font-semibold text-gray-800 mb-4">Apply for this Job</h3>
                        
                        @auth
                        <form action="{{ route('jobs.apply', $job->slug) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                                    <input type="text" name="applicant_name" value="{{ auth()->user()->name }}" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                    <input type="email" name="applicant_email" value="{{ auth()->user()->email }}" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <input type="text" name="applicant_phone" value="{{ auth()->user()->phone }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">CV/Resume (PDF, DOC)</label>
                                    <input type="file" name="cv" accept=".pdf,.doc,.docx"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Expected Salary (UGX)</label>
                                    <input type="number" name="expected_salary" placeholder="e.g. 1000000"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cover Letter</label>
                                    <textarea name="cover_letter" rows="4"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                              placeholder="Tell us why you're interested in this role..."></textarea>
                                </div>
                                
                                <button type="submit" class="w-full px-4 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium">
                                    <i class="fas fa-paper-plane mr-2"></i> Submit Application
                                </button>
                            </div>
                        </form>
                        @else
                        <p class="text-gray-600 text-sm mb-4">Please login to apply for this job</p>
                        <a href="{{ route('login') }}" class="w-full block text-center px-4 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                            Login to Apply
                        </a>
                        @endauth
                    @endif
                </div>
                
                <!-- Company Info -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">About the Company</h3>
                    
                    <div class="flex items-center gap-4 pb-4 border-b">
                        <div class="w-14 h-14 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden">
                            @if($job->vendor->logo)
                                <img src="{{ $job->vendor->logo }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-building text-gray-400 text-xl"></i>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-800">{{ $job->vendor->business_name }}</h4>
                            <p class="text-sm text-gray-500">{{ $job->vendor->business_address ?? $job->city }}</p>
                        </div>
                    </div>
                    
                    @if($job->vendor->description)
                    <p class="text-sm text-gray-600 mt-4">{{ Str::limit($job->vendor->description, 200) }}</p>
                    @endif
                    
                    <div class="mt-4 space-y-2">
                        @if($job->contact_email)
                        <a href="mailto:{{ $job->contact_email }}" class="flex items-center gap-2 text-sm text-gray-600 hover:text-emerald-600">
                            <i class="fas fa-envelope w-5"></i> {{ $job->contact_email }}
                        </a>
                        @endif
                        @if($job->contact_phone)
                        <a href="tel:{{ $job->contact_phone }}" class="flex items-center gap-2 text-sm text-gray-600 hover:text-emerald-600">
                            <i class="fas fa-phone w-5"></i> {{ $job->contact_phone }}
                        </a>
                        @endif
                    </div>
                </div>
                
                <!-- Share -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Share this Job</h3>
                    <div class="flex gap-2">
                        <a href="https://wa.me/?text={{ urlencode($job->title . ' - ' . url()->current()) }}" target="_blank"
                           class="flex-1 text-center px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($job->title) }}" target="_blank"
                           class="flex-1 text-center px-3 py-2 bg-blue-400 text-white rounded-lg hover:bg-blue-500">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank"
                           class="flex-1 text-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <button onclick="navigator.clipboard.writeText('{{ url()->current() }}'); alert('Link copied!');"
                                class="flex-1 text-center px-3 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Similar Jobs -->
        @if($similarJobs->count() > 0)
        <div class="mt-12">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Similar Jobs</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($similarJobs as $sj)
                <a href="{{ route('jobs.show', $sj->slug) }}" class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden">
                            @if($sj->vendor->logo)
                            <img src="{{ $sj->vendor->logo }}" class="w-full h-full object-cover">
                            @else
                            <i class="fas fa-building text-gray-400"></i>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-medium text-gray-800 truncate">{{ $sj->title }}</h3>
                            <p class="text-sm text-gray-500 truncate">{{ $sj->vendor->business_name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i> {{ $sj->city }}</span>
                        <span class="text-emerald-600 font-medium">{{ $sj->formatted_salary }}</span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection