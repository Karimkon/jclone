@extends('layouts.vendor')

@section('title', 'Subscription - BebaMart')
@section('page_title', 'Subscription Plans')
@section('page_description', 'Boost your visibility and grow your business')

@section('content')
<div class="space-y-6">
    <!-- Current Plan Banner -->
    @if($currentSubscription && $currentSubscription->plan)
        @php
            $plan = $currentSubscription->plan;
            $isExpiringSoon = $currentSubscription->isExpiringSoon(7);
            $daysRemaining = $currentSubscription->daysRemaining();
        @endphp
        <div class="bg-gradient-to-r {{ $plan->slug == 'gold' ? 'from-yellow-500 to-amber-600' : ($plan->slug == 'silver' ? 'from-gray-400 to-gray-600' : ($plan->slug == 'bronze' ? 'from-orange-500 to-orange-700' : 'from-indigo-600 to-purple-600')) }} text-white rounded-2xl p-6 shadow-lg">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        @if($plan->badge_enabled)
                            <span class="px-3 py-1 bg-white/20 backdrop-blur rounded-full text-sm font-semibold">
                                <i class="fas fa-crown mr-1"></i>{{ $plan->badge_text ?? $plan->name }}
                            </span>
                        @endif
                        <span class="px-3 py-1 bg-white/20 backdrop-blur rounded-full text-sm">
                            {{ $plan->boost_multiplier }}x Visibility Boost
                        </span>
                    </div>
                    <h2 class="text-2xl font-bold mb-1">{{ $plan->name }} Plan</h2>
                    <p class="text-white/90">
                        @if($plan->is_free_plan)
                            Your current plan - Free forever
                        @else
                            @if($isExpiringSoon)
                                <span class="text-yellow-200"><i class="fas fa-exclamation-triangle mr-1"></i>Expires in {{ $daysRemaining }} days</span>
                            @else
                                Expires: {{ $currentSubscription->expires_at->format('M d, Y') }}
                            @endif
                        @endif
                    </p>
                </div>
                <div class="mt-4 md:mt-0 text-right">
                    @if(!$plan->is_free_plan)
                        <p class="text-3xl font-bold">UGX {{ number_format($plan->price) }}</p>
                        <p class="text-white/80 text-sm">per {{ $plan->billing_cycle }}</p>
                        <div class="mt-2">
                            <form action="{{ route('vendor.subscription.toggle-auto-renew') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-sm px-3 py-1 bg-white/20 hover:bg-white/30 rounded-lg transition">
                                    Auto-renew: {{ $currentSubscription->auto_renew ? 'ON' : 'OFF' }}
                                    <i class="fas fa-{{ $currentSubscription->auto_renew ? 'toggle-on' : 'toggle-off' }} ml-1"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="bg-gradient-to-r from-gray-600 to-gray-800 text-white rounded-2xl p-6 shadow-lg">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-1">No Active Subscription</h2>
                    <p class="text-white/90">Choose a plan below to boost your visibility</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="px-4 py-2 bg-white/20 rounded-lg text-sm">
                        <i class="fas fa-info-circle mr-1"></i>Basic visibility
                    </span>
                </div>
            </div>
        </div>
    @endif

    <!-- Benefits Overview -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Why Upgrade?</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="flex items-start gap-3 p-4 bg-indigo-50 rounded-lg">
                <div class="bg-indigo-100 p-2 rounded-lg">
                    <i class="fas fa-rocket text-indigo-600"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-800">Higher Visibility</h4>
                    <p class="text-sm text-gray-600">Appear higher in search results</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 bg-green-50 rounded-lg">
                <div class="bg-green-100 p-2 rounded-lg">
                    <i class="fas fa-star text-green-600"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-800">Featured Badge</h4>
                    <p class="text-sm text-gray-600">Stand out from competitors</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 bg-purple-50 rounded-lg">
                <div class="bg-purple-100 p-2 rounded-lg">
                    <i class="fas fa-bullhorn text-purple-600"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-800">Featured Listings</h4>
                    <p class="text-sm text-gray-600">Promote your best products</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 bg-orange-50 rounded-lg">
                <div class="bg-orange-100 p-2 rounded-lg">
                    <i class="fas fa-chart-line text-orange-600"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-800">More Sales</h4>
                    <p class="text-sm text-gray-600">Reach more customers</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription Plans -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-6">Choose Your Plan</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($plans as $plan)
                @php
                    $isCurrentPlan = $currentSubscription && $currentSubscription->subscription_plan_id == $plan->id && $currentSubscription->status == 'active';
                    $planColors = [
                        'free' => ['border' => 'border-gray-200', 'bg' => 'bg-gray-50', 'btn' => 'bg-gray-600 hover:bg-gray-700', 'badge' => 'bg-gray-100 text-gray-800'],
                        'bronze' => ['border' => 'border-orange-200', 'bg' => 'bg-orange-50', 'btn' => 'bg-orange-600 hover:bg-orange-700', 'badge' => 'bg-orange-100 text-orange-800'],
                        'silver' => ['border' => 'border-gray-300', 'bg' => 'bg-gray-100', 'btn' => 'bg-gray-600 hover:bg-gray-700', 'badge' => 'bg-gray-200 text-gray-800'],
                        'gold' => ['border' => 'border-yellow-300', 'bg' => 'bg-yellow-50', 'btn' => 'bg-yellow-600 hover:bg-yellow-700', 'badge' => 'bg-yellow-100 text-yellow-800'],
                    ];
                    $colors = $planColors[$plan->slug] ?? $planColors['free'];
                @endphp

                <div class="relative border-2 {{ $isCurrentPlan ? 'border-indigo-500 ring-2 ring-indigo-200' : $colors['border'] }} rounded-2xl p-6 {{ $colors['bg'] }} transition-all hover:shadow-lg {{ $plan->slug == 'gold' ? 'transform md:-translate-y-2' : '' }}">
                    @if($plan->slug == 'gold')
                        <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                            <span class="px-4 py-1 bg-yellow-500 text-white text-xs font-bold rounded-full shadow-lg">
                                MOST POPULAR
                            </span>
                        </div>
                    @endif

                    @if($isCurrentPlan)
                        <div class="absolute -top-3 right-4">
                            <span class="px-3 py-1 bg-indigo-600 text-white text-xs font-bold rounded-full">
                                CURRENT
                            </span>
                        </div>
                    @endif

                    <div class="text-center mb-4">
                        <h4 class="text-xl font-bold text-gray-800 mb-1">{{ $plan->name }}</h4>
                        @if($plan->badge_enabled && $plan->badge_text)
                            <span class="inline-block px-2 py-0.5 {{ $colors['badge'] }} rounded-full text-xs font-medium">
                                {{ $plan->badge_text }}
                            </span>
                        @endif
                    </div>

                    <div class="text-center mb-6">
                        @if($plan->is_free_plan)
                            <p class="text-3xl font-bold text-gray-800">Free</p>
                            <p class="text-gray-500 text-sm">Forever</p>
                        @else
                            <p class="text-3xl font-bold text-gray-800">UGX {{ number_format($plan->price) }}</p>
                            <p class="text-gray-500 text-sm">per {{ $plan->billing_cycle }}</p>
                        @endif
                    </div>

                    <ul class="space-y-3 mb-6">
                        <li class="flex items-center text-sm text-gray-700">
                            <i class="fas fa-check text-green-500 mr-2 w-4"></i>
                            <span><strong>{{ $plan->boost_multiplier }}x</strong> Visibility Boost</span>
                        </li>
                        <li class="flex items-center text-sm text-gray-700">
                            <i class="fas fa-{{ $plan->max_featured_listings > 0 ? 'check text-green-500' : 'times text-gray-400' }} mr-2 w-4"></i>
                            @if($plan->max_featured_listings == -1 || $plan->max_featured_listings > 100)
                                <span>Unlimited Featured Listings</span>
                            @elseif($plan->max_featured_listings > 0)
                                <span>{{ $plan->max_featured_listings }} Featured Listings</span>
                            @else
                                <span class="text-gray-500">No Featured Listings</span>
                            @endif
                        </li>
                        <li class="flex items-center text-sm text-gray-700">
                            <i class="fas fa-{{ $plan->badge_enabled ? 'check text-green-500' : 'times text-gray-400' }} mr-2 w-4"></i>
                            @if($plan->badge_enabled)
                                <span>{{ $plan->badge_text ?? 'Seller Badge' }}</span>
                            @else
                                <span class="text-gray-500">No Seller Badge</span>
                            @endif
                        </li>
                        @if($plan->features)
                            @foreach($plan->features as $feature)
                                <li class="flex items-center text-sm text-gray-700">
                                    <i class="fas fa-check text-green-500 mr-2 w-4"></i>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        @endif
                    </ul>

                    @if($isCurrentPlan)
                        <button disabled class="w-full py-3 bg-gray-300 text-gray-600 rounded-xl font-semibold cursor-not-allowed">
                            Current Plan
                        </button>
                    @else
                        <form action="{{ route('vendor.subscription.subscribe') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <button type="submit" class="w-full py-3 {{ $colors['btn'] }} text-white rounded-xl font-semibold transition shadow-lg hover:shadow-xl">
                                @if($plan->is_free_plan)
                                    Select Free Plan
                                @elseif($currentSubscription && !$currentSubscription->plan->is_free_plan)
                                    @if($plan->price > $currentSubscription->plan->price)
                                        Upgrade Now
                                    @else
                                        Switch Plan
                                    @endif
                                @else
                                    Subscribe Now
                                @endif
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Payment History -->
    @if($paymentHistory && $paymentHistory->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Payment History</h3>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($paymentHistory as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ $payment->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-800">
                                    {{ $payment->vendorSubscription?->plan?->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800">
                                    {{ $payment->currency }} {{ number_format($payment->amount) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500 font-mono">
                                    {{ Str::limit($payment->pesapal_merchant_reference, 20) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'completed' => 'bg-green-100 text-green-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'failed' => 'bg-red-100 text-red-800',
                                            'refunded' => 'bg-gray-100 text-gray-800',
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$payment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- FAQ -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Frequently Asked Questions</h3>

        <div class="space-y-4">
            <details class="group border border-gray-200 rounded-lg">
                <summary class="flex items-center justify-between p-4 cursor-pointer">
                    <span class="font-medium text-gray-800">How does the visibility boost work?</span>
                    <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="px-4 pb-4 text-gray-600 text-sm">
                    Your products will appear higher in search results and category listings based on your plan's boost multiplier. Gold members get 3x boost, Silver gets 2x, and Bronze gets 1.5x visibility compared to free vendors.
                </div>
            </details>

            <details class="group border border-gray-200 rounded-lg">
                <summary class="flex items-center justify-between p-4 cursor-pointer">
                    <span class="font-medium text-gray-800">Can I upgrade or downgrade my plan?</span>
                    <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="px-4 pb-4 text-gray-600 text-sm">
                    Yes! You can change your plan at any time. When upgrading, your new plan starts immediately. When downgrading, you'll continue with your current plan until it expires.
                </div>
            </details>

            <details class="group border border-gray-200 rounded-lg">
                <summary class="flex items-center justify-between p-4 cursor-pointer">
                    <span class="font-medium text-gray-800">What payment methods are accepted?</span>
                    <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="px-4 pb-4 text-gray-600 text-sm">
                    We accept Mobile Money (MTN, Airtel) and card payments through our secure Pesapal payment gateway.
                </div>
            </details>

            <details class="group border border-gray-200 rounded-lg">
                <summary class="flex items-center justify-between p-4 cursor-pointer">
                    <span class="font-medium text-gray-800">How do I cancel my subscription?</span>
                    <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="px-4 pb-4 text-gray-600 text-sm">
                    You can turn off auto-renewal at any time using the toggle in your current plan section above. Your subscription will remain active until the expiry date.
                </div>
            </details>
        </div>
    </div>
</div>
@endsection
