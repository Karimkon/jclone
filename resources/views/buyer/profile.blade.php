@extends('layouts.buyer')

@section('title', 'My Profile - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">My Profile</h1>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
        <p class="text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
        <ul class="list-disc list-inside text-red-700">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Personal Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Basic Information</h2>
                
                <form action="{{ route('buyer.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Full Name *</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Email *</label>
                                <input type="email" value="{{ $user->email }}" disabled
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                                <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Phone Number *</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="+256 XXX XXX XXX">
                            @error('phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                                <i class="fas fa-save mr-2"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Address Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Shipping Address</h2>
                
                <form action="{{ route('buyer.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Full Address</label>
                            <textarea name="address" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                      placeholder="Street address, building, apartment">{{ old('address', $addresses[0]['address'] ?? '') }}</textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">City</label>
                                <input type="text" name="city" value="{{ old('city', $addresses[0]['city'] ?? '') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Country</label>
                                <select name="country"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="">Select Country</option>
                                    <option value="Uganda" {{ old('country', $addresses[0]['country'] ?? '') == 'Uganda' ? 'selected' : '' }}>Uganda</option>
                                    <option value="Kenya" {{ old('country', $addresses[0]['country'] ?? '') == 'Kenya' ? 'selected' : '' }}>Kenya</option>
                                    <option value="Tanzania" {{ old('country', $addresses[0]['country'] ?? '') == 'Tanzania' ? 'selected' : '' }}>Tanzania</option>
                                    <option value="Rwanda" {{ old('country', $addresses[0]['country'] ?? '') == 'Rwanda' ? 'selected' : '' }}>Rwanda</option>
                                    <option value="Burundi" {{ old('country', $addresses[0]['country'] ?? '') == 'Burundi' ? 'selected' : '' }}>Burundi</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Postal Code</label>
                                <input type="text" name="postal_code" value="{{ old('postal_code', $addresses[0]['postal_code'] ?? '') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                                <i class="fas fa-save mr-2"></i> Save Address
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Change Password -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Change Password</h2>
                
                <form action="{{ route('buyer.profile.change-password') }}" method="POST">
                    @csrf
                    @method('POST')
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Current Password *</label>
                            <input type="password" name="current_password" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            @error('current_password')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">New Password *</label>
                                <input type="password" name="new_password" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                @error('new_password')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Confirm New Password *</label>
                                <input type="password" name="new_password_confirmation" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                                <i class="fas fa-key mr-2"></i> Change Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Right Column: Account Info & Stats -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Account Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Account Information</h2>
                
                <div class="space-y-3">
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                        <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-user text-xl"></i>
                        </div>
                        <div>
                            <div class="font-bold text-gray-800">{{ $user->name }}</div>
                            <div class="text-sm text-gray-600">{{ $user->email }}</div>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Account Type:</span>
                            <span class="font-medium capitalize">{{ $user->role }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Joined:</span>
                            <span class="font-medium">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Status:</span>
                            <span class="font-medium text-green-600">
                                <i class="fas fa-check-circle mr-1"></i> Active
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Statistics -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Order Statistics</h2>
                
                <div class="space-y-3">
                    @php
                        $orders = $user->orders;
                        $totalOrders = $orders->count();
                        $pendingOrders = $orders->where('status', 'pending')->count();
                        $completedOrders = $orders->whereIn('status', ['delivered', 'completed'])->count();
                    @endphp
                    
                    <div class="flex justify-between items-center p-3 hover:bg-gray-50 rounded-lg">
                        <span class="text-gray-700">Total Orders</span>
                        <span class="font-bold text-lg text-primary">{{ $totalOrders }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center p-3 hover:bg-gray-50 rounded-lg">
                        <span class="text-gray-700">Pending Orders</span>
                        <span class="font-bold text-lg text-yellow-600">{{ $pendingOrders }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center p-3 hover:bg-gray-50 rounded-lg">
                        <span class="text-gray-700">Completed Orders</span>
                        <span class="font-bold text-lg text-green-600">{{ $completedOrders }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Links</h2>
                
                <div class="space-y-2">
                    <a href="{{ route('buyer.orders.index') }}" 
                       class="flex items-center p-3 hover:bg-gray-50 rounded-lg">
                        <i class="fas fa-shopping-bag text-gray-600 mr-3"></i>
                        <span class="font-medium text-gray-800">My Orders</span>
                        <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
                    </a>
                    
                    <a href="{{ route('buyer.wallet.index') }}" 
                       class="flex items-center p-3 hover:bg-gray-50 rounded-lg">
                        <i class="fas fa-wallet text-gray-600 mr-3"></i>
                        <span class="font-medium text-gray-800">My Wallet</span>
                        <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
                    </a>
                    
                    <a href="{{ route('buyer.cart.index') }}" 
                       class="flex items-center p-3 hover:bg-gray-50 rounded-lg">
                        <i class="fas fa-shopping-cart text-gray-600 mr-3"></i>
                        <span class="font-medium text-gray-800">Shopping Cart</span>
                        <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
                    </a>
                    
                    <a href="{{ route('buyer.wishlist.index') }}" 
                       class="flex items-center p-3 hover:bg-gray-50 rounded-lg">
                        <i class="fas fa-heart text-gray-600 mr-3"></i>
                        <span class="font-medium text-gray-800">My Wishlist</span>
                        <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
                    </a>
                </div>
            </div>
            
            <!-- Account Security -->
            <div class="bg-gradient-to-r from-primary to-indigo-600 text-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold mb-4">Account Security</h2>
                
                <div class="space-y-3">
                    <div class="flex items-center">
                        <i class="fas fa-shield-alt text-xl mr-3"></i>
                        <div>
                            <div class="font-medium">Two-Factor Authentication</div>
                            <div class="text-sm opacity-80">Add extra security to your account</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <i class="fas fa-lock text-xl mr-3"></i>
                        <div>
                            <div class="font-medium">Password Strength</div>
                            <div class="text-sm opacity-80">Last changed {{ $user->updated_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-white/20">
                        <a href="#" class="text-sm hover:underline flex items-center">
                            <i class="fas fa-question-circle mr-2"></i>
                            Learn about account security
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Phone number formatting
    const phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (!value.startsWith('+')) {
                    // Assume Uganda number if no country code
                    if (value.startsWith('0')) {
                        value = '+256' + value.substring(1);
                    } else if (value.length === 9 && !value.startsWith('0')) {
                        value = '+256' + value;
                    }
                }
            }
            e.target.value = value;
        });
    }
    
    // Password strength indicator
    const newPasswordInput = document.querySelector('input[name="new_password"]');
    if (newPasswordInput) {
        const strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'text-xs mt-1';
        newPasswordInput.parentNode.appendChild(strengthIndicator);
        
        newPasswordInput.addEventListener('input', function(e) {
            const password = e.target.value;
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            const strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'][strength];
            const strengthColors = ['text-red-500', 'text-orange-500', 'text-yellow-500', 'text-blue-500', 'text-green-500'];
            
            strengthIndicator.textContent = `Strength: ${strengthText}`;
            strengthIndicator.className = `text-xs mt-1 ${strengthColors[strength]}`;
        });
    }
});
</script>
@endsection