@extends('layouts.vendor')

@section('title', 'Post New Job - Vendor Dashboard')
@section('page_title', 'Post New Job')

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('vendor.jobs.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Basic Info -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Job Details</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="e.g. Senior Sales Representative">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="service_category_id" id="jobCategorySelect" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('service_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Type *</label>
                    <select name="job_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="full_time" {{ old('job_type') == 'full_time' ? 'selected' : '' }}>Full Time</option>
                        <option value="part_time" {{ old('job_type') == 'part_time' ? 'selected' : '' }}>Part Time</option>
                        <option value="contract" {{ old('job_type') == 'contract' ? 'selected' : '' }}>Contract</option>
                        <option value="freelance" {{ old('job_type') == 'freelance' ? 'selected' : '' }}>Freelance</option>
                        <option value="internship" {{ old('job_type') == 'internship' ? 'selected' : '' }}>Internship</option>
                        <option value="temporary" {{ old('job_type') == 'temporary' ? 'selected' : '' }}>Temporary</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Experience Level *</label>
                    <select name="experience_level" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="entry" {{ old('experience_level') == 'entry' ? 'selected' : '' }}>Entry Level</option>
                        <option value="junior" {{ old('experience_level') == 'junior' ? 'selected' : '' }}>Junior (1-2 years)</option>
                        <option value="mid" {{ old('experience_level') == 'mid' ? 'selected' : '' }}>Mid Level (3-5 years)</option>
                        <option value="senior" {{ old('experience_level') == 'senior' ? 'selected' : '' }}>Senior (5+ years)</option>
                        <option value="expert" {{ old('experience_level') == 'expert' ? 'selected' : '' }}>Expert (10+ years)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vacancies *</label>
                    <input type="number" name="vacancies" value="{{ old('vacancies', 1) }}" min="1" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Description *</label>
                    <textarea name="description" rows="6" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="Describe the role, responsibilities, and what you're looking for...">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>
        
        <!-- Location -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Location</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                    <input type="text" name="city" value="{{ old('city', 'Kampala') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location/Area</label>
                    <input type="text" name="location" value="{{ old('location') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="e.g. Kololo, Industrial Area">
                </div>
                
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_remote" value="1" {{ old('is_remote') ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">This is a remote position (work from home)</span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Salary -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Salary</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum (UGX)</label>
                    <input type="number" name="salary_min" value="{{ old('salary_min') }}" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="e.g. 500000">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Maximum (UGX)</label>
                    <input type="number" name="salary_max" value="{{ old('salary_max') }}" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="e.g. 1000000">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Period *</label>
                    <select name="salary_period" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="monthly" {{ old('salary_period', 'monthly') == 'monthly' ? 'selected' : '' }}>Per Month</option>
                        <option value="hourly" {{ old('salary_period') == 'hourly' ? 'selected' : '' }}>Per Hour</option>
                        <option value="daily" {{ old('salary_period') == 'daily' ? 'selected' : '' }}>Per Day</option>
                        <option value="weekly" {{ old('salary_period') == 'weekly' ? 'selected' : '' }}>Per Week</option>
                        <option value="yearly" {{ old('salary_period') == 'yearly' ? 'selected' : '' }}>Per Year</option>
                        <option value="project" {{ old('salary_period') == 'project' ? 'selected' : '' }}>Per Project</option>
                    </select>
                </div>
                
                <div class="md:col-span-3">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="salary_negotiable" value="1" {{ old('salary_negotiable', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">Salary is negotiable</span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Requirements & Responsibilities -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Requirements & Responsibilities</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Requirements (one per line)</label>
                    <textarea name="requirements" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="Bachelor's degree in relevant field&#10;2+ years experience&#10;Strong communication skills">{{ old('requirements') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Enter each requirement on a new line</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Responsibilities (one per line)</label>
                    <textarea name="responsibilities" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="Manage client relationships&#10;Prepare sales reports&#10;Meet monthly targets">{{ old('responsibilities') }}</textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Benefits (one per line)</label>
                    <textarea name="benefits" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="Health insurance&#10;Transport allowance&#10;Performance bonus">{{ old('benefits') }}</textarea>
                </div>
            </div>
        </div>
        
        <!-- Application Settings -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Application Settings</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Application Method *</label>
                    <select name="application_method" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="in_app" {{ old('application_method', 'in_app') == 'in_app' ? 'selected' : '' }}>Apply on BebaMart (Recommended)</option>
                        <option value="email" {{ old('application_method') == 'email' ? 'selected' : '' }}>Apply via Email</option>
                        <option value="phone" {{ old('application_method') == 'phone' ? 'selected' : '' }}>Apply via Phone</option>
                        <option value="whatsapp" {{ old('application_method') == 'whatsapp' ? 'selected' : '' }}>Apply via WhatsApp</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Application Deadline</label>
                    <input type="date" name="deadline" value="{{ old('deadline') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', auth()->user()->email) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $vendor->phone) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_urgent" value="1" {{ old('is_urgent') ? 'checked' : '' }}
                               class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                        <span class="text-sm text-gray-700">Mark as Urgent Hiring (highlighted in search results)</span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Submit -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('vendor.jobs.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                <i class="fas fa-briefcase mr-2"></i> Post Job
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for job category
    $('#jobCategorySelect').select2({
        placeholder: "Type to search for a category...",
        allowClear: true,
        width: '100%',
        dropdownParent: $('body'), // Ensure dropdown appears above everything
        minimumResultsForSearch: 0 // Always show search box
    });
    
    // Debug: Check if Select2 is loaded
    if (!$.fn.select2) {
        console.error('Select2 is not loaded!');
    } else {
        console.log('Select2 is loaded, initializing...');
    }
    
    // Debug: Check if element exists
    if ($('#jobCategorySelect').length) {
        console.log('jobCategorySelect element found');
    } else {
        console.error('jobCategorySelect element NOT found!');
    }
});
</script>
@endpush