<?php
// =============================================================================
// FILE 1: app/Http/Controllers/Admin/AdminVendorController.php
// =============================================================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VendorProfile;
use App\Models\User;
use App\Models\VendorDocument;
use App\Models\VendorScore;
use App\Models\AuditLog;
use App\Models\NotificationQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; 

class AdminVendorController extends Controller
{
    /**
     * Show pending vendors
     */
    public function pending()
    {
        $pendingVendors = VendorProfile::with(['user', 'documents'])
            ->where('vetting_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('admin.vendors.pending', compact('pendingVendors'));
    }
    
    /**
     * Show all vendors
     */
    public function index(Request $request)
    {
        $query = VendorProfile::with(['user', 'scores']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('vetting_status', $request->status);
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('vendor_type', $request->type);
        }

        $vendors = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total' => VendorProfile::count(),
            'pending' => VendorProfile::where('vetting_status', 'pending')->count(),
            'approved' => VendorProfile::where('vetting_status', 'approved')->count(),
            'rejected' => VendorProfile::where('vetting_status', 'rejected')->count(),
        ];

        return view('admin.vendors.index', compact('vendors', 'stats'));
    }
    
    /**
     * Show vendor details
     */
    public function show($id)
    {
        $vendor = VendorProfile::with(['user', 'documents', 'scores', 'listings'])
            ->findOrFail($id);
        $documents = $vendor->documents()->orderBy('created_at', 'desc')->get();
        
        return view('admin.vendors.show', compact('vendor', 'documents'));
    }
    
