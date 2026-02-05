<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\VendorSubscription;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminSubscriptionController extends Controller
{
    /**
     * Display all vendor subscriptions
     */
    public function index(Request $request)
    {
        $query = VendorSubscription::with(['vendorProfile.user', 'plan'])
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by plan
        if ($request->filled('plan_id')) {
            $query->where('subscription_plan_id', $request->plan_id);
        }

        // Search by vendor name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('vendorProfile', function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $subscriptions = $query->paginate(20);
        $plans = SubscriptionPlan::active()->ordered()->get();

        $stats = [
            'total' => VendorSubscription::count(),
            'active' => VendorSubscription::where('status', 'active')->count(),
            'pending' => VendorSubscription::where('status', 'pending')->count(),
            'expired' => VendorSubscription::where('status', 'expired')->count(),
            'cancelled' => VendorSubscription::where('status', 'cancelled')->count(),
        ];

        return view('admin.subscriptions.index', compact('subscriptions', 'plans', 'stats'));
    }

    /**
     * Display subscription plans management
     */
    public function plans()
    {
        $plans = SubscriptionPlan::ordered()->get();

        $planStats = $plans->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'active_subscribers' => VendorSubscription::where('subscription_plan_id', $plan->id)
                    ->where('status', 'active')
                    ->count(),
                'total_revenue' => SubscriptionPayment::whereHas('vendorSubscription', function ($q) use ($plan) {
                    $q->where('subscription_plan_id', $plan->id);
                })->where('status', 'completed')->sum('amount'),
            ];
        });

        return view('admin.subscriptions.plans', compact('plans', 'planStats'));
    }

    /**
     * Store a new subscription plan
     */
    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'boost_multiplier' => 'required|numeric|min:1|max:10',
            'max_featured_listings' => 'required|integer|min:0',
            'badge_enabled' => 'boolean',
            'badge_text' => 'nullable|string|max:100',
            'features' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = true;

        // Ensure slug is unique
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (SubscriptionPlan::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter++;
        }

        SubscriptionPlan::create($validated);

        return redirect()->route('admin.subscriptions.plans')
            ->with('success', 'Subscription plan created successfully');
    }

    /**
     * Update a subscription plan
     */
    public function updatePlan(Request $request, $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'boost_multiplier' => 'required|numeric|min:1|max:10',
            'max_featured_listings' => 'required|integer|min:0',
            'badge_enabled' => 'boolean',
            'badge_text' => 'nullable|string|max:100',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $plan->update($validated);

        return redirect()->route('admin.subscriptions.plans')
            ->with('success', 'Subscription plan updated successfully');
    }

    /**
     * Delete a subscription plan
     */
    public function destroyPlan($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        // Check if there are active subscriptions
        $activeCount = $plan->vendorSubscriptions()->where('status', 'active')->count();

        if ($activeCount > 0) {
            return back()->with('error', "Cannot delete plan with {$activeCount} active subscriptions");
        }

        $plan->delete();

        return redirect()->route('admin.subscriptions.plans')
            ->with('success', 'Subscription plan deleted successfully');
    }

    /**
     * Toggle plan active status
     */
    public function togglePlanStatus($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->update(['is_active' => !$plan->is_active]);

        $status = $plan->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Plan {$status} successfully");
    }

    /**
     * Manually extend a subscription
     */
    public function extendSubscription(Request $request, $id)
    {
        $subscription = VendorSubscription::findOrFail($id);

        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $days = $request->days;
        $currentExpiry = $subscription->expires_at ?? now();

        if ($currentExpiry->isPast()) {
            $currentExpiry = now();
        }

        $subscription->update([
            'status' => 'active',
            'expires_at' => $currentExpiry->addDays($days),
        ]);

        return back()->with('success', "Subscription extended by {$days} days");
    }

    /**
     * Manually cancel a subscription
     */
    public function cancelSubscription($id)
    {
        $subscription = VendorSubscription::findOrFail($id);
        $subscription->cancel();

        return back()->with('success', 'Subscription cancelled successfully');
    }

    /**
     * View subscription details
     */
    public function showSubscription($id)
    {
        $subscription = VendorSubscription::with([
            'vendorProfile.user',
            'plan',
            'payments'
        ])->findOrFail($id);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    /**
     * Display revenue analytics
     */
    public function revenue(Request $request)
    {
        $period = $request->input('period', '30');
        $startDate = now()->subDays((int)$period);

        // Revenue over time
        $revenueByDay = SubscriptionPayment::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue by plan
        $revenueByPlan = SubscriptionPayment::where('subscription_payments.status', 'completed')
            ->where('subscription_payments.created_at', '>=', $startDate)
            ->join('vendor_subscriptions', 'subscription_payments.vendor_subscription_id', '=', 'vendor_subscriptions.id')
            ->join('subscription_plans', 'vendor_subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
            ->selectRaw('subscription_plans.name, SUM(subscription_payments.amount) as total, COUNT(*) as count')
            ->groupBy('subscription_plans.id', 'subscription_plans.name')
            ->get();

        // Summary stats
        $stats = [
            'total_revenue' => SubscriptionPayment::where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->sum('amount'),
            'total_payments' => SubscriptionPayment::where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->count(),
            'average_payment' => SubscriptionPayment::where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->avg('amount') ?? 0,
            'active_subscriptions' => VendorSubscription::where('status', 'active')
                ->where('expires_at', '>', now())
                ->count(),
            'expiring_soon' => VendorSubscription::where('status', 'active')
                ->whereBetween('expires_at', [now(), now()->addDays(7)])
                ->count(),
        ];

        // Monthly recurring revenue estimate
        $activeByPlan = VendorSubscription::where('vendor_subscriptions.status', 'active')
            ->where('vendor_subscriptions.expires_at', '>', now())
            ->join('subscription_plans', 'vendor_subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
            ->selectRaw('subscription_plans.name, subscription_plans.price, COUNT(*) as count')
            ->groupBy('subscription_plans.id', 'subscription_plans.name', 'subscription_plans.price')
            ->get();

        $mrr = $activeByPlan->sum(function ($item) {
            return $item->price * $item->count;
        });

        $stats['mrr'] = $mrr;

        return view('admin.subscriptions.revenue', compact('revenueByDay', 'revenueByPlan', 'stats', 'period', 'activeByPlan'));
    }

    /**
     * Export subscription data
     */
    public function export(Request $request)
    {
        $subscriptions = VendorSubscription::with(['vendorProfile.user', 'plan'])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'subscriptions_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($subscriptions) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Vendor',
                'Email',
                'Plan',
                'Status',
                'Starts At',
                'Expires At',
                'Auto Renew',
                'Created At',
            ]);

            foreach ($subscriptions as $sub) {
                fputcsv($file, [
                    $sub->id,
                    $sub->vendorProfile?->business_name ?? 'N/A',
                    $sub->vendorProfile?->user?->email ?? 'N/A',
                    $sub->plan?->name ?? 'N/A',
                    $sub->status,
                    $sub->starts_at?->format('Y-m-d H:i'),
                    $sub->expires_at?->format('Y-m-d H:i'),
                    $sub->auto_renew ? 'Yes' : 'No',
                    $sub->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
