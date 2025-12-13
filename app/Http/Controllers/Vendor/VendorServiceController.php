<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorService;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\ServiceInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VendorServiceController extends Controller
{
    protected function getVendor()
    {
        return auth()->user()->vendorProfile;
    }

    /**
     * List vendor's services
     */
    public function index(Request $request)
    {
        $vendor = $this->getVendor();

        $query = VendorService::where('vendor_profile_id', $vendor->id)
            ->with('category')
            ->withCount(['requests', 'inquiries']);

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $services = $query->latest()->paginate(20);

        $stats = [
            'total' => VendorService::where('vendor_profile_id', $vendor->id)->count(),
            'active' => VendorService::where('vendor_profile_id', $vendor->id)->active()->count(),
            'total_requests' => ServiceRequest::where('vendor_profile_id', $vendor->id)->count(),
            'pending_requests' => ServiceRequest::where('vendor_profile_id', $vendor->id)->pending()->count(),
            'new_inquiries' => ServiceInquiry::where('vendor_profile_id', $vendor->id)->new()->count(),
        ];

        return view('vendor.services.index', compact('services', 'stats'));
    }

    /**
     * Show create service form
     */
    public function create()
    {
        $categories = ServiceCategory::active()->forServices()->orderBy('name')->get();
        return view('vendor.services.create', compact('categories'));
    }

    /**
     * Store new service
     */
    public function store(Request $request)
    {
        $vendor = $this->getVendor();

        $request->validate([
            'title' => 'required|string|max:255',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'description' => 'required|string|max:10000',
            'pricing_type' => 'required|in:fixed,hourly,negotiable,starting_from,free_quote',
            'price' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0|gte:price',
            'duration' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'is_mobile' => 'boolean',
            'features' => 'nullable|string',
            'images.*' => 'nullable|image|max:5120',
        ]);

        // Handle images
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('vendor-services', 'public');
            }
        }

        // Convert features text to array
        $features = $request->features ? array_filter(array_map('trim', explode("\n", $request->features))) : null;

        $service = VendorService::create([
            'vendor_profile_id' => $vendor->id,
            'service_category_id' => $request->service_category_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(4),
            'description' => $request->description,
            'pricing_type' => $request->pricing_type,
            'price' => $request->price,
            'price_max' => $request->price_max,
            'duration' => $request->duration,
            'location' => $request->location ?? $vendor->business_address,
            'city' => $request->city,
            'is_mobile' => $request->boolean('is_mobile'),
            'features' => $features,
            'images' => $images,
            'is_active' => true,
        ]);

        return redirect()->route('vendor.services.index')
            ->with('success', 'Service created successfully!');
    }

    /**
     * Show edit service form
     */
    public function edit($id)
    {
        $vendor = $this->getVendor();
        $service = VendorService::where('vendor_profile_id', $vendor->id)->findOrFail($id);
        $categories = ServiceCategory::active()->forServices()->orderBy('name')->get();

        return view('vendor.services.edit', compact('service', 'categories'));
    }

    /**
     * Update service
     */
    public function update(Request $request, $id)
    {
        $vendor = $this->getVendor();
        $service = VendorService::where('vendor_profile_id', $vendor->id)->findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'description' => 'required|string|max:10000',
            'pricing_type' => 'required|in:fixed,hourly,negotiable,starting_from,free_quote',
            'price' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0|gte:price',
            'duration' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'is_mobile' => 'boolean',
            'features' => 'nullable|string',
            'images.*' => 'nullable|image|max:5120',
            'is_active' => 'boolean',
        ]);

        // Handle new images
        $images = $service->images ?? [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('vendor-services', 'public');
            }
        }

        $features = $request->features ? array_filter(array_map('trim', explode("\n", $request->features))) : null;

        $service->update([
            'service_category_id' => $request->service_category_id,
            'title' => $request->title,
            'description' => $request->description,
            'pricing_type' => $request->pricing_type,
            'price' => $request->price,
            'price_max' => $request->price_max,
            'duration' => $request->duration,
            'location' => $request->location,
            'city' => $request->city,
            'is_mobile' => $request->boolean('is_mobile'),
            'features' => $features,
            'images' => $images,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('vendor.services.index')
            ->with('success', 'Service updated successfully!');
    }

    /**
     * Delete service image
     */
    public function deleteImage(Request $request, $id)
    {
        $vendor = $this->getVendor();
        $service = VendorService::where('vendor_profile_id', $vendor->id)->findOrFail($id);
        
        $imagePath = $request->image;
        if ($service->images && in_array($imagePath, $service->images)) {
            Storage::disk('public')->delete($imagePath);
            $service->images = array_values(array_diff($service->images, [$imagePath]));
            $service->save();
        }

        return back()->with('success', 'Image deleted.');
    }

    /**
     * Delete service
     */
    public function destroy($id)
    {
        $vendor = $this->getVendor();
        $service = VendorService::where('vendor_profile_id', $vendor->id)->findOrFail($id);

        // Delete images
        if ($service->images) {
            foreach ($service->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $service->delete();

        return redirect()->route('vendor.services.index')
            ->with('success', 'Service deleted.');
    }

    /**
     * Toggle service status
     */
    public function toggleStatus($id)
    {
        $vendor = $this->getVendor();
        $service = VendorService::where('vendor_profile_id', $vendor->id)->findOrFail($id);
        
        $service->is_active = !$service->is_active;
        $service->save();

        return back()->with('success', 'Service status updated.');
    }

    // ==================
    // SERVICE REQUESTS
    // ==================

    /**
     * List service requests
     */
    public function requests(Request $request)
    {
        $vendor = $this->getVendor();

        $query = ServiceRequest::where('vendor_profile_id', $vendor->id)
            ->with(['service', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(20);

        $statusCounts = [
            'pending' => ServiceRequest::where('vendor_profile_id', $vendor->id)->pending()->count(),
            'quoted' => ServiceRequest::where('vendor_profile_id', $vendor->id)->where('status', 'quoted')->count(),
            'in_progress' => ServiceRequest::where('vendor_profile_id', $vendor->id)->where('status', 'in_progress')->count(),
            'completed' => ServiceRequest::where('vendor_profile_id', $vendor->id)->completed()->count(),
        ];

        return view('vendor.services.requests', compact('requests', 'statusCounts'));
    }

    /**
     * Show single request
     */
    public function showRequest($id)
    {
        $vendor = $this->getVendor();
        $request = ServiceRequest::where('vendor_profile_id', $vendor->id)
            ->with(['service', 'user', 'review'])
            ->findOrFail($id);

        return view('vendor.services.request-detail', compact('request'));
    }

    /**
     * Submit quote for request
     */
    public function submitQuote(Request $httpRequest, $id)
    {
        $vendor = $this->getVendor();
        $serviceRequest = ServiceRequest::where('vendor_profile_id', $vendor->id)->findOrFail($id);

        if (!$serviceRequest->canBeQuoted()) {
            return back()->with('error', 'Cannot submit quote for this request.');
        }

        $httpRequest->validate([
            'quoted_price' => 'required|numeric|min:0',
            'vendor_notes' => 'nullable|string|max:2000',
        ]);

        $serviceRequest->update([
            'quoted_price' => $httpRequest->quoted_price,
            'vendor_notes' => $httpRequest->vendor_notes,
            'status' => 'quoted',
        ]);

        return back()->with('success', 'Quote submitted successfully!');
    }

    /**
     * Update request status
     */
    public function updateRequestStatus(Request $httpRequest, $id)
    {
        $vendor = $this->getVendor();
        $serviceRequest = ServiceRequest::where('vendor_profile_id', $vendor->id)->findOrFail($id);

        $httpRequest->validate([
            'status' => 'required|in:in_progress,completed,cancelled',
            'vendor_notes' => 'nullable|string|max:2000',
        ]);

        $data = [
            'status' => $httpRequest->status,
            'vendor_notes' => $httpRequest->vendor_notes,
        ];

        if ($httpRequest->status === 'completed') {
            $data['completed_at'] = now();
            $data['final_price'] = $serviceRequest->quoted_price;
        }

        $serviceRequest->update($data);

        return back()->with('success', 'Request status updated.');
    }

    // ==================
    // INQUIRIES
    // ==================

    /**
     * List inquiries
     */
    public function inquiries(Request $request)
    {
        $vendor = $this->getVendor();

        $query = ServiceInquiry::where('vendor_profile_id', $vendor->id)
            ->with(['service', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $inquiries = $query->latest()->paginate(20);

        return view('vendor.services.inquiries', compact('inquiries'));
    }

    /**
     * Update inquiry status
     */
    public function updateInquiryStatus(Request $request, $id)
    {
        $vendor = $this->getVendor();
        $inquiry = ServiceInquiry::where('vendor_profile_id', $vendor->id)->findOrFail($id);

        $request->validate([
            'status' => 'required|in:contacted,converted,closed',
        ]);

        $inquiry->update(['status' => $request->status]);

        return back()->with('success', 'Inquiry status updated.');
    }

    // ==================
    // REVIEWS
    // ==================

    /**
     * List service reviews
     */
    public function reviews()
    {
        $vendor = $this->getVendor();

        $reviews = \App\Models\ServiceReview::where('vendor_profile_id', $vendor->id)
            ->with(['service', 'user'])
            ->latest()
            ->paginate(20);

        return view('vendor.services.reviews', compact('reviews'));
    }

    /**
     * Respond to review
     */
    public function respondToReview(Request $request, $id)
    {
        $vendor = $this->getVendor();
        $review = \App\Models\ServiceReview::where('vendor_profile_id', $vendor->id)->findOrFail($id);

        $request->validate([
            'response' => 'required|string|max:2000',
        ]);

        $review->update([
            'vendor_response' => $request->response,
            'responded_at' => now(),
        ]);

        return back()->with('success', 'Response added.');
    }
}
