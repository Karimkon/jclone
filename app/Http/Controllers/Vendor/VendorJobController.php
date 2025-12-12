<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Models\JobApplication;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VendorJobController extends Controller
{
    protected function getVendor()
    {
        return auth()->user()->vendorProfile;
    }

    /**
     * List vendor's job listings
     */
    public function index(Request $request)
    {
        $vendor = $this->getVendor();

        $query = JobListing::where('vendor_profile_id', $vendor->id)
            ->withCount('applications');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $jobs = $query->latest()->paginate(20);

        $stats = [
            'total' => JobListing::where('vendor_profile_id', $vendor->id)->count(),
            'active' => JobListing::where('vendor_profile_id', $vendor->id)->active()->count(),
            'total_applications' => JobApplication::whereHas('job', fn($q) => $q->where('vendor_profile_id', $vendor->id))->count(),
            'pending_applications' => JobApplication::whereHas('job', fn($q) => $q->where('vendor_profile_id', $vendor->id))->pending()->count(),
        ];

        return view('vendor.jobs.index', compact('jobs', 'stats'));
    }

    /**
     * Show create job form
     */
    public function create()
    {
        $categories = ServiceCategory::active()->forJobs()->orderBy('name')->get();
        $vendor = $this->getVendor();

        return view('vendor.jobs.create', compact('categories', 'vendor'));
    }

    /**
     * Store new job listing
     */
    public function store(Request $request)
    {
        $vendor = $this->getVendor();

        $request->validate([
            'title' => 'required|string|max:255',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'description' => 'required|string|max:20000',
            'job_type' => 'required|in:full_time,part_time,contract,freelance,internship,temporary',
            'experience_level' => 'required|in:entry,junior,mid,senior,expert',
            'location' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'is_remote' => 'boolean',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'salary_period' => 'required|in:hourly,daily,weekly,monthly,yearly,project',
            'salary_negotiable' => 'boolean',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'benefits' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'application_method' => 'required|in:email,phone,whatsapp,in_app',
            'deadline' => 'nullable|date|after:today',
            'vacancies' => 'required|integer|min:1',
            'is_urgent' => 'boolean',
        ]);

        // Convert text areas to arrays (split by new line)
        $requirements = $request->requirements ? array_filter(array_map('trim', explode("\n", $request->requirements))) : null;
        $responsibilities = $request->responsibilities ? array_filter(array_map('trim', explode("\n", $request->responsibilities))) : null;
        $benefits = $request->benefits ? array_filter(array_map('trim', explode("\n", $request->benefits))) : null;

        $job = JobListing::create([
            'vendor_profile_id' => $vendor->id,
            'service_category_id' => $request->service_category_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(6),
            'description' => $request->description,
            'job_type' => $request->job_type,
            'experience_level' => $request->experience_level,
            'location' => $request->location,
            'city' => $request->city,
            'is_remote' => $request->boolean('is_remote'),
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'salary_period' => $request->salary_period,
            'salary_negotiable' => $request->boolean('salary_negotiable', true),
            'requirements' => $requirements,
            'responsibilities' => $responsibilities,
            'benefits' => $benefits,
            'contact_email' => $request->contact_email ?? auth()->user()->email,
            'contact_phone' => $request->contact_phone ?? $vendor->phone,
            'application_method' => $request->application_method,
            'deadline' => $request->deadline,
            'vacancies' => $request->vacancies,
            'is_urgent' => $request->boolean('is_urgent'),
            'status' => 'active',
        ]);

        return redirect()->route('vendor.jobs.show', $job)
            ->with('success', 'Job listing created successfully!');
    }

    /**
     * Show single job listing with applications
     */
    public function show($id)
    {
        $vendor = $this->getVendor();
        $job = JobListing::where('vendor_profile_id', $vendor->id)
            ->withCount('applications')
            ->findOrFail($id);

        $applications = $job->applications()
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('vendor.jobs.show', compact('job', 'applications'));
    }

    /**
     * Show edit job form
     */
    public function edit($id)
    {
        $vendor = $this->getVendor();
        $job = JobListing::where('vendor_profile_id', $vendor->id)->findOrFail($id);
        $categories = ServiceCategory::active()->forJobs()->orderBy('name')->get();

        return view('vendor.jobs.edit', compact('job', 'categories'));
    }

    /**
     * Update job listing
     */
    public function update(Request $request, $id)
    {
        $vendor = $this->getVendor();
        $job = JobListing::where('vendor_profile_id', $vendor->id)->findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'description' => 'required|string|max:20000',
            'job_type' => 'required|in:full_time,part_time,contract,freelance,internship,temporary',
            'experience_level' => 'required|in:entry,junior,mid,senior,expert',
            'location' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'is_remote' => 'boolean',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'salary_period' => 'required|in:hourly,daily,weekly,monthly,yearly,project',
            'salary_negotiable' => 'boolean',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'benefits' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'application_method' => 'required|in:email,phone,whatsapp,in_app',
            'deadline' => 'nullable|date',
            'vacancies' => 'required|integer|min:1',
            'is_urgent' => 'boolean',
            'status' => 'required|in:draft,active,paused,closed,filled',
        ]);

        $requirements = $request->requirements ? array_filter(array_map('trim', explode("\n", $request->requirements))) : null;
        $responsibilities = $request->responsibilities ? array_filter(array_map('trim', explode("\n", $request->responsibilities))) : null;
        $benefits = $request->benefits ? array_filter(array_map('trim', explode("\n", $request->benefits))) : null;

        $job->update([
            'service_category_id' => $request->service_category_id,
            'title' => $request->title,
            'description' => $request->description,
            'job_type' => $request->job_type,
            'experience_level' => $request->experience_level,
            'location' => $request->location,
            'city' => $request->city,
            'is_remote' => $request->boolean('is_remote'),
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'salary_period' => $request->salary_period,
            'salary_negotiable' => $request->boolean('salary_negotiable', true),
            'requirements' => $requirements,
            'responsibilities' => $responsibilities,
            'benefits' => $benefits,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'application_method' => $request->application_method,
            'deadline' => $request->deadline,
            'vacancies' => $request->vacancies,
            'is_urgent' => $request->boolean('is_urgent'),
            'status' => $request->status,
        ]);

        return redirect()->route('vendor.jobs.show', $job)
            ->with('success', 'Job listing updated successfully!');
    }

    /**
     * Delete job listing
     */
    public function destroy($id)
    {
        $vendor = $this->getVendor();
        $job = JobListing::where('vendor_profile_id', $vendor->id)->findOrFail($id);
        $job->delete();

        return redirect()->route('vendor.jobs.index')
            ->with('success', 'Job listing deleted.');
    }

    /**
     * Toggle job status (active/paused)
     */
    public function toggleStatus($id)
    {
        $vendor = $this->getVendor();
        $job = JobListing::where('vendor_profile_id', $vendor->id)->findOrFail($id);
        
        $job->status = $job->status === 'active' ? 'paused' : 'active';
        $job->save();

        return back()->with('success', 'Job status updated.');
    }

    // ==================
    // APPLICATIONS
    // ==================

    /**
     * View single application
     */
    public function showApplication($jobId, $applicationId)
    {
        $vendor = $this->getVendor();
        $job = JobListing::where('vendor_profile_id', $vendor->id)->findOrFail($jobId);
        $application = $job->applications()->with('user')->findOrFail($applicationId);

        // Mark as reviewed if pending
        if ($application->status === 'pending') {
            $application->update(['status' => 'reviewed', 'reviewed_at' => now()]);
        }

        return view('vendor.jobs.application', compact('job', 'application'));
    }

    /**
     * Update application status
     */
    public function updateApplicationStatus(Request $request, $jobId, $applicationId)
    {
        $vendor = $this->getVendor();
        $job = JobListing::where('vendor_profile_id', $vendor->id)->findOrFail($jobId);
        $application = $job->applications()->findOrFail($applicationId);

        $request->validate([
            'status' => 'required|in:reviewed,shortlisted,interviewed,offered,hired,rejected',
            'notes' => 'nullable|string|max:2000',
        ]);

        $application->update([
            'status' => $request->status,
            'notes' => $request->notes,
            'reviewed_at' => $application->reviewed_at ?? now(),
        ]);

        return back()->with('success', 'Application status updated.');
    }

    /**
     * Download CV
     */
    public function downloadCV($jobId, $applicationId)
    {
        $vendor = $this->getVendor();
        $job = JobListing::where('vendor_profile_id', $vendor->id)->findOrFail($jobId);
        $application = $job->applications()->findOrFail($applicationId);

        if (!$application->cv_path) {
            return back()->with('error', 'No CV uploaded.');
        }

        return Storage::disk('public')->download($application->cv_path, "CV_{$application->applicant_name}.pdf");
    }
}