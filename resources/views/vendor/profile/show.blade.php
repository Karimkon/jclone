@extends('layouts.vendor')

@section('title', 'Profile - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Vendor Profile</h1>
            <p class="text-gray-600">Manage your business information</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Profile Info -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Business Information</h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('vendor.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Business Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Business Name *
                                </label>
                                <input type="text" name="business_name" required
                                       class="w-full border border-gray-300 rounded-lg p-3"
                                       value="{{ old('business_name', $vendor->business_name) }}">
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number *
                                </label>
                                <input type="tel" name="phone" required
                                       class="w-full border border-gray-300 rounded-lg p-3"
                                       value="{{ old('phone', $user->phone) }}">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address
                                </label>
                                <input type="email" disabled
                                       class="w-full border border-gray-300 rounded-lg p-3 bg-gray-50"
                                       value="{{ $user->email }}">
                                <p class="text-sm text-gray-500 mt-1">Contact admin to change email</p>
                            </div>

                            <!-- Vendor Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Vendor Type
                                </label>
                                <input type="text" disabled
                                       class="w-full border border-gray-300 rounded-lg p-3 bg-gray-50"
                                       value="{{ ucfirst(str_replace('_', ' ', $vendor->vendor_type)) }}">
                            </div>

                            <!-- Address -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Business Address *
                                </label>
                                <textarea name="address" rows="3" required
                                          class="w-full border border-gray-300 rounded-lg p-3">{{ old('address', $vendor->address) }}</textarea>
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Business Description
                                </label>
                                <textarea name="description" rows="4"
                                          class="w-full border border-gray-300 rounded-lg p-3"
                                          placeholder="Describe your business...">{{ old('description', $vendor->meta['description'] ?? '') }}</textarea>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="mt-8 flex justify-end">
                            <button type="submit"
                                    class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                                <i class="fas fa-save mr-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Verification Status -->
            <div class="bg-white rounded-lg shadow mt-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Verification Status</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-gray-900">
                                Status: 
                                <span class="ml-2 px-3 py-1 text-sm font-medium rounded-full 
                                    @if($vendor->vetting_status == 'approved') bg-green-100 text-green-800
                                    @elseif($vendor->vetting_status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($vendor->vetting_status == 'rejected') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($vendor->vetting_status) }}
                                </span>
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                @if($vendor->vetting_status == 'approved')
                                    Your vendor account is fully verified and active.
                                @elseif($vendor->vetting_status == 'pending')
                                    Your application is under review. Usually takes 24-48 hours.
                                @elseif($vendor->vetting_status == 'rejected')
                                    Your application was rejected. Please contact support.
                                @endif
                            </p>
                        </div>
                        
                        @if($vendor->vetting_status == 'approved')
                        <div class="text-green-600">
                            <i class="fas fa-check-circle text-3xl"></i>
                        </div>
                        @endif
                    </div>

                    @if($vendor->vetting_notes)
                    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="font-semibold text-blue-800 mb-2">Admin Notes:</h4>
                        <p class="text-blue-700">{{ $vendor->vetting_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Stats & Actions -->
        <div class="space-y-6">
            <!-- Account Stats -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Account Stats</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600">Joined Date</p>
                            <p class="font-medium text-gray-900">{{ $vendor->created_at->format('F d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Account ID</p>
                            <p class="font-medium text-gray-900">VENDOR-{{ str_pad($vendor->id, 6, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Country</p>
                            <p class="font-medium text-gray-900">{{ $vendor->country }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Currency</p>
                            <p class="font-medium text-gray-900">{{ $vendor->preferred_currency }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Quick Actions</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="{{ route('vendor.listings.create') }}" 
                           class="flex items-center p-3 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition">
                            <i class="fas fa-plus-circle mr-3"></i>
                            <span>Add New Product</span>
                        </a>
                        
                        <a href="{{ route('vendor.orders.index') }}" 
                           class="flex items-center p-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition">
                            <i class="fas fa-shopping-bag mr-3"></i>
                            <span>View Orders</span>
                        </a>
                        
                        <a href="{{ route('vendor.promotions.index') }}" 
                           class="flex items-center p-3 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition">
                            <i class="fas fa-bullhorn mr-3"></i>
                            <span>Manage Promotions</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Support -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Support</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600">Need Help?</p>
                            <p class="text-sm text-gray-900 mt-1">
                                Contact our support team for assistance with your vendor account.
                            </p>
                        </div>
                        
                        <div class="space-y-2">
                            <a href="mailto:support@jclone.com" 
                               class="flex items-center text-gray-700 hover:text-primary">
                                <i class="fas fa-envelope mr-3"></i>
                                support@jclone.com
                            </a>
                            
                            <a href="{{ route('vendor.orders.index') }}" 
                               class="flex items-center text-gray-700 hover:text-primary">
                                <i class="fas fa-question-circle mr-3"></i>
                                View Help Center
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection