@extends('layouts.admin')

@section('title', 'Create User - ' . config('app.name'))
@section('page-title', 'Create New User')
@section('page-description', 'Add a new user to the system')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">
                        <i class="fas fa-home mr-2"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <a href="{{ route('admin.users.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-primary md:ml-2">Users</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Create User</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold text-gray-900">Create New User</h2>
                <p class="text-gray-600 mt-1">Add a new user account to the system</p>
            </div>
            
            <div class="p-6">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    
                    <div class="space-y-6">
                        <!-- Personal Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Full Name *
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           id="name"
                                           required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                           value="{{ old('name') }}">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                        Phone Number
                                    </label>
                                    <input type="text" 
                                           name="phone" 
                                           id="phone"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                           value="{{ old('phone') }}">
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Account Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                        Email Address *
                                    </label>
                                    <input type="email" 
                                           name="email" 
                                           id="email"
                                           required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                           value="{{ old('email') }}">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                            Password *
                                        </label>
                                        <input type="password" 
                                               name="password" 
                                               id="password"
                                               required
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        @error('password')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                            Confirm Password *
                                        </label>
                                        <input type="password" 
                                               name="password_confirmation" 
                                               id="password_confirmation"
                                               required
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Role and Status -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Role & Status</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                                        User Role *
                                    </label>
                                    <select name="role" 
                                            id="role"
                                            required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select Role</option>
                                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="support" {{ old('role') == 'support' ? 'selected' : '' }}>Support Agent</option>
                                        <option value="buyer" {{ old('role') == 'buyer' ? 'selected' : '' }}>Buyer</option>
                                        <option value="vendor_local" {{ old('role') == 'vendor_local' ? 'selected' : '' }}>Local Vendor</option>
                                        <option value="vendor_international" {{ old('role') == 'vendor_international' ? 'selected' : '' }}>International Vendor</option>
                                        <option value="logistics" {{ old('role') == 'logistics' ? 'selected' : '' }}>Logistics</option>
                                        <option value="finance" {{ old('role') == 'finance' ? 'selected' : '' }}>Finance</option>
                                        <option value="ceo" {{ old('role') == 'ceo' ? 'selected' : '' }}>CEO</option>
                                    </select>
                                    @error('role')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Account Status
                                    </label>
                                    <div class="flex items-center space-x-4">
                                        <label class="flex items-center">
                                            <input type="radio" 
                                                   name="is_active" 
                                                   value="1" 
                                                   {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                                                   class="text-primary focus:ring-primary">
                                            <span class="ml-2 text-sm text-gray-700">Active</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" 
                                                   name="is_active" 
                                                   value="0" 
                                                   {{ old('is_active') == '0' ? 'checked' : '' }}
                                                   class="text-primary focus:ring-primary">
                                            <span class="ml-2 text-sm text-gray-700">Inactive</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Email Verification -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="email_verified" 
                                       value="1"
                                       {{ old('email_verified') ? 'checked' : '' }}
                                       class="text-primary focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-700">Mark email as verified</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Check this to bypass email verification</p>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                        <a href="{{ route('admin.users.index') }}" 
                           class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-700">
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    const passwordStrength = document.createElement('div');
    passwordStrength.className = 'mt-1 text-xs';
    passwordInput.parentNode.appendChild(passwordStrength);
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        let message = '';
        let color = 'text-gray-500';
        
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        switch(strength) {
            case 0:
            case 1:
                message = 'Very Weak';
                color = 'text-red-600';
                break;
            case 2:
                message = 'Weak';
                color = 'text-orange-600';
                break;
            case 3:
                message = 'Good';
                color = 'text-yellow-600';
                break;
            case 4:
                message = 'Strong';
                color = 'text-green-600';
                break;
            case 5:
                message = 'Very Strong';
                color = 'text-green-700';
                break;
        }
        
        passwordStrength.textContent = `Password Strength: ${message}`;
        passwordStrength.className = `mt-1 text-xs ${color}`;
    });
</script>
@endsection