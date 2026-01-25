@extends('layouts.admin')

@section('title', 'Users Management - ' . config('app.name'))
@section('page-title', 'Users Management')
@section('page-description', 'Manage all system users')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Users Management</h1>
            <p class="text-gray-600">Manage all users on the platform</p>
        </div>
        <div class="bg-indigo-50 px-4 py-2 rounded-lg">
            <span class="text-indigo-700 font-bold">{{ $stats['total'] }}</span>
            <span class="text-gray-600">total users</span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Users</p>
                    <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg mr-3">
                    <i class="fas fa-store text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Vendors</p>
                    <p class="text-2xl font-bold">{{ $stats['vendors'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg mr-3">
                    <i class="fas fa-shopping-cart text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Buyers</p>
                    <p class="text-2xl font-bold">{{ $stats['buyers'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg mr-3">
                    <i class="fas fa-user-tie text-yellow-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Staff</p>
                    <p class="text-2xl font-bold">{{ $stats['staff'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Bar -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="relative flex-1">
                <input type="text" 
                       placeholder="Search users by name, email, or phone..."
                       id="searchInput"
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            
            <div class="flex space-x-2">
                <select id="roleFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="buyer">Buyer</option>
                    <option value="vendor_local">Local Vendor</option>
                    <option value="vendor_international">International Vendor</option>
                    <option value="logistics">Logistics</option>
                    <option value="finance">Finance</option>
                    <option value="ceo">CEO</option>
                </select>
                
                <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                
                <a href="{{ route('admin.users.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-700">
                    <i class="fas fa-plus mr-2"></i> Add User
                </a>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                    @if($user->profile_photo)
                                        <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="h-10 w-10 rounded-full">
                                    @else
                                        <i class="fas fa-user text-indigo-600"></i>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 flex items-center">
                                        {{ $user->name }}
                                        @if($user->is_admin_verified)
                                            <span class="inline-flex items-center justify-center w-4 h-4 ml-1 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 shadow-sm" title="Verified User">
                                                <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                            </span>
                                        @endif
                                        @if($user->id == auth()->id())
                                            <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded ml-2">You</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    @if($user->phone)
                                    <div class="text-xs text-gray-500">{{ $user->phone }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
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
                                $roleLabels = [
                                    'admin' => 'Admin',
                                    'buyer' => 'Buyer',
                                    'vendor_local' => 'Local Vendor',
                                    'vendor_international' => 'International Vendor',
                                    'logistics' => 'Logistics',
                                    'finance' => 'Finance',
                                    'ceo' => 'CEO',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs rounded-full {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->is_active)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Active
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i> Inactive
                                </span>
                            @endif
                            @if($user->email_verified_at)
                                <div class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-check text-green-500 mr-1"></i> Verified
                                </div>
                            @else
                                <div class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-clock text-yellow-500 mr-1"></i> Unverified
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $user->created_at->format('M d, Y') }}
                            <div class="text-xs text-gray-400">{{ $user->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.users.show', $user) }}" 
                                   class="text-indigo-600 hover:text-indigo-900"
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" 
                                   class="text-blue-600 hover:text-blue-900"
                                   title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($user->id != auth()->id())
                                <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="text-gray-600 hover:text-gray-900"
                                            title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}"
                                            onclick="return confirm('{{ $user->is_active ? 'Deactivate this user?' : 'Activate this user?' }}')">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="text-gray-400">
                                <i class="fas fa-users-slash text-4xl mb-3"></i>
                                <p class="text-lg">No users found</p>
                                <p class="text-sm mt-1">No users have registered yet</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    // Simple search and filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');
        
        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase();
            const role = roleFilter.value;
            const status = statusFilter.value;
            
            document.querySelectorAll('tbody tr').forEach(row => {
                const name = row.querySelector('.text-sm.font-medium').textContent.toLowerCase();
                const email = row.querySelector('.text-sm.text-gray-500').textContent.toLowerCase();
                const roleCell = row.querySelector('.px-2.py-1.text-xs.rounded-full').textContent.toLowerCase();
                const isActive = row.querySelector('.fa-check-circle') ? 'active' : 'inactive';
                
                let shouldShow = true;
                
                if (searchTerm && !name.includes(searchTerm) && !email.includes(searchTerm)) {
                    shouldShow = false;
                }
                
                if (role) {
                    const roleMap = {
                        'admin': 'admin',
                        'buyer': 'buyer',
                        'vendor_local': 'local vendor',
                        'vendor_international': 'international vendor',
                        'logistics': 'logistics',
                        'finance': 'finance',
                        'ceo': 'ceo'
                    };
                    if (roleCell !== roleMap[role]) {
                        shouldShow = false;
                    }
                }
                
                if (status && isActive !== status) {
                    shouldShow = false;
                }
                
                row.style.display = shouldShow ? '' : 'none';
            });
        }
        
        searchInput.addEventListener('keyup', applyFilters);
        roleFilter.addEventListener('change', applyFilters);
        statusFilter.addEventListener('change', applyFilters);
    });
</script>
@endsection