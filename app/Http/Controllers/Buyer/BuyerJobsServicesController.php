<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\ServiceRequest;
use App\Models\ServiceReview;
use Illuminate\Http\Request;

class BuyerJobsServicesController extends Controller
{
    // ==========================================
    // MY JOB APPLICATIONS
    // ==========================================

    /**
     * List user's job applications
     */
    public function myApplications(Request $request)
    {
        $query = JobApplication::where('user_id', auth()->id())
            ->with(['job.vendor', 'job.category']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->latest()->paginate(20);

        return view('buyer.jobs.my-applications', compact('applications'));
    }

    /**
     * View single application
     */
    public function showApplication($id)
    {
        $application = JobApplication::where('user_id', auth()->id())
            ->with(['job.vendor', 'job.category'])
            ->findOrFail($id);

        return view('buyer.jobs.application-detail', compact('application'));
    }

    /**
     * Withdraw application
     */
    public function withdrawApplication($id)
    {
        $application = JobApplication::where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'reviewed'])
            ->findOrFail($id);

        $application->job->decrement('applications_count');
        $application->delete();

        return redirect()->route('buyer.applications.index')
            ->with('success', 'Application withdrawn successfully.');
    }

    // ==========================================
    // MY SERVICE REQUESTS
    // ==========================================

    /**
     * List user's service requests
     */
    public function myServiceRequests(Request $request)
    {
        $query = ServiceRequest::where('user_id', auth()->id())
            ->with(['service.vendor', 'service.category']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(20);

        return view('buyer.services.my-requests', compact('requests'));
    }

    /**
     * View single service request
     */
    public function showServiceRequest($id)
    {
        $request = ServiceRequest::where('user_id', auth()->id())
            ->with(['service.vendor', 'review'])
            ->findOrFail($id);

        return view('buyer.services.request-detail', compact('request'));
    }

    /**
     * Accept vendor's quote
     */
    public function acceptQuote($id)
    {
        $request = ServiceRequest::where('user_id', auth()->id())
            ->where('status', 'quoted')
            ->findOrFail($id);

        $request->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'final_price' => $request->quoted_price,
        ]);

        return back()->with('success', 'Quote accepted! The vendor will begin work soon.');
    }

    /**
     * Cancel service request
     */
    public function cancelServiceRequest($id)
    {
        $request = ServiceRequest::where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'quoted'])
            ->findOrFail($id);

        $request->update(['status' => 'cancelled']);

        return back()->with('success', 'Service request cancelled.');
    }

    /**
     * Mark request as completed (confirm work done)
     */
    public function confirmCompletion($id)
    {
        $request = ServiceRequest::where('user_id', auth()->id())
            ->where('status', 'in_progress')
            ->findOrFail($id);

        $request->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('buyer.service-requests.review', $request)
            ->with('success', 'Service marked as complete! Please leave a review.');
    }

    // ==========================================
    // REVIEWS
    // ==========================================

    /**
     * Show review form
     */
    public function showReviewForm($requestId)
    {
        $request = ServiceRequest::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->with('service')
            ->findOrFail($requestId);

        // Check if already reviewed
        $existingReview = ServiceReview::where('service_request_id', $requestId)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingReview) {
            return redirect()->route('buyer.service-requests.show', $requestId)
                ->with('info', 'You have already reviewed this service.');
        }

        return view('buyer.services.review', compact('request'));
    }

    /**
     * Submit review
     */
    public function submitReview(Request $httpRequest, $requestId)
    {
        $serviceRequest = ServiceRequest::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->findOrFail($requestId);

        // Check if already reviewed
        if (ServiceReview::where('service_request_id', $requestId)->where('user_id', auth()->id())->exists()) {
            return back()->with('error', 'You have already reviewed this service.');
        }

        $httpRequest->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'images.*' => 'nullable|image|max:5120',
        ]);

        $images = [];
        if ($httpRequest->hasFile('images')) {
            foreach ($httpRequest->file('images') as $image) {
                $images[] = $image->store('service-reviews', 'public');
            }
        }

        ServiceReview::create([
            'vendor_service_id' => $serviceRequest->vendor_service_id,
            'service_request_id' => $requestId,
            'user_id' => auth()->id(),
            'vendor_profile_id' => $serviceRequest->vendor_profile_id,
            'rating' => $httpRequest->rating,
            'comment' => $httpRequest->comment,
            'images' => $images ?: null,
            'is_verified' => true, // From completed job
        ]);

        return redirect()->route('buyer.service-requests.show', $requestId)
            ->with('success', 'Thank you for your review!');
    }
}