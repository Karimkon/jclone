@extends('layouts.admin')

@section('title', 'Subscription Details - ' . config('app.name'))
@section('page-title', 'Subscription Details')
@section('page-description', 'View subscription information')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Subscription #{{ $subscription->id }}</h1>
            <p class="text-gray-600">{{ $subscription->vendorProfile?->business_name ?? 'Unknown Vendor' }}</p>
        </div>
        <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Subscription Info -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="text-lg font-semibold">Subscription Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                @if($subscription->status == 'active') bg-green-100 text-green-800
                                @elseif($subscription->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($subscription->status == 'expired') bg-gray-100 text-gray-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($subscription->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Plan</p>
                            <p class="font-semibold text-lg">{{ $subscription->plan?->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Start Date</p>
                            <p class="font-medium">{{ $subscription->starts_at?->format('M d, Y H:i') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Expiry Date</p>
                            <p class="font-medium {{ $subscription->expires_at?->isPast() ? 'text-red-600' : '' }}">
                                {{ $subscription->expires_at?->format('M d, Y H:i') ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Days Remaining</p>
                            <p class="font-medium {{ $subscription->daysRemaining() <= 7 ? 'text-orange-600' : 'text-green-600' }}">
                                {{ $subscription->daysRemaining() }} days
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Auto Renew</p>
                            <p class="font-medium">
                                @if($subscription->auto_renew)
                                    <span class="text-green-600"><i class="fas fa-check-circle"></i> Enabled</span>
                                @else
                                    <span class="text-gray-500"><i class="fas fa-times-circle"></i> Disabled</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan Details -->
            @if($subscription->plan)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="text-lg font-semibold">Plan Details</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Price</p>
                            <p class="font-semibold text-lg">UGX {{ number_format($subscription->plan->price) }}/{{ $subscription->plan->billing_cycle }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Boost Multiplier</p>
                            <p class="font-semibold text-lg">{{ $subscription->plan->boost_multiplier }}x</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Max Featured Listings</p>
                            <p class="font-medium">{{ $subscription->plan->max_featured_listings == -1 ? 'Unlimited' : $subscription->plan->max_featured_listings }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Badge</p>
                            <p class="font-medium">
                                @if($subscription->plan->badge_enabled)
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">{{ $subscription->plan->badge_text }}</span>
                                @else
                                    <span class="text-gray-400">None</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Payment History -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="text-lg font-semibold">Payment History</h3>
                </div>
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($subscription->payments as $payment)
                        <tr>
                            <td class="px-6 py-4 text-sm font-mono">{{ $payment->pesapal_merchant_reference }}</td>
                            <td class="px-6 py-4 text-sm font-semibold">{{ $payment->currency }} {{ number_format($payment->amount) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($payment->status == 'completed') bg-green-100 text-green-800
                                    @elseif($payment->status == 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No payments found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Vendor Info -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="text-lg font-semibold">Vendor Information</h3>
                </div>
                <div class="p-6">
                    <div class="text-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-primary-100 flex items-center justify-center mx-auto mb-3">
                            <span class="text-2xl font-bold text-primary-600">
                                {{ substr($subscription->vendorProfile?->business_name ?? 'V', 0, 1) }}
                            </span>
                        </div>
                        <h4 class="font-semibold text-lg">{{ $subscription->vendorProfile?->business_name ?? 'Unknown' }}</h4>
                        <p class="text-sm text-gray-500">{{ $subscription->vendorProfile?->user?->email ?? '' }}</p>
                    </div>
                    @if($subscription->vendorProfile)
                    <div class="border-t pt-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Vendor Type</span>
                            <span class="font-medium">{{ ucfirst($subscription->vendorProfile->vendor_type ?? 'N/A') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Status</span>
                            <span class="font-medium">{{ ucfirst($subscription->vendorProfile->vetting_status ?? 'N/A') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Listings</span>
                            <span class="font-medium">{{ $subscription->vendorProfile->listings()->count() }}</span>
                        </div>
                    </div>
                    <a href="{{ route('admin.vendors.show', $subscription->vendorProfile->id) }}"
                       class="mt-4 block w-full text-center px-4 py-2 bg-primary-100 text-primary-700 rounded hover:bg-primary-200">
                        View Vendor Profile
                    </a>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="text-lg font-semibold">Quick Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    @if($subscription->status == 'active')
                    <form action="{{ route('admin.subscriptions.extend', $subscription->id) }}" method="POST" class="space-y-2">
                        @csrf
                        <div class="flex gap-2">
                            <input type="number" name="days" min="1" max="365" value="30" placeholder="Days"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded text-sm">
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                Extend
                            </button>
                        </div>
                    </form>
                    <form action="{{ route('admin.subscriptions.cancel', $subscription->id) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to cancel this subscription?')">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                            <i class="fas fa-times mr-1"></i>Cancel Subscription
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
