<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VendorProfile;
use App\Models\VendorDocument;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorOnboardingController extends Controller
{
    /**
     * Show the onboarding form
     * Public access - anyone can view the form
     */
    public function create()
    {
        // If user is logged in, check their status
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user already has a vendor profile
            if ($user->vendorProfile) {
                if ($user->vendorProfile->vetting_status === 'approved') {
                    return redirect()->route('vendor.dashboard')
                        ->with('info', 'Your vendor account is already approved.');
                } else {
                    return redirect()->route('vendor.onboard.status')
                        ->with('info', 'Your application is already submitted.');
                }
            }
        }
        
        // Show form to everyone (logged in or not)
        return view('vendor.onboarding.create');
    }

    /**
     * Store vendor profile and documents
     * Handles both new user registration and existing user onboarding
     */
    public function store(Request $request)
    {
        // Determine if this is a new user registration or existing user onboarding
        $isNewUser = !Auth::check();
        
        // Build validation rules based on whether user is logged in
        $validationRules = [
            'vendor_type' => 'required|in:local_retail,china_supplier,dropship',
            'business_name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'address' => 'required|string|max:500',
            'annual_turnover' => 'nullable|numeric|min:0',
            'preferred_currency' => 'required|string|size:3',
            
            // Document validations
            'national_id_front' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'national_id_back' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'bank_statement' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'proof_of_address' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'guarantor_name' => 'required|string|max:255',
            'guarantor_phone' => 'required|string|max:20',
            'guarantor_id' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            
            // Optional company docs
            'company_registration' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'tax_certificate' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            
            'terms' => 'required|accepted',
        ];
        
        // Add user registration fields if not logged in
        if ($isNewUser) {
            $validationRules = array_merge($validationRules, [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'required|string|max:20',
                'password' => 'required|string|min:8|confirmed',
            ]);
        }
        
        // Validate all fields
        $validated = $request->validate($validationRules);

        // Start database transaction
        DB::beginTransaction();
        
        try {
            // Handle user creation or retrieval
            if ($isNewUser) {
                // Create new user account
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'password' => Hash::make($validated['password']),
                    'role' => $validated['vendor_type'] == 'china_supplier' ? 'vendor_international' : 'vendor_local',
                    'is_active' => true,
                ]);
                
                Log::info('New vendor user created', ['user_id' => $user->id, 'email' => $user->email]);
            } else {
                // Use existing logged-in user
                $user = Auth::user();
                
                // Check if user already has a vendor profile
                if ($user->vendorProfile) {
                    DB::rollBack();
                    return back()->withInput()
                        ->with('error', 'You already have a vendor profile. Please check your application status.');
                }
                
                // Update user role to vendor
                $user->update([
                    'role' => $validated['vendor_type'] == 'china_supplier' ? 'vendor_international' : 'vendor_local'
                ]);
                
                Log::info('Existing user applying for vendor', ['user_id' => $user->id]);
            }

            // Create vendor profile
            $vendorProfile = VendorProfile::create([
                'user_id' => $user->id,
                'vendor_type' => $validated['vendor_type'],
                'business_name' => $validated['business_name'],
                'country' => $validated['country'],
                'city' => $validated['city'],
                'address' => $validated['address'],
                'annual_turnover' => $validated['annual_turnover'],
                'preferred_currency' => $validated['preferred_currency'],
                'vetting_status' => 'pending',
                'vetting_notes' => null,
                'meta' => [
                    'guarantor' => [
                        'name' => $validated['guarantor_name'],
                        'phone' => $validated['guarantor_phone'],
                        'added_at' => now()->toDateTimeString(),
                    ]
                ]
            ]);
            
            Log::info('Vendor profile created', ['vendor_profile_id' => $vendorProfile->id]);

            // Upload and save documents
            $documents = [
                ['type' => 'national_id', 'file' => $request->file('national_id_front'), 'side' => 'front'],
                ['type' => 'national_id', 'file' => $request->file('national_id_back'), 'side' => 'back'],
                ['type' => 'bank_statement', 'file' => $request->file('bank_statement')],
                ['type' => 'proof_of_address', 'file' => $request->file('proof_of_address')],
                ['type' => 'guarantor_id', 'file' => $request->file('guarantor_id')],
            ];

            // Add optional company documents
            if ($request->hasFile('company_registration')) {
                $documents[] = ['type' => 'company_docs', 'file' => $request->file('company_registration'), 'subtype' => 'registration'];
            }
            
            if ($request->hasFile('tax_certificate')) {
                $documents[] = ['type' => 'company_docs', 'file' => $request->file('tax_certificate'), 'subtype' => 'tax'];
            }

            foreach ($documents as $doc) {
                $file = $doc['file'];
                $path = $file->store('vendor-documents/' . $vendorProfile->id, 'public');
                
                VendorDocument::create([
                    'vendor_profile_id' => $vendorProfile->id,
                    'type' => $doc['type'],
                    'path' => $path,
                    'mime' => $file->getMimeType(),
                    'ocr_data' => [
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'uploaded_at' => now()->toDateTimeString(),
                        'side' => $doc['side'] ?? null,
                        'subtype' => $doc['subtype'] ?? null,
                    ],
                    'status' => 'uploaded',
                ]);
            }
            
            Log::info('Vendor documents uploaded', ['vendor_profile_id' => $vendorProfile->id, 'document_count' => count($documents)]);

            // Create initial vendor score
            $vendorProfile->scores()->create([
                'score' => 25, // Starting score for submitting documents
                'factors' => [
                    'id_uploaded' => true,
                    'bank_statement_uploaded' => true,
                    'address_proof_uploaded' => true,
                    'guarantor_provided' => true,
                    'initial_score' => 25,
                ]
            ]);

            // Send notification to admin about new vendor
            \App\Models\NotificationQueue::create([
                'user_id' => null, // Will be picked up by admin users
                'type' => 'admin_notification',
                'title' => 'New Vendor Application',
                'message' => "New vendor application from {$validated['business_name']}. Please review.",
                'meta' => [
                    'vendor_id' => $vendorProfile->id,
                    'vendor_name' => $vendorProfile->business_name,
                    'vendor_type' => $vendorProfile->vendor_type,
                    'vendor_email' => $user->email,
                    'action_url' => route('admin.vendors.pending')
                ],
                'status' => 'pending'
            ]);

            DB::commit();
            
            Log::info('Vendor onboarding completed successfully', [
                'user_id' => $user->id,
                'vendor_profile_id' => $vendorProfile->id
            ]);

            // Auto-login the user if they just registered
            if ($isNewUser) {
                Auth::login($user);
            }

            return redirect()->route('vendor.onboard.status')
                ->with('success', 'Vendor application submitted successfully! It will be reviewed within 24-48 hours. Check your email for updates.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor onboarding failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()
                ->with('error', 'Failed to submit application. Please try again. If the problem persists, contact support.');
        }
    }

    /**
     * Show vendor onboarding status
     * Requires authentication
     */
    public function show()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to view your application status.');
        }
        
        $vendorProfile = Auth::user()->vendorProfile;
        
        if (!$vendorProfile) {
            return redirect()->route('vendor.onboard.create')
                ->with('info', 'You have not submitted a vendor application yet.');
        }

        $documents = $vendorProfile->documents;
        $score = $vendorProfile->scores()->latest()->first();
        
        return view('vendor.onboarding.status', compact('vendorProfile', 'documents', 'score'));
    }

    /**
     * Upload additional documents
     * Requires authentication
     */
    public function uploadAdditional(Request $request)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to upload documents.');
        }
        
        $request->validate([
            'document_type' => 'required|in:additional_id,bank_statement_update,business_license,other',
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'description' => 'nullable|string|max:500',
        ]);

        $vendorProfile = Auth::user()->vendorProfile;
        
        if (!$vendorProfile) {
            return redirect()->route('vendor.onboard.create')
                ->with('error', 'You need to submit a vendor application first.');
        }

        try {
            $file = $request->file('document');
            $path = $file->store('vendor-documents/' . $vendorProfile->id . '/additional', 'public');

            VendorDocument::create([
                'vendor_profile_id' => $vendorProfile->id,
                'type' => $request->document_type,
                'path' => $path,
                'mime' => $file->getMimeType(),
                'ocr_data' => [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'uploaded_at' => now()->toDateTimeString(),
                    'description' => $request->description,
                    'upload_type' => 'additional',
                ],
                'status' => 'uploaded',
            ]);
            
            Log::info('Additional document uploaded', [
                'vendor_profile_id' => $vendorProfile->id,
                'document_type' => $request->document_type
            ]);

            return back()->with('success', 'Additional document uploaded successfully.');
            
        } catch (\Exception $e) {
            Log::error('Additional document upload failed', [
                'vendor_profile_id' => $vendorProfile->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to upload document. Please try again.');
        }
    }
}