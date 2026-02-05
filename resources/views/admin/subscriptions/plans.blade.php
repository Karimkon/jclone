@extends('layouts.admin')

@section('title', 'Subscription Plans - ' . config('app.name'))
@section('page-title', 'Subscription Plans')
@section('page-description', 'Manage subscription plans and pricing')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Subscription Plans</h1>
            <p class="text-gray-600">Configure vendor subscription tiers and pricing</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
            <button onclick="openCreateModal()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                <i class="fas fa-plus mr-2"></i>Add Plan
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        {{ session('error') }}
    </div>
    @endif

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach($plans as $plan)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden {{ !$plan->is_active ? 'opacity-60' : '' }}">
            <div class="p-6 {{ $plan->slug == 'gold' ? 'bg-gradient-to-r from-yellow-400 to-yellow-500' : ($plan->slug == 'silver' ? 'bg-gradient-to-r from-gray-300 to-gray-400' : ($plan->slug == 'bronze' ? 'bg-gradient-to-r from-orange-400 to-orange-500' : 'bg-gradient-to-r from-primary-500 to-primary-600')) }}">
                <h3 class="text-xl font-bold text-white">{{ $plan->name }}</h3>
                <div class="mt-2">
                    <span class="text-3xl font-bold text-white">UGX {{ number_format($plan->price) }}</span>
                    <span class="text-white opacity-75">/{{ $plan->billing_cycle }}</span>
                </div>
            </div>

            <div class="p-6">
                <ul class="space-y-3 mb-6">
                    <li class="flex items-center text-sm">
                        <i class="fas fa-rocket text-primary-500 mr-2"></i>
                        <span>{{ $plan->boost_multiplier }}x Ranking Boost</span>
                    </li>
                    <li class="flex items-center text-sm">
                        <i class="fas fa-star text-primary-500 mr-2"></i>
                        <span>{{ $plan->max_featured_listings == -1 ? 'Unlimited' : $plan->max_featured_listings }} Featured Listings</span>
                    </li>
                    <li class="flex items-center text-sm">
                        @if($plan->badge_enabled)
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>{{ $plan->badge_text ?: 'Badge Enabled' }}</span>
                        @else
                            <i class="fas fa-times text-gray-400 mr-2"></i>
                            <span class="text-gray-400">No Badge</span>
                        @endif
                    </li>
                </ul>

                <!-- Stats -->
                @php
                    $planStat = $planStats->firstWhere('id', $plan->id);
                @endphp
                <div class="border-t pt-4 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Active Subscribers</span>
                        <span class="font-semibold">{{ $planStat['active_subscribers'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between text-sm mt-1">
                        <span class="text-gray-500">Total Revenue</span>
                        <span class="font-semibold">UGX {{ number_format($planStat['total_revenue'] ?? 0) }}</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex space-x-2">
                    <button onclick="openEditModal({{ $plan->id }}, {{ json_encode($plan) }})"
                            class="flex-1 px-3 py-2 bg-primary-100 text-primary-700 rounded hover:bg-primary-200 text-sm">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <form action="{{ route('admin.subscriptions.plans.toggle', $plan->id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 {{ $plan->is_active ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} rounded text-sm">
                            <i class="fas {{ $plan->is_active ? 'fa-pause' : 'fa-play' }} mr-1"></i>
                            {{ $plan->is_active ? 'Disable' : 'Enable' }}
                        </button>
                    </form>
                    @if(($planStat['active_subscribers'] ?? 0) == 0)
                    <form action="{{ route('admin.subscriptions.plans.destroy', $plan->id) }}" method="POST"
                          onsubmit="return confirm('Delete this plan?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Feature Comparison Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">Plan Comparison</h3>
        </div>
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Feature</th>
                    @foreach($plans as $plan)
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ $plan->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Price (Monthly)</td>
                    @foreach($plans as $plan)
                    <td class="px-6 py-4 text-sm text-center font-medium">UGX {{ number_format($plan->price) }}</td>
                    @endforeach
                </tr>
                <tr class="bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">Ranking Boost</td>
                    @foreach($plans as $plan)
                    <td class="px-6 py-4 text-sm text-center">{{ $plan->boost_multiplier }}x</td>
                    @endforeach
                </tr>
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Featured Listings</td>
                    @foreach($plans as $plan)
                    <td class="px-6 py-4 text-sm text-center">{{ $plan->max_featured_listings == -1 ? 'Unlimited' : $plan->max_featured_listings }}</td>
                    @endforeach
                </tr>
                <tr class="bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">Seller Badge</td>
                    @foreach($plans as $plan)
                    <td class="px-6 py-4 text-sm text-center">
                        @if($plan->badge_enabled)
                            <span class="text-green-600"><i class="fas fa-check"></i></span>
                        @else
                            <span class="text-gray-400"><i class="fas fa-times"></i></span>
                        @endif
                    </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Plan Modal -->
<div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg max-h-screen overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">Create Subscription Plan</h3>
        <form action="{{ route('admin.subscriptions.plans.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plan Name</label>
                    <input type="text" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price (UGX)</label>
                        <input type="number" name="price" min="0" step="0.01" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Billing Cycle</label>
                        <select name="billing_cycle" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Boost Multiplier</label>
                        <input type="number" name="boost_multiplier" min="1" max="10" step="0.1" value="1" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Featured Listings</label>
                        <input type="number" name="max_featured_listings" min="-1" value="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        <p class="text-xs text-gray-500 mt-1">Use -1 for unlimited</p>
                    </div>
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="badge_enabled" value="1" class="mr-2">
                        <span class="text-sm font-medium text-gray-700">Enable Seller Badge</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Badge Text</label>
                    <input type="text" name="badge_text" placeholder="e.g., Gold Seller"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" min="0" value="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
            </div>
            <div class="flex justify-end space-x-2 mt-6">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">Create Plan</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Plan Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg max-h-screen overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">Edit Subscription Plan</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plan Name</label>
                    <input type="text" name="name" id="edit_name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price (UGX)</label>
                        <input type="number" name="price" id="edit_price" min="0" step="0.01" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Billing Cycle</label>
                        <select name="billing_cycle" id="edit_billing_cycle" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Boost Multiplier</label>
                        <input type="number" name="boost_multiplier" id="edit_boost_multiplier" min="1" max="10" step="0.1" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Featured Listings</label>
                        <input type="number" name="max_featured_listings" id="edit_max_featured_listings" min="-1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="badge_enabled" id="edit_badge_enabled" value="1" class="mr-2">
                        <span class="text-sm font-medium text-gray-700">Enable Seller Badge</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Badge Text</label>
                    <input type="text" name="badge_text" id="edit_badge_text"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="mr-2">
                        <span class="text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" id="edit_sort_order" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
            </div>
            <div class="flex justify-end space-x-2 mt-6">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
    document.getElementById('createModal').classList.add('flex');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.getElementById('createModal').classList.remove('flex');
}

function openEditModal(planId, plan) {
    document.getElementById('editForm').action = '/admin/subscription-plans/' + planId;
    document.getElementById('edit_name').value = plan.name;
    document.getElementById('edit_price').value = plan.price;
    document.getElementById('edit_billing_cycle').value = plan.billing_cycle;
    document.getElementById('edit_boost_multiplier').value = plan.boost_multiplier;
    document.getElementById('edit_max_featured_listings').value = plan.max_featured_listings;
    document.getElementById('edit_badge_enabled').checked = plan.badge_enabled;
    document.getElementById('edit_badge_text').value = plan.badge_text || '';
    document.getElementById('edit_is_active').checked = plan.is_active;
    document.getElementById('edit_sort_order').value = plan.sort_order;

    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
}
</script>
@endsection
