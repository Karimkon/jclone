@extends('layouts.app')

@section('title', 'Browse Jobs - BebaMart')

@section('content')
<!-- Hero -->
<section class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">Find Your Next Opportunity</h1>
            <p class="text-lg text-white/80 mb-6">Browse {{ $jobs->total() }} job listings from top employers</p>
            
            <!-- Search -->
            <form action="{{ route('jobs.index') }}" method="GET" class="max-w-2xl mx-auto">
                <div class="flex flex-col md:flex-row gap-2">
                    <div class="flex-1 relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="q" value="{{ request('q') }}" 
                               placeholder="Job title or keyword" 
                               class="w-full pl-12 pr-4 py-3 rounded-lg text-gray-800 focus:ring-2 focus:ring-white/50 outline-none">
                    </div>
                    <select name="city" class="px-4 py-3 rounded-lg text-gray-800 bg-white">
                        <option value="">All Cities</option>
                        @foreach($cities as $city)
                        <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-white text-emerald-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100">
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Filters Sidebar -->
        <div class="lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-lg shadow-sm p-4 sticky top-4">
                <h3 class="font-semibold text-gray-800 mb-4">Filters</h3>
                
                <form action="{{ route('jobs.index') }}" method="GET">
                    @if(request('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    
                    <!-- Category -->
                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-700 block mb-2">Category</label>
                        <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }} ({{ $cat->jobs_count }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Job Type -->
                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-700 block mb-2">Job Type</label>
                        <div class="space-y-2">
                            @foreach(['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'freelance' => 'Freelance', 'internship' => 'Internship'] as $value => $label)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" name="type" value="{{ $value }}" {{ request('type') == $value ? 'checked' : '' }} onchange="this.form.submit()"
                                       class="text-emerald-600 focus:ring-emerald-500">
                                {{ $label }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Remote -->
                    <div class="mb-4">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="remote" value="1" {{ request('remote') ? 'checked' : '' }} onchange="this.form.submit()"
                                   class="text-emerald-600 focus:ring-emerald-500 rounded">
                            Remote Jobs Only
                        </label>
                    </div>
                    
                    @if(request()->hasAny(['category', 'type', 'remote', 'city']))
                    <a href="{{ route('jobs.index') }}" class="text-sm text-red-600 hover:underline">
                        <i class="fas fa-times mr-1"></i> Clear Filters
                    </a>
                    @endif
                </form>
            </div>
        </div>
        
        <!-- Jobs List -->
        <div class="flex-1">
            <!-- Sort & Count -->
            <div class="flex items-center justify-between mb-4">
                <p class="text-gray-600">{{ $jobs->total() }} jobs found</p>
                <select onchange="window.location.href=this.value" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>Newest First</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'salary_high']) }}" {{ request('sort') == 'salary_high' ? 'selected' : '' }}>Highest Salary</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'urgent']) }}" {{ request('sort') == 'urgent' ? 'selected' : '' }}>Urgent First</option>
                </select>
            </div>
            
            <!-- Job Cards -->
            @if($jobs->count() > 0)
            <div class="space-y-4">
                @foreach($jobs as $job)
                <div class="bg-white rounded-lg shadow-sm p-5 hover:shadow-md transition {{ $job->is_featured ? 'border-l-4 border-emerald-500' : '' }}">
                    <div class="flex items-start gap-4">
                        <!-- Company Logo -->
                        <div class="w-14 h-14 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                            @if($job->vendor->logo)
                                <img src="{{ $job->vendor->logo }}" alt="{{ $job->vendor->business_name }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-building text-gray-400 text-xl"></i>
                            @endif
                        </div>
                        
                        <!-- Job Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <a href="{{ route('jobs.show', $job->slug) }}" class="text-lg font-semibold text-gray-900 hover:text-emerald-600 line-clamp-1">
                                        {{ $job->title }}
                                    </a>
                                    <p class="text-sm text-gray-600">{{ $job->vendor->business_name }}</p>
                                </div>
                                
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if($job->is_urgent)
                                    <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full">Urgent</span>
                                    @endif
                                    @if($job->is_featured)
                                    <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-xs font-medium rounded-full">Featured</span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Meta -->
                            <div class="flex flex-wrap items-center gap-3 mt-2 text-sm text-gray-500">
                                <span><i class="fas fa-map-marker-alt mr-1"></i> {{ $job->city }}</span>
                                <span><i class="fas fa-briefcase mr-1"></i> {{ $job->job_type_label }}</span>
                                @if($job->is_remote)
                                <span class="text-green-600"><i class="fas fa-globe mr-1"></i> Remote</span>
                                @endif
                                <span><i class="fas fa-clock mr-1"></i> {{ $job->created_at->diffForHumans() }}</span>
                            </div>
                            
                            <!-- Description Preview -->
                            <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ Str::limit(strip_tags($job->description), 150) }}</p>
                            
                            <!-- Footer -->
                            <div class="flex items-center justify-between mt-3 pt-3 border-t">
                                <span class="font-semibold text-emerald-600">{{ $job->formatted_salary }}</span>
                                <a href="{{ route('jobs.show', $job->slug) }}" class="text-sm text-emerald-600 hover:underline font-medium">
                                    View Details →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $jobs->links() }}
            </div>
            @else
            <div class="bg-white rounded-lg shadow-sm text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-briefcase text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Jobs Found</h3>
                <p class="text-gray-500">Try adjusting your search or filters</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