    /**
     * Approve a vendor
     */
    public function approve(Request $request, $id)
    {
        $vendor = VendorProfile::findOrFail($id);
        
        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'score' => 'nullable|integer|min:0|max:100',
        ]);
        
        DB::beginTransaction();
        try {
            // Update vendor status
            $vendor->vetting_status = 'approved';
            $vendor->vetting_notes = $request->notes;
            $vendor->save();
            
            // Update user status and ensure they remain vendor
            $vendor->user->is_active = true;
            $vendor->user->save();
            
            // Create vendor score
            $scoreValue = $request->score ?? 50; // Default score of 50 if not provided
            $vendor->scores()->create([
                'score' => $scoreValue,
                'factors' => [
                    'admin_approved' => true,
                    'admin_score' => $scoreValue,
                    'approval_date' => now()->toDateTimeString(),
                    'approved_by' => auth()->id(),
                    'documents_verified' => true,
                ]
            ]);
            
            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'vendor_approved',
                'model' => 'VendorProfile',
                'model_id' => $vendor->id,
                'old_values' => ['vetting_status' => 'pending'],
                'new_values' => ['vetting_status' => 'approved'],
                'ip' => $request->ip(),
            ]);
            
            // Send notification to vendor
            NotificationQueue::create([
                'user_id' => $vendor->user_id,
                'type' => 'email',
                'title' => 'Vendor Application Approved! 🎉',
                'message' => "Congratulations {$vendor->user->name}! Your vendor application for '{$vendor->business_name}' has been approved. You can now start listing products and receiving orders.",
                'meta' => [
                    'vendor_id' => $vendor->id,
                    'action_url' => route('vendor.dashboard'),
                    'vendor_score' => $scoreValue,
                ],
                'status' => 'pending',
            ]);
            
            DB::commit();
            
            Log::info('Vendor approved by admin', [
                'vendor_id' => $vendor->id,
                'admin_id' => auth()->id(),
                'score' => $scoreValue
            ]);
            
            return redirect()->route('admin.vendors.pending')
                ->with('success', "Vendor '{$vendor->business_name}' approved successfully with score of {$scoreValue}.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor approval failed', [
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to approve vendor. Please try again.');
        }
    }
    
    /**
     * Reject a vendor
     */
    public function reject(Request $request, $id)
    {
        $vendor = VendorProfile::findOrFail($id);
        
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);
        
        DB::beginTransaction();
        try {
            // Store old status for logging
            $oldStatus = $vendor->vetting_status;
            
            // Update vendor status
            $vendor->vetting_status = 'rejected';
            $vendor->vetting_notes = $request->reason;
            $vendor->save();
            
            // Update user role back to buyer
            $vendor->user->role = 'buyer';
            $vendor->user->save();
            
            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'vendor_rejected',
                'model' => 'VendorProfile',
                'model_id' => $vendor->id,
                'old_values' => ['vetting_status' => $oldStatus],
                'new_values' => ['vetting_status' => 'rejected', 'reason' => $request->reason],
                'ip' => $request->ip(),
            ]);
            
            // Send notification to vendor
            NotificationQueue::create([
                'user_id' => $vendor->user_id,
                'type' => 'email',
                'title' => 'Vendor Application Update',
                'message' => "Hello {$vendor->user->name}, your vendor application for '{$vendor->business_name}' requires attention. Please review the feedback and reapply with the necessary corrections.",
                'meta' => [
                    'vendor_id' => $vendor->id,
                    'reason' => $request->reason,
                    'action_url' => route('vendor.onboard.create'),
                ],
                'status' => 'pending',
            ]);
            
            DB::commit();
            
            Log::info('Vendor rejected by admin', [
                'vendor_id' => $vendor->id,
                'admin_id' => auth()->id(),
                'reason' => $request->reason
            ]);
            
            return redirect()->route('admin.vendors.pending')
                ->with('success', "Vendor application rejected and notification sent.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor rejection failed', [
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to reject vendor. Please try again.');
        }
    }
    
    /**
     * Mark document as verified
     */
    public function verifyDocument(Request $request, $id)
    {
        $document = VendorDocument::findOrFail($id);
        
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);
        
        try {
            $document->update([
                'status' => 'verified',
                'ocr_data' => array_merge($document->ocr_data ?? [], [
                    'verified_by' => auth()->id(),
                    'verified_at' => now()->toDateTimeString(),
                    'verification_notes' => $request->notes,
                ]),
            ]);
            
            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'document_verified',
                'model' => 'VendorDocument',
                'model_id' => $document->id,
                'new_values' => ['status' => 'verified'],
                'ip' => $request->ip(),
            ]);
            
            return back()->with('success', 'Document verified successfully.');
            
        } catch (\Exception $e) {
            Log::error('Document verification failed', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to verify document.');
        }
    }
    
    /**
     * Mark document as rejected
     */
    public function rejectDocument(Request $request, $id)
    {
        $document = VendorDocument::findOrFail($id);
        
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        try {
            $document->update([
                'status' => 'rejected',
                'ocr_data' => array_merge($document->ocr_data ?? [], [
                    'rejected_by' => auth()->id(),
                    'rejected_at' => now()->toDateTimeString(),
                    'rejection_reason' => $request->reason,
                ]),
            ]);
            
            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'document_rejected',
                'model' => 'VendorDocument',
                'model_id' => $document->id,
                'new_values' => ['status' => 'rejected', 'reason' => $request->reason],
                'ip' => $request->ip(),
            ]);
            
            // Notify vendor about rejected document
            NotificationQueue::create([
                'user_id' => $document->vendorProfile->user_id,
                'type' => 'email',
                'title' => 'Document Verification Issue',
                'message' => "One of your uploaded documents needs to be replaced. Reason: {$request->reason}",
                'meta' => [
                    'document_id' => $document->id,
                    'document_type' => $document->type,
                    'action_url' => route('vendor.onboard.status'),
                ],
                'status' => 'pending',
            ]);
            
            return back()->with('success', 'Document rejected and vendor notified.');
            
        } catch (\Exception $e) {
            Log::error('Document rejection failed', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to reject document.');
        }
    }
    
    /**
     * Update vendor score manually
     */
    public function updateScore(Request $request, $id)
    {
        $vendor = VendorProfile::findOrFail($id);
        
        $request->validate([
            'score' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string|max:500',
        ]);
        
        try {
            $previousScore = $vendor->scores()->latest()->first()->score ?? 0;
            
            $vendor->scores()->create([
                'score' => $request->score,
                'factors' => [
                    'manual_update' => true,
                    'updated_by' => auth()->id(),
                    'update_reason' => $request->notes,
                    'previous_score' => $previousScore,
                    'updated_at' => now()->toDateTimeString(),
                ]
            ]);
            
            Log::info('Vendor score updated manually', [
                'vendor_id' => $vendor->id,
                'old_score' => $previousScore,
                'new_score' => $request->score,
                'admin_id' => auth()->id()
            ]);
            
            return back()->with('success', "Vendor score updated from {$previousScore} to {$request->score}.");
            
        } catch (\Exception $e) {
            Log::error('Score update failed', [
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to update score.');
        }
    }

    /**
 * View document securely
 */
public function viewDocument($id)
{
    $document = VendorDocument::findOrFail($id);
    
    // Clean up the path - remove any double slashes or weird characters
    $cleanPath = str_replace('\\', '/', $document->path);
    $cleanPath = preg_replace('/\/+/', '/', $cleanPath); // Replace multiple slashes with single
    
    \Log::info('Clean path', ['original' => $document->path, 'clean' => $cleanPath]);
    
    // Try with Storage facade first (recommended)
    if (Storage::disk('public')->exists($cleanPath)) {
        \Log::info('Found via Storage facade (public disk)');
        return Storage::disk('public')->response($cleanPath, null, [
            'Content-Type' => $document->mime,
            'Content-Disposition' => 'inline; filename="' . basename($cleanPath) . '"'
        ]);
    }
    
    // Try local disk
    if (Storage::disk('local')->exists($cleanPath)) {
        \Log::info('Found via Storage facade (local disk)');
        return Storage::disk('local')->response($cleanPath, null, [
            'Content-Type' => $document->mime,
            'Content-Disposition' => 'inline; filename="' . basename($cleanPath) . '"'
        ]);
    }
    
    // Try direct file path (fallback)
    $paths = [
        storage_path('app/public/' . $cleanPath),
        storage_path('app/' . $cleanPath),
        public_path('storage/' . $cleanPath),
    ];
    
    foreach ($paths as $path) {
        $path = str_replace('\\', '/', $path);
        if (file_exists($path)) {
            \Log::info('Found via direct file path', ['path' => $path]);
            return response()->file($path, [
                'Content-Type' => $document->mime,
                'Content-Disposition' => 'inline; filename="' . basename($cleanPath) . '"'
            ]);
        }
    }
    
    \Log::error('Document file not found', [
        'document_id' => $id,
        'path' => $document->path,
        'clean_path' => $cleanPath
    ]);
    
    abort(404, 'Document file not found');
}
    
    /**
     * Toggle vendor active status
     */
    public function toggleStatus($id)
    {
        $vendor = VendorProfile::findOrFail($id);
        
        try {
            $newStatus = !$vendor->user->is_active;
            $vendor->user->is_active = $newStatus;
            $vendor->user->save();
            
            $action = $newStatus ? 'vendor_activated' : 'vendor_deactivated';
            
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'model' => 'VendorProfile',
                'model_id' => $vendor->id,
                'old_values' => ['is_active' => !$newStatus],
                'new_values' => ['is_active' => $newStatus],
                'ip' => request()->ip(),
            ]);
            
            $statusText = $newStatus ? 'activated' : 'deactivated';
            
            return back()->with('success', "Vendor {$statusText} successfully.");
            
        } catch (\Exception $e) {
            Log::error('Status toggle failed', [
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to update status.');
        }
    }
}