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
        // Log incoming request for debugging
        Log::info('Vendor onboarding form submitted', [
            'is_authenticated' => Auth::check(),
            'has_files' => $request->hasFile('national_id_front'),
            'request_keys' => array_keys($request->all()),
        ]);
        
        // Determine if this is a new user registration or existing user onboarding
        $isNewUser = !Auth::check();
        
        // Build validation rules based on whether user is logged in
        $validationRules = [
            'vendor_type' => 'required|in:local_retail,china_supplier,dropship',
            'business_name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'annual_turnover' => 'nullable|numeric|min:0',
            'preferred_currency' => 'required|string|size:3',

            // Document validations - Only National ID is required
            'national_id_front' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'national_id_back' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',

            // Optional documents
            'bank_statement' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'proof_of_address' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'guarantor_name' => 'nullable|string|max:255',
            'guarantor_phone' => 'nullable|string|max:20',
            'guarantor_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',

            // Optional company docs
            'company_registration' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'tax_certificate' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',

            'terms' => 'required|accepted',
        ];

        // Conditional China supplier validation
        if ($request->input('vendor_type') === 'china_supplier') {
            $validationRules = array_merge($validationRules, [
                'china_company_name' => 'required|string|max:255',
                'uscc' => ['required', 'string', 'size:18', 'regex:/^[A-Za-z0-9]{18}$/', 'unique:vendor_profiles,uscc'],
                'legal_representative' => 'required|string|max:255',
                'business_scope' => 'required|string|max:2000',
                'china_registered_address' => 'required|string|max:500',
                'registered_capital' => 'nullable|string|max:100',
                'business_license' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'industry_permits' => 'nullable|array|max:5',
                'industry_permits.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
            ]);

            // For china_supplier, National ID is optional (Business License is primary)
            $validationRules['national_id_front'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120';
            $validationRules['national_id_back'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }

        // Add user registration fields if not logged in
        if ($isNewUser) {
            $validationRules = array_merge($validationRules, [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'required|string|max:20',
                'password' => 'required|string|min:8|confirmed',
            ]);
        }
        
        // Validate all fields with better error handling
        try {
            $validated = $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Vendor onboarding validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['password', 'password_confirmation']),
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return back()->withErrors($e->errors())->withInput();
        }

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
                    // role set explicitly below
                    // is_active set explicitly below
                ]);
            // Set protected fields explicitly
            $user->role = $validated['vendor_type'] == 'china_supplier' ? 'vendor_international' : 'vendor_local';
            $user->is_active = true;
            $user->save();
                
                Log::info('New vendor user created', ['user_id' => $user->id, 'email' => $user->email]);
            } else {
                // Use existing logged-in user
                $user = Auth::user();
                
                // Check if user already has a vendor profile
                if ($user->vendorProfile) {
                    DB::rollBack();
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You already have a vendor profile.'
                        ], 400);
                    }
                    return back()->withInput()
                        ->with('error', 'You already have a vendor profile. Please check your application status.');
                }
                
                // Update user role to vendor
                $user->role = $validated['vendor_type'] == 'china_supplier' ? 'vendor_international' : 'vendor_local';
                $user->save();
                
                Log::info('Existing user applying for vendor', ['user_id' => $user->id]);
            }

            // Build meta data
            $metaData = [];
            if (!empty($validated['guarantor_name'])) {
                $metaData['guarantor'] = [
                    'name' => $validated['guarantor_name'],
                    'phone' => $validated['guarantor_phone'] ?? null,
                    'added_at' => now()->toDateTimeString(),
                ];
            }
            if ($validated['vendor_type'] === 'china_supplier') {
                $metaData['china_verification'] = [
                    'company_chinese_name' => $validated['china_company_name'],
                    'legal_representative' => $validated['legal_representative'],
                    'business_scope' => $validated['business_scope'],
                    'registered_address' => $validated['china_registered_address'],
                    'registered_capital' => $validated['registered_capital'] ?? null,
                    'submitted_at' => now()->toDateTimeString(),
                ];
            }

            // Create vendor profile
            $vendorProfile = VendorProfile::create([
                'user_id' => $user->id,
                'vendor_type' => $validated['vendor_type'],
                'business_name' => $validated['business_name'],
                'country' => $validated['country'],
                'city' => $validated['city'] ?? null,
                'address' => $validated['address'] ?? null,
                'annual_turnover' => $validated['annual_turnover'] ?? null,
                'preferred_currency' => $validated['preferred_currency'],
                'uscc' => $validated['uscc'] ?? null,
                'meta' => !empty($metaData) ? $metaData : null,
            ]);
            // Set vetting fields explicitly (not mass-assignable for security)
            $vendorProfile->vetting_status = 'pending';
            $vendorProfile->save();
            
            Log::info('Vendor profile created', ['vendor_profile_id' => $vendorProfile->id]);

            // Upload and save documents
            $documents = [];

            // National ID - required for non-China, optional for China suppliers
            if ($request->hasFile('national_id_front')) {
                $documents[] = ['type' => 'national_id', 'file' => $request->file('national_id_front'), 'side' => 'front'];
            }
            if ($request->hasFile('national_id_back')) {
                $documents[] = ['type' => 'national_id', 'file' => $request->file('national_id_back'), 'side' => 'back'];
            }

            // China supplier documents
            if ($validated['vendor_type'] === 'china_supplier') {
                if ($request->hasFile('business_license')) {
                    $documents[] = ['type' => 'business_license', 'file' => $request->file('business_license')];
                }
                if ($request->hasFile('industry_permits')) {
                    foreach ($request->file('industry_permits') as $index => $permitFile) {
                        $documents[] = [
                            'type' => 'industry_permit',
                            'file' => $permitFile,
                            'subtype' => 'permit_' . ($index + 1),
                        ];
                    }
                }
            }

            // Add optional documents if provided
            if ($request->hasFile('bank_statement')) {
                $documents[] = ['type' => 'bank_statement', 'file' => $request->file('bank_statement')];
            }

            if ($request->hasFile('proof_of_address')) {
                $documents[] = ['type' => 'proof_of_address', 'file' => $request->file('proof_of_address')];
            }

            if ($request->hasFile('guarantor_id')) {
                $documents[] = ['type' => 'guarantor_id', 'file' => $request->file('guarantor_id')];
            }

            // Add optional company documents
            if ($request->hasFile('company_registration')) {
                $documents[] = ['type' => 'company_docs', 'file' => $request->file('company_registration'), 'subtype' => 'registration'];
            }

            if ($request->hasFile('tax_certificate')) {
                $documents[] = ['type' => 'company_docs', 'file' => $request->file('tax_certificate'), 'subtype' => 'tax'];
            }

            foreach ($documents as $doc) {
                $file = $doc['file'];
                
                // Validate file exists and is valid
                if (!$file || !$file->isValid()) {
                    throw new \Exception('Invalid file uploaded: ' . ($doc['type'] ?? 'unknown'));
                }
                
                // Store file
                $path = $file->store('vendor-documents/' . $vendorProfile->id, 'public');
                
                if (!$path) {
                    throw new \Exception('Failed to store file: ' . $file->getClientOriginalName());
                }
                
                // Create document record
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
                
                Log::info('Document uploaded successfully', [
                    'vendor_profile_id' => $vendorProfile->id,
                    'type' => $doc['type'],
                    'path' => $path,
                ]);
            }
            
            Log::info('Vendor documents uploaded', ['vendor_profile_id' => $vendorProfile->id, 'document_count' => count($documents)]);

            // Create initial vendor score - base score for ID, bonus for optional docs
            $scoreFactors = [
                'id_uploaded' => true,
                'bank_statement_uploaded' => $request->hasFile('bank_statement'),
                'address_proof_uploaded' => $request->hasFile('proof_of_address'),
                'guarantor_provided' => !empty($validated['guarantor_name']),
            ];

            // Calculate score: 15 base for ID, +5 for each optional document
            $score = 15; // Base score for National ID
            if ($scoreFactors['bank_statement_uploaded']) $score += 5;
            if ($scoreFactors['address_proof_uploaded']) $score += 5;
            if ($scoreFactors['guarantor_provided']) $score += 5;

            // China-specific scoring
            if ($validated['vendor_type'] === 'china_supplier') {
                $scoreFactors['business_license_uploaded'] = $request->hasFile('business_license');
                $scoreFactors['industry_permits_count'] = $request->hasFile('industry_permits')
                    ? count($request->file('industry_permits'))
                    : 0;
                $scoreFactors['uscc_provided'] = !empty($validated['uscc']);

                if ($scoreFactors['business_license_uploaded']) $score += 10;
                if ($scoreFactors['industry_permits_count'] > 0) {
                    $score += min($scoreFactors['industry_permits_count'] * 5, 15);
                }
                if ($scoreFactors['uscc_provided']) $score += 5;
            }

            $scoreFactors['initial_score'] = $score;

            $vendorProfile->scores()->create([
                'score' => $score,
                'factors' => $scoreFactors
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
                    'is_china_supplier' => $vendorProfile->vendor_type === 'china_supplier',
                    'uscc' => $vendorProfile->uscc,
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

            // Check if this is an API request (JSON expected or has Bearer token or is api/* path)
            $isApiRequest = $request->expectsJson()
                || $request->bearerToken()
                || $request->is('api/*');

            if ($isApiRequest) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vendor application submitted successfully!',
                    'vendor_profile' => $vendorProfile
                ]);
            }

            return redirect()->route('vendor.onboard.status')
                ->with('success', 'Vendor application submitted successfully! It will be reviewed within 24-48 hours. Check your email for updates.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Vendor onboarding validation exception', [
                'errors' => $e->errors(),
            ]);

            $isApiRequest = $request->expectsJson() || $request->bearerToken() || $request->is('api/*');
            if ($isApiRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput()
                ->with('error', 'Please correct the errors below and try again.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor onboarding failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $isApiRequest = $request->expectsJson() || $request->bearerToken() || $request->is('api/*');
            if ($isApiRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to submit application. Please try again.'
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to submit application: ' . $e->getMessage() . '. Please try again or contact support.');
        }
    }


 public function show(Request $request)
{
    // Check if user is logged in
    if (!Auth::check()) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        return redirect()->route('login')
            ->with('error', 'Please login to view your application status.');
    }
    
    $user = Auth::user();
    
    // ============================================================
    // This prevents admins and other roles from seeing this page
    // ============================================================
    $roleRedirects = [
        'admin' => 'admin.dashboard',
        'ceo' => 'ceo.dashboard',
        'finance' => 'finance.dashboard',
        'logistics' => 'logistics.dashboard',
        'buyer' => 'welcome',
    ];
    
    if (isset($roleRedirects[$user->role])) {
        $routeName = $roleRedirects[$user->role];
        $message = $user->role === 'admin' 
            ? 'Welcome back, Admin!' 
            : 'You have been redirected to your dashboard.';
            
        return redirect()->route($routeName)->with('info', $message);
    }
    
    // Only vendor-type roles should see this page
    if (!in_array($user->role, ['vendor_local', 'vendor_international'])) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'This page is only for vendor accounts.'
            ], 403);
        }
        return redirect()->route('welcome')
            ->with('error', 'This page is only for vendor accounts.');
    }
    
    // ============================================================
    // Vendor-specific logic below
    // ============================================================
    $vendorProfile = $user->vendorProfile;
    
    if (!$vendorProfile) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'You haven\'t submitted a vendor application yet.'
            ], 404);
        }
        return redirect()->route('vendor.onboard.create')
            ->with('info', 'You haven\'t submitted a vendor application yet.');
    }

    $documents = $vendorProfile->documents;
    $score = $vendorProfile->scores()->latest()->first();
    
    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'vendor_profile' => $vendorProfile,
            'status' => $vendorProfile->vetting_status,
            'documents' => $documents,
            'score' => $score
        ]);
    }

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
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
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
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You need to submit a vendor application first.'
                ], 400);
            }
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

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Additional document uploaded successfully.'
                ]);
            }

            return back()->with('success', 'Additional document uploaded successfully.');
            
        } catch (\Exception $e) {
            Log::error('Additional document upload failed', [
                'vendor_profile_id' => $vendorProfile->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload document. Please try again.'
                ], 500);
            }

            return back()->with('error', 'Failed to upload document. Please try again.');
        }
    }
}