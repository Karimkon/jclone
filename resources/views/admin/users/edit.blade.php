@extends('layouts.admin')

@section('title', 'Edit User - ' . config('app.name'))
@section('page-title', 'Edit User')
@section('page-description', 'Edit user information and settings')

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
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <a href="{{ route('admin.users.show', $user) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-primary md:ml-2">{{ $user->name }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Edit</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold text-gray-900">Edit User: {{ $user->name }}</h2>
                <p class="text-gray-600 mt-1">Update user account information</p>
            </div>
            
            <div class="p-6">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
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
                                           value="{{ old('name', $user->name) }}">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                        Phone Number *
                                    </label>
                                    <input type="text" 
                                           name="phone" 
                                           id="phone"
                                           required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                           value="{{ old('phone', $user->phone) }}">
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
                                           value="{{ old('email', $user->email) }}">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                            Password (Leave blank to keep current)
                                        </label>
                                        <input type="password" 
                                               name="password" 
                                               id="password"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        @error('password')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                            Confirm Password
                                        </label>
                                        <input type="password" 
                                               name="password_confirmation" 
                                               id="password_confirmation"
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
                                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="buyer" {{ old('role', $user->role) == 'buyer' ? 'selected' : '' }}>Buyer</option>
                                        <option value="vendor_local" {{ old('role', $user->role) == 'vendor_local' ? 'selected' : '' }}>Local Vendor</option>
                                        <option value="vendor_international" {{ old('role', $user->role) == 'vendor_international' ? 'selected' : '' }}>International Vendor</option>
                                        <option value="logistics" {{ old('role', $user->role) == 'logistics' ? 'selected' : '' }}>Logistics</option>
                                        <option value="finance" {{ old('role', $user->role) == 'finance' ? 'selected' : '' }}>Finance</option>
                                        <option value="ceo" {{ old('role', $user->role) == 'ceo' ? 'selected' : '' }}>CEO</option>
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
                                                   {{ old('is_active', $user->is_active) == '1' ? 'checked' : '' }}
                                                   class="text-primary focus:ring-primary">
                                            <span class="ml-2 text-sm text-gray-700">Active</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" 
                                                   name="is_active" 
                                                   value="0" 
                                                   {{ old('is_active', $user->is_active) == '0' ? 'checked' : '' }}
                                                   class="text-primary focus:ring-primary">
                                            <span class="ml-2 text-sm text-gray-700">Inactive</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex justify-between items-center mt-8 pt-6 border-t">
                        <div>
                            @if($user->id != auth()->id())
                            <button type="button" 
                                    onclick="showDeleteModal('{{ $user->id }}', '{{ $user->name }}')"
                                    class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50">
                                <i class="fas fa-trash mr-2"></i> Delete User
                            </button>
                            @endif
                        </div>
                        
                        <div class="flex space-x-3">
                            <a href="{{ route('admin.users.show', $user) }}" 
                               class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-700">
                                Update User
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <div class="text-center mb-4">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-3">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Delete User</h3>
            <p class="text-gray-600 mb-4">Are you sure you want to delete <span id="deleteUserName" class="font-semibold"></span>?</p>
            <p class="text-sm text-red-600 mb-6">This action cannot be undone. All user data will be permanently deleted.</p>
        </div>
        
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Delete User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showDeleteModal(userId, userName) {
        document.getElementById('deleteUserName').textContent = userName;
        document.getElementById('deleteForm').action = `/admin/users/${userId}`;
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
    
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
</script>
@endsection