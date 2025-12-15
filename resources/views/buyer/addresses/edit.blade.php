@extends('layouts.buyer')

@section('title', 'Edit Shipping Address - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Shipping Address</h1>
            <a href="{{ route('buyer.addresses.index') }}" 
               class="text-gray-600 hover:text-gray-800">
                ← Back to Addresses
            </a>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <ul class="list-disc list-inside text-red-700">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm p-6">
            <form action="{{ route('buyer.addresses.update', $address->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Label (Optional) -->
                    <div class="md:col-span-2">
                        <label for="label" class="block text-sm font-medium text-gray-700 mb-1">
                            Address Label (Optional)
                        </label>
                        <input type="text" 
                               id="label" 
                               name="label" 
                               value="{{ old('label', $address->label) }}"
                               placeholder="e.g., Home, Office"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <!-- Recipient Name -->
                    <div>
                        <label for="recipient_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Recipient Name *
                        </label>
                        <input type="text" 
                               id="recipient_name" 
                               name="recipient_name" 
                               value="{{ old('recipient_name', $address->recipient_name) }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="recipient_phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Phone Number *
                        </label>
                        <input type="tel" 
                               id="recipient_phone" 
                               name="recipient_phone" 
                               value="{{ old('recipient_phone', $address->recipient_phone) }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <!-- Address Line 1 -->
                    <div class="md:col-span-2">
                        <label for="address_line_1" class="block text-sm font-medium text-gray-700 mb-1">
                            Address Line 1 *
                        </label>
                        <input type="text" 
                               id="address_line_1" 
                               name="address_line_1" 
                               value="{{ old('address_line_1', $address->address_line_1) }}"
                               required
                               placeholder="Street address, P.O. box"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <!-- Address Line 2 -->
                    <div class="md:col-span-2">
                        <label for="address_line_2" class="block text-sm font-medium text-gray-700 mb-1">
                            Address Line 2 (Optional)
                        </label>
                        <input type="text" 
                               id="address_line_2" 
                               name="address_line_2" 
                               value="{{ old('address_line_2', $address->address_line_2) }}"
                               placeholder="Apartment, suite, unit, building, floor"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <!-- City -->
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">
                            City *
                        </label>
                        <input type="text" 
                               id="city" 
                               name="city" 
                               value="{{ old('city', $address->city) }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <!-- State/Region -->
                    <div>
                        <label for="state_region" class="block text-sm font-medium text-gray-700 mb-1">
                            State/Region
                        </label>
                        <input type="text" 
                               id="state_region" 
                               name="state_region" 
                               value="{{ old('state_region', $address->state_region) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <!-- Postal Code -->
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">
                            Postal/ZIP Code
                        </label>
                        <input type="text" 
                               id="postal_code" 
                               name="postal_code" 
                               value="{{ old('postal_code', $address->postal_code) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <!-- Country -->
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-1">
                            Country *
                        </label>
                        <select id="country" 
                                name="country" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select Country</option>
                            <option value="Uganda" {{ old('country', $address->country) == 'Uganda' ? 'selected' : '' }}>Uganda</option>
                            <option value="Kenya" {{ old('country', $address->country) == 'Kenya' ? 'selected' : '' }}>Kenya</option>
                            <option value="Tanzania" {{ old('country', $address->country) == 'Tanzania' ? 'selected' : '' }}>Tanzania</option>
                            <option value="Rwanda" {{ old('country', $address->country) == 'Rwanda' ? 'selected' : '' }}>Rwanda</option>
                            <option value="Burundi" {{ old('country', $address->country) == 'Burundi' ? 'selected' : '' }}>Burundi</option>
                            <option value="South Sudan" {{ old('country', $address->country) == 'South Sudan' ? 'selected' : '' }}>South Sudan</option>
                            <option value="Ethiopia" {{ old('country', $address->country) == 'Ethiopia' ? 'selected' : '' }}>Ethiopia</option>
                            <option value="DR Congo" {{ old('country', $address->country) == 'DR Congo' ? 'selected' : '' }}>DR Congo</option>
                            <option value="Other" {{ old('country', $address->country) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <!-- Delivery Instructions -->
                    <div class="md:col-span-2">
                        <label for="delivery_instructions" class="block text-sm font-medium text-gray-700 mb-1">
                            Delivery Instructions (Optional)
                        </label>
                        <textarea id="delivery_instructions" 
                                  name="delivery_instructions" 
                                  rows="3"
                                  placeholder="Any special instructions for delivery"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('delivery_instructions', $address->delivery_instructions) }}</textarea>
                    </div>

                    <!-- Set as Default -->
                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_default" 
                                   value="1"
                                   {{ $address->is_default ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">
                                Set as default shipping address
                            </span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-3 mt-8 pt-6 border-t border-gray-100">
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                        Update Address
                    </button>
                    <a href="{{ route('buyer.addresses.index') }}" 
                       class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-medium">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection