<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dispute;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DisputeController extends Controller
{
    public function index()
    {
        $disputes = Auth::user()->disputes()
            ->with(['order.vendorProfile.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('buyer.disputes.index', compact('disputes'));
    }
    
    public function create(Order $order)
    {
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }
        
        if (!in_array($order->status, ['delivered', 'shipped'])) {
            return back()->with('error', 'You can only raise disputes for delivered or shipped orders.');
        }
        
        // Check if dispute already exists
        if ($order->dispute()->exists()) {
            return redirect()->route('buyer.disputes.show', $order->dispute)
                ->with('info', 'A dispute already exists for this order.');
        }
        
        return view('buyer.disputes.create', compact('order'));
    }
    
    public function store(Request $request, Order $order)
    {
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'type' => 'required|in:item_not_received,item_damaged,wrong_item,quality_issue,other',
            'description' => 'required|string|max:2000',
            'desired_resolution' => 'required|in:refund,replacement,partial_refund,other',
            'amount_requested' => 'nullable|numeric|min:0|max:' . $order->total,
            'evidence.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        
        DB::beginTransaction();
        try {
            $evidencePaths = [];
            if ($request->hasFile('evidence')) {
                foreach ($request->file('evidence') as $file) {
                    $path = $file->store('disputes/' . $order->id, 'public');
                    $evidencePaths[] = $path;
                }
            }
            
            $dispute = Dispute::create([
                'order_id' => $order->id,
                'raised_by' => Auth::id(),
                'type' => $request->type,
                'status' => 'open',
                'meta' => [
                    'description' => $request->description,
                    'desired_resolution' => $request->desired_resolution,
                    'amount_requested' => $request->amount_requested,
                    'evidence' => $evidencePaths,
                    'raised_at' => now()->toDateTimeString(),
                ]
            ]);
            
            // Update order status
            $order->update(['status' => 'disputed']);
            
            // Notify vendor and admin
            \App\Models\NotificationQueue::create([
                'user_id' => $order->vendorProfile->user_id,
                'type' => 'dispute_raised',
                'title' => 'Dispute Raised on Order',
                'message' => "A dispute has been raised on order #{$order->order_number}",
                'meta' => ['dispute_id' => $dispute->id, 'order_id' => $order->id],
                'status' => 'pending',
            ]);
            
            DB::commit();
            
            return redirect()->route('buyer.disputes.show', $dispute)
                ->with('success', 'Dispute raised successfully. We will review your case.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to raise dispute: ' . $e->getMessage());
        }
    }
    
    public function show(Dispute $dispute)
    {
        if ($dispute->raised_by !== Auth::id()) {
            abort(403);
        }
        
        $dispute->load([
            'order.buyer',
            'order.vendorProfile.user',
            'order.items.listing',
            'raisedBy',
            'order.escrow'
        ]);
        
        return view('buyer.disputes.show', compact('dispute'));
    }
    
    public function addEvidence(Request $request, Dispute $dispute)
    {
        if ($dispute->raised_by !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'evidence.*' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'comment' => 'nullable|string|max:1000',
        ]);
        
        $evidencePaths = $dispute->meta['evidence'] ?? [];
        
        if ($request->hasFile('evidence')) {
            foreach ($request->file('evidence') as $file) {
                $path = $file->store('disputes/' . $dispute->order_id, 'public');
                $evidencePaths[] = $path;
            }
        }
        
        $meta = $dispute->meta;
        $meta['evidence'] = $evidencePaths;
        
        // Add comment if provided
        if ($request->filled('comment')) {
            $comments = $meta['comments'] ?? [];
            $comments[] = [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'comment' => $request->comment,
                'is_internal' => false,
                'created_at' => now()->toDateTimeString(),
            ];
            $meta['comments'] = $comments;
        }
        
        $dispute->update(['meta' => $meta]);
        
        return back()->with('success', 'Evidence added successfully.');
    }
    
    public function acceptResolution(Request $request, Dispute $dispute)
    {
        if ($dispute->raised_by !== Auth::id()) {
            abort(403);
        }
        
        if ($dispute->status !== 'resolved') {
            return back()->with('error', 'Dispute is not yet resolved.');
        }
        
        $dispute->update([
            'status' => 'closed',
            'meta' => array_merge($dispute->meta ?? [], [
                'accepted_by_buyer' => true,
                'accepted_at' => now()->toDateTimeString(),
            ])
        ]);
        
        return back()->with('success', 'Resolution accepted. Dispute closed.');
    }
}