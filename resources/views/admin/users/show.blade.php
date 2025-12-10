@extends('layouts.admin')

@section('title', 'User Details - ' . config('app.name'))
@section('page-title', 'User Details')
@section('page-description', 'View user information and activity')

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
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $user->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - User Info -->
        <div class="lg:col-span-2">
            <!-- User Profile Card -->
            <div class="bg-white rounded-lg shadow-sm mb-6">
                <div class="p-6 border-b">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-16 w-16 bg-indigo-100 rounded-full flex items-center justify-center">
                                @if($user->profile_photo)
                                    <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-full">
                                @else
                                    <i class="fas fa-user text-indigo-600 text-2xl"></i>
                                @endif
                            </div>
                            <div class="ml-4">
                                <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                                <div class="flex items-center mt-1">
                                    <span class="text-sm text-gray-600 mr-3">
                                        <i class="fas fa-envelope mr-1"></i>{{ $user->email }}
                                    </span>
                                    @if($user->phone)
                                    <span class="text-sm text-gray-600">
                                        <i class="fas fa-phone mr-1"></i>{{ $user->phone }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-col items-end">
                            @php
                                $roleColors = [
                                    'admin' => 'bg-red-100 text-red-800',
                                    'buyer' => 'bg-purple-100 text-purple-800',
                                    'vendor_local' => 'bg-blue-100 text-blue-800',
                                    'vendor_international' => 'bg-green-100 text-green-800',
                                    'logistics' => 'bg-yellow-100 text-yellow-800',
                                    'finance' => 'bg-indigo-100 text-indigo-800',
                                    'ceo' => 'bg-pink-100 text-pink-800',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                            
                            @if($user->is_active)
                                <span class="mt-2 px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Active
                                </span>
                            @else
                                <span class="mt-2 px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i> Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- User Details -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">User ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email Status</dt>
                                    <dd class="mt-1">
                                        @if($user->email_verified_at)
                                            <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-1"></i> Verified
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i> Unverified
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y h:i A') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('M d, Y h:i A') }}</dd>
                                </div>
                                @if($user->last_login_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last Login</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->last_login_at->format('M d, Y h:i A') }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                        
                        <!-- Additional Info -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
                            @if($user->meta)
                            <dl class="space-y-3">
                                @foreach($user->meta as $key => $value)
                                    @if(is_string($value))
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $value }}</dd>
                                    </div>
                                    @endif
                                @endforeach
                            </dl>
                            @else
                            <p class="text-sm text-gray-500">No additional information</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Vendor Profile (if vendor) -->
                    @if($user->vendorProfile)
                    <div class="mt-8 pt-6 border-t">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Vendor Profile</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Business Name</dt>
                                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $user->vendorProfile->business_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full {{ $statusColors[$user->vendorProfile->vetting_status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($user->vendorProfile->vetting_status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Location</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->vendorProfile->city }}, {{ $user->vendorProfile->country }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $user->vendorProfile->vendor_type)) }}</dd>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('admin.vendors.show', $user->vendorProfile->id) }}" 
                                   class="text-sm text-primary hover:text-primary-700">
                                    View Full Vendor Profile →
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Right Column - Actions and Stats -->
        <div>
            <!-- Actions Card -->
            <div class="bg-white rounded-lg shadow-sm mb-6">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Actions</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="{{ route('admin.users.edit', $user) }}" 
                           class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-edit mr-2"></i> Edit User
                        </a>
                        
                        @if($user->id != auth()->id())
                        <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                                    onclick="return confirm('{{ $user->is_active ? 'Deactivate this user?' : 'Activate this user?' }}')">
                                <i class="fas fa-power-off mr-2"></i>
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        @endif
                        
                        @if(!$user->email_verified_at)
                        <form action="{{ route('admin.users.verify-email', $user->id) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-white bg-green-600 rounded-lg hover:bg-green-700"
                                    onclick="return confirm('Mark this email as verified?')">
                                <i class="fas fa-check-circle mr-2"></i> Verify Email
                            </button>
                        </form>
                        @endif
                        
                        @if($user->id != auth()->id())
                        <button type="button" 
                                onclick="showDeleteModal('{{ $user->id }}', '{{ $user->name }}')"
                                class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-white bg-red-600 rounded-lg hover:bg-red-700">
                            <i class="fas fa-trash mr-2"></i> Delete User
                        </button>
                        @endif
                        
                        <a href="{{ route('admin.users.index') }}" 
                           class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Users
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">User Stats</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Add stats based on user role -->
                        @if($user->role == 'buyer')
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Orders</span>
                            <span class="font-medium">{{ $user->orders->count() ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Reviews</span>
                            <span class="font-medium">{{ $user->reviews->count() ?? 0 }}</span>
                        </div>
                        @endif
                        
                        @if(in_array($user->role, ['vendor_local', 'vendor_international']) && $user->vendorProfile)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Products</span>
                            <span class="font-medium">{{ $user->vendorProfile->listings->count() ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Vendor Score</span>
                            <span class="font-medium">{{ $user->vendorProfile->scores()->latest()->first()->score ?? 0 }}/100</span>
                        </div>
                        @endif
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Account Age</span>
                            <span class="font-medium">{{ $user->created_at->diffForHumans(null, true) }}</span>
                        </div>
                    </div>
                </div>
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