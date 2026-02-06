<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Models\JobApplication;
use App\Models\VendorService;
use App\Models\ServiceRequest;
use App\Models\ServiceInquiry;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobsServicesController extends Controller
{
    // ==========================================
    // JOBS - PUBLIC BROWSING
    // ==========================================

    /**
     * Jobs listing page - WITH SUBSCRIPTION RANKING
     */
    public function jobs(Request $request)
    {
        $query = JobListing::active()
            ->notExpired()
            ->with(['vendor.activeSubscription.plan', 'category']);

        // Filters
        if ($request->filled('category')) {
            $query->where('service_category_id', $request->category);
        }
        if ($request->filled('type')) {
            $query->where('job_type', $request->type);
        }
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }
        if ($request->boolean('remote')) {
            $query->where('is_remote', true);
        }
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('vendor', function ($vq) use ($searchTerm) {
                      $vq->where('business_name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Sorting with subscription ranking as default
        $sort = $request->get('sort', 'recommended');

        if ($sort === 'recommended') {
            // Apply subscription-based ranking
            $allJobs = $query->get()->map(function($job) {
                $boost = ($job->vendor && method_exists($job->vendor, 'getBoostMultiplier'))
                    ? $job->vendor->getBoostMultiplier()
                    : 1.0;
                $job->boost_multiplier = $boost;
                $job->is_promoted = $boost > 1.0;
                return $job;
            });

            // Separate boosted and free, then interleave
            $boosted = $allJobs->filter(fn($j) => $j->is_promoted)->sortByDesc('boost_multiplier');
            $free = $allJobs->filter(fn($j) => !$j->is_promoted)->sortByDesc('created_at');

            $result = collect();
            $bIdx = 0; $fIdx = 0;
            $total = $boosted->count() + $free->count();
            for ($i = 0; $i < $total; $i++) {
                if ($i > 0 && $i % 3 === 0 && $fIdx < $free->count()) {
                    $result->push($free->values()[$fIdx++]);
                } elseif ($bIdx < $boosted->count()) {
                    $result->push($boosted->values()[$bIdx++]);
                } elseif ($fIdx < $free->count()) {
                    $result->push($free->values()[$fIdx++]);
                }
            }

            $page = $request->get('page', 1);
            $perPage = 20;
            $jobs = new \Illuminate\Pagination\LengthAwarePaginator(
                $result->forPage($page, $perPage),
                $result->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            match ($sort) {
                'salary_high' => $query->orderByDesc('salary_max'),
                'salary_low' => $query->orderBy('salary_min'),
                'urgent' => $query->orderByDesc('is_urgent')->orderByDesc('created_at'),
                'newest' => $query->orderByDesc('created_at'),
                default => $query->orderByDesc('is_featured')->orderByDesc('created_at'),
            };
            $jobs = $query->paginate(20)->withQueryString();
        }

        $categories = ServiceCategory::active()->forJobs()->withCount([
            'jobs' => fn($q) => $q->active()->notExpired()
        ])->orderBy('name')->get();

        $cities = JobListing::active()->notExpired()->distinct()->pluck('city')->filter()->sort();

        return view('marketplace.jobs.index', compact('jobs', 'categories', 'cities'));
    }

    /**
     * Single job detail
     */
    public function showJob($slug)
    {
        $job = JobListing::where('slug', $slug)
            ->with(['vendor', 'category'])
            ->firstOrFail();

        if ($job->status !== 'active' && (!auth()->check() || $job->vendor->user_id !== auth()->id())) {
            abort(404);
        }

        $job->incrementViews();

        $hasApplied = auth()->check() ? $job->hasUserApplied(auth()->id()) : false;
        $application = $hasApplied ? JobApplication::where('job_listing_id', $job->id)->where('user_id', auth()->id())->first() : null;

        // Similar jobs
        $similarJobs = JobListing::active()
            ->notExpired()
            ->where('id', '!=', $job->id)
            ->where(fn($q) => $q->where('service_category_id', $job->service_category_id)->orWhere('city', $job->city))
            ->with('vendor')
            ->take(4)
            ->get();

        return view('marketplace.jobs.show', compact('job', 'hasApplied', 'application', 'similarJobs'));
    }

    /**
     * Apply for job
     */
    public function applyJob(Request $request, $slug)
    {
        $job = JobListing::where('slug', $slug)->active()->notExpired()->firstOrFail();

        if ($job->hasUserApplied(auth()->id())) {
            return back()->with('error', 'You have already applied for this job.');
        }

        $request->validate([
            'applicant_name' => 'required|string|max:255',
            'applicant_email' => 'required|email|max:255',
            'applicant_phone' => 'nullable|string|max:20',
            'cover_letter' => 'nullable|string|max:10000',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'expected_salary' => 'nullable|numeric|min:0',
        ]);

        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('job-applications', 'public');
        }

        JobApplication::create([
            'job_listing_id' => $job->id,
            'user_id' => auth()->id(),
            'applicant_name' => $request->applicant_name,
            'applicant_email' => $request->applicant_email,
            'applicant_phone' => $request->applicant_phone,
            'cover_letter' => $request->cover_letter,
            'cv_path' => $cvPath,
            'expected_salary' => $request->expected_salary,
        ]);

        $job->increment('applications_count');

        return redirect()->route('jobs.show', $slug)
            ->with('success', 'Your application has been submitted successfully!');
    }

    // ==========================================
    // SERVICES - PUBLIC BROWSING
    // ==========================================

    /**
     * Services listing page - WITH SUBSCRIPTION RANKING
     */
    public function services(Request $request)
    {
        $query = VendorService::active()
            ->with(['vendor.activeSubscription.plan', 'category'])
            ->whereHas('vendor', fn($q) => $q->where('vetting_status', 'approved'));

        // Filters
        if ($request->filled('category')) {
            $query->where('service_category_id', $request->category);
        }
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where(fn($q) => $q->where('price', '<=', $request->price_max)->orWhereNull('price'));
        }
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('vendor', function ($vq) use ($searchTerm) {
                      $vq->where('business_name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Sorting with subscription ranking as default
        $sort = $request->get('sort', 'recommended');

        if ($sort === 'recommended' || $sort === 'popular') {
            // Apply subscription-based ranking
            $allServices = $query->get()->map(function($service) {
                $boost = ($service->vendor && method_exists($service->vendor, 'getBoostMultiplier'))
                    ? $service->vendor->getBoostMultiplier()
                    : 1.0;
                $service->boost_multiplier = $boost;
                $service->is_promoted = $boost > 1.0;
                return $service;
            });

            // Separate boosted and free, then interleave
            $boosted = $allServices->filter(fn($s) => $s->is_promoted)->sortByDesc('boost_multiplier');
            $free = $allServices->filter(fn($s) => !$s->is_promoted)->sortByDesc('views_count');

            $result = collect();
            $bIdx = 0; $fIdx = 0;
            $total = $boosted->count() + $free->count();
            for ($i = 0; $i < $total; $i++) {
                if ($i > 0 && $i % 3 === 0 && $fIdx < $free->count()) {
                    $result->push($free->values()[$fIdx++]);
                } elseif ($bIdx < $boosted->count()) {
                    $result->push($boosted->values()[$bIdx++]);
                } elseif ($fIdx < $free->count()) {
                    $result->push($free->values()[$fIdx++]);
                }
            }

            $page = $request->get('page', 1);
            $perPage = 20;
            $services = new \Illuminate\Pagination\LengthAwarePaginator(
                $result->forPage($page, $perPage),
                $result->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            match ($sort) {
                'price_low' => $query->orderBy('price'),
                'price_high' => $query->orderByDesc('price'),
                'rating' => $query->orderByDesc('average_rating'),
                'newest' => $query->orderByDesc('created_at'),
                default => $query->orderByDesc('views_count'),
            };
            $services = $query->paginate(20)->withQueryString();
        }

        $categories = ServiceCategory::active()->forServices()->withCount([
            'services' => fn($q) => $q->active()
        ])->orderBy('name')->get();

        $cities = VendorService::active()->distinct()->pluck('city')->filter()->sort();

        return view('marketplace.services.index', compact('services', 'categories', 'cities'));
    }

    /**
     * Single service detail
     */
    public function showService($slug)
    {
        $service = VendorService::where('slug', $slug)
            ->with(['vendor', 'category', 'reviews.user'])
            ->firstOrFail();

        if (!$service->is_active && (!auth()->check() || $service->vendor->user_id !== auth()->id())) {
            abort(404);
        }

        $service->incrementViews();

        // Other services by same vendor
        $vendorServices = VendorService::where('vendor_profile_id', $service->vendor_profile_id)
            ->where('id', '!=', $service->id)
            ->active()
            ->take(4)
            ->get();

        // Similar services
        $similarServices = VendorService::where('service_category_id', $service->service_category_id)
            ->where('id', '!=', $service->id)
            ->active()
            ->with('vendor')
            ->take(4)
            ->get();

        return view('marketplace.services.show', compact('service', 'vendorServices', 'similarServices'));
    }

    /**
     * Request a service (submit booking request)
     */
    public function requestService(Request $request, $slug)
    {
        $service = VendorService::where('slug', $slug)->active()->firstOrFail();

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'description' => 'required|string|max:5000',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'preferred_date' => 'nullable|date|after_or_equal:today',
            'preferred_time' => 'nullable|string|max:50',
            'urgency' => 'required|in:normal,urgent,emergency',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'images.*' => 'nullable|image|max:5120',
        ]);

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('service-requests', 'public');
            }
        }

        $serviceRequest = ServiceRequest::create([
            'vendor_service_id' => $service->id,
            'vendor_profile_id' => $service->vendor_profile_id,
            'user_id' => auth()->id(),
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'description' => $request->description,
            'location' => $request->location,
            'address' => $request->address,
            'preferred_date' => $request->preferred_date,
            'preferred_time' => $request->preferred_time,
            'urgency' => $request->urgency,
            'budget_min' => $request->budget_min,
            'budget_max' => $request->budget_max,
            'images' => $images ?: null,
        ]);

        $service->increment('bookings_count');

        return redirect()->route('buyer.service-requests.show', $serviceRequest)
            ->with('success', 'Your service request has been submitted! The vendor will respond soon.');
    }

    /**
     * Quick inquiry (contact form)
     */
    public function sendInquiry(Request $request, $slug)
    {
        $service = VendorService::where('slug', $slug)->active()->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'message' => 'required|string|max:2000',
        ]);

        ServiceInquiry::create([
            'vendor_service_id' => $service->id,
            'vendor_profile_id' => $service->vendor_profile_id,
            'user_id' => auth()->id(),
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'message' => $request->message,
        ]);

        $service->increment('inquiries_count');

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Your inquiry has been sent!']);
        }

        return back()->with('success', 'Your inquiry has been sent! The vendor will contact you soon.');
    }

    // ==========================================
    // CATEGORY BROWSING
    // ==========================================

    /**
     * Browse by category
     */
    public function category($slug, Request $request)
    {
        $category = ServiceCategory::where('slug', $slug)->active()->firstOrFail();

        $type = $request->get('type', 'all'); // jobs, services, all

        $jobs = collect();
        $services = collect();

        if ($type === 'all' || $type === 'jobs') {
            $jobs = JobListing::active()
                ->notExpired()
                ->where('service_category_id', $category->id)
                ->with('vendor')
                ->orderByDesc('created_at')
                ->take($type === 'all' ? 6 : 20)
                ->get();
        }

        if ($type === 'all' || $type === 'services') {
            $services = VendorService::active()
                ->where('service_category_id', $category->id)
                ->with('vendor')
                ->orderByDesc('views_count')
                ->take($type === 'all' ? 6 : 20)
                ->get();
        }

        return view('marketplace.category', compact('category', 'jobs', 'services', 'type'));
    }
}
