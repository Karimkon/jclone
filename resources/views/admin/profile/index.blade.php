@extends('layouts.admin')

@section('title', 'My Profile')
@section('page-title', 'My Profile')
@section('page-description', 'Manage your profile and account settings')

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-white">
                <i class="fas fa-home mr-2"></i>
                Dashboard
            </a>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400"></i>
                <span class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400">My Profile</span>
            </div>
        </li>
    </ol>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Profile Header -->
    <div class="bg-white dark:bg-dark-800 rounded-xl shadow-lg border border-gray-200 dark:border-dark-700 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center relative overflow-hidden">
                        @if(auth()->user()->profile_photo)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" 
                                 alt="{{ auth()->user()->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <span class="text-white text-3xl font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-white dark:bg-dark-800 rounded-full flex items-center justify-center border-4 border-white dark:border-dark-800">
                        <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-white text-xs"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ auth()->user()->name }}</h2>
                    <p class="text-gray-600 dark:text-gray-400">{{ auth()->user()->email }}</p>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="px-3 py-1 bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 text-xs font-medium rounded-full">
                            {{ ucfirst(auth()->user()->role) }}
                        </span>
                        <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs font-medium rounded-full flex items-center gap-1">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            Online
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button onclick="document.getElementById('photoUpload').click()"
                        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition">
                    <i class="fas fa-camera mr-2"></i> Change Photo
                </button>
                <button onclick="showSection('settings')"
                        class="px-4 py-2 bg-gray-200 dark:bg-dark-700 hover:bg-gray-300 dark:hover:bg-dark-600 text-gray-800 dark:text-gray-200 rounded-lg font-medium transition">
                    <i class="fas fa-edit mr-2"></i> Edit Profile
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Profile Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white dark:bg-dark-800 rounded-xl shadow border border-gray-200 dark:border-dark-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-700 bg-gray-50 dark:bg-dark-900">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Personal Information</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Your personal details and contact information</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                            <p class="text-gray-900 dark:text-white font-medium">{{ auth()->user()->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                            <p class="text-gray-900 dark:text-white font-medium">{{ auth()->user()->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                            <p class="text-gray-900 dark:text-white font-medium">{{ auth()->user()->phone ?? 'Not set' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Type</label>
                            <p class="text-gray-900 dark:text-white font-medium">{{ ucfirst(auth()->user()->role) }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bio</label>
                            <p class="text-gray-600 dark:text-gray-400">{{ auth()->user()->bio ?? 'No bio added yet' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-dark-800 rounded-xl shadow border border-gray-200 dark:border-dark-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-700 bg-gray-50 dark:bg-dark-900">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Your recent actions and activities</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($activities as $activity)
                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-dark-700 transition">
                            <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-300 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-history text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900 dark:text-white">{{ $activity->action }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $activity->created_at->diffForHumans() }} • {{ $activity->ip_address }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                        
                        @if($activities->isEmpty())
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-dark-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-history text-gray-400 dark:text-gray-500 text-xl"></i>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400">No recent activity</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Quick Actions & Stats -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white dark:bg-dark-800 rounded-xl shadow border border-gray-200 dark:border-dark-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                </div>
                <div class="p-4 space-y-2">
                    <button onclick="showSection('settings')"
                            class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-dark-700 transition text-left">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 flex items-center justify-center">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Edit Profile</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Update your personal information</p>
                        </div>
                    </button>
                    
                    <button onclick="showSection('password')"
                            class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-dark-700 transition text-left">
                        <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300 flex items-center justify-center">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Change Password</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Update your account password</p>
                        </div>
                    </button>
                    
                    <a href="{{ route('admin.settings.index') }}"
                       class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-dark-700 transition text-left">
                        <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-300 flex items-center justify-center">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">System Settings</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Configure platform settings</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Account Stats -->
            <div class="bg-white dark:bg-dark-800 rounded-xl shadow border border-gray-200 dark:border-dark-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Account Stats</h3>
                </div>
                <div class="p-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-300 flex items-center justify-center">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Member Since</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ auth()->user()->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-300 flex items-center justify-center">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Account Status</p>
                                <p class="font-medium text-gray-900 dark:text-white">Active</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 flex items-center justify-center">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Last Login</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ auth()->user()->last_login_at?->diffForHumans() ?? 'Never' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Photo Upload -->
<form id="photoForm" enctype="multipart/form-data" style="display: none;">
    @csrf
    <input type="file" id="photoUpload" name="photo" accept="image/*" onchange="uploadPhoto()">
</form>

<!-- Modal for Profile Editing -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-700 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white" id="modalTitle">Edit Profile</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto max-h-[70vh]">
            <!-- Profile Form -->
            <form id="profileForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ auth()->user()->name }}"
                               class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ auth()->user()->email }}"
                               class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Phone Number
                        </label>
                        <input type="text" name="phone" value="{{ auth()->user()->phone }}"
                               class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                               placeholder="+1234567890">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Company
                        </label>
                        <input type="text" name="company" value="{{ auth()->user()->company }}"
                               class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                               placeholder="Your company">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Location
                        </label>
                        <input type="text" name="location" value="{{ auth()->user()->location }}"
                               class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                               placeholder="City, Country">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Website
                        </label>
                        <input type="url" name="website" value="{{ auth()->user()->website }}"
                               class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                               placeholder="https://example.com">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Bio <span class="text-xs text-gray-500">(Optional, max 500 characters)</span>
                    </label>
                    <textarea name="bio" rows="4"
                              class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition resize-none"
                              placeholder="Tell us about yourself...">{{ auth()->user()->bio }}</textarea>
                </div>
                
                <div id="profileErrors" class="hidden p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"></div>
                
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-700">
                    <button type="button" onclick="closeModal()"
                            class="px-5 py-2.5 border border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-dark-700 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
            
            <!-- Password Form -->
            <form id="passwordForm" class="space-y-6 hidden">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Current Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="current_password" id="current_password"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition pr-10">
                            <button type="button" onclick="togglePassword('current_password')"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            New Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="new_password" id="new_password"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition pr-10">
                            <button type="button" onclick="togglePassword('new_password')"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Must be at least 8 characters</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Confirm New Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition pr-10">
                            <button type="button" onclick="togglePassword('new_password_confirmation')"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div id="passwordErrors" class="hidden p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"></div>
                
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-700">
                    <button type="button" onclick="closeModal()"
                            class="px-5 py-2.5 border border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-dark-700 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                        <i class="fas fa-key"></i>
                        <span>Change Password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Show/hide sections
    function showSection(section) {
        const modal = document.getElementById('editModal');
        const title = document.getElementById('modalTitle');
        const profileForm = document.getElementById('profileForm');
        const passwordForm = document.getElementById('passwordForm');
        const profileErrors = document.getElementById('profileErrors');
        const passwordErrors = document.getElementById('passwordErrors');
        
        // Hide all forms and clear errors
        profileForm.classList.add('hidden');
        passwordForm.classList.add('hidden');
        profileErrors.classList.add('hidden');
        passwordErrors.classList.add('hidden');
        
        // Show selected section
        if (section === 'settings') {
            title.textContent = 'Edit Profile';
            profileForm.classList.remove('hidden');
        } else if (section === 'password') {
            title.textContent = 'Change Password';
            passwordForm.classList.remove('hidden');
        }
        
        // Show modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    // Close modal
    function closeModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    // Toggle password visibility
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const button = input.nextElementSibling;
        
        if (input.type === 'password') {
            input.type = 'text';
            button.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
            input.type = 'password';
            button.innerHTML = '<i class="fas fa-eye"></i>';
        }
    }
    
    // Upload profile photo
    function uploadPhoto() {
        const formData = new FormData(document.getElementById('photoForm'));
        
        // Show loading
        Swal.fire({
            title: 'Uploading...',
            text: 'Please wait while we upload your photo',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch('{{ route("admin.profile.upload-photo") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Reload page after 2 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to upload photo',
                });
            }
        })
        .catch(error => {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while uploading',
            });
        });
    }
    
    // Update profile
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const errorsDiv = document.getElementById('profileErrors');
        
        // Show loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;
        
        fetch('{{ route("admin.profile.update") }}', {
            method: 'PUT',
            body: JSON.stringify(Object.fromEntries(formData)),
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                
                // Update user info on page
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                errorsDiv.classList.remove('hidden');
                
                if (data.errors) {
                    let errorsHtml = '<ul class="list-disc pl-5">';
                    Object.values(data.errors).forEach(error => {
                        errorsHtml += `<li class="text-red-600 dark:text-red-400 text-sm">${error}</li>`;
                    });
                    errorsHtml += '</ul>';
                    errorsDiv.innerHTML = errorsHtml;
                } else {
                    errorsDiv.innerHTML = `<p class="text-red-600 dark:text-red-400">${data.message}</p>`;
                }
                
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while saving',
            });
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    
    // Change password
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const errorsDiv = document.getElementById('passwordErrors');
        
        // Show loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Changing...';
        submitBtn.disabled = true;
        
        fetch('{{ route("admin.profile.change-password") }}', {
            method: 'PUT',
            body: JSON.stringify(Object.fromEntries(formData)),
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                
                setTimeout(() => {
                    closeModal();
                }, 1500);
            } else {
                errorsDiv.classList.remove('hidden');
                
                if (data.errors) {
                    let errorsHtml = '<ul class="list-disc pl-5">';
                    Object.values(data.errors).forEach(error => {
                        errorsHtml += `<li class="text-red-600 dark:text-red-400 text-sm">${error}</li>`;
                    });
                    errorsHtml += '</ul>';
                    errorsDiv.innerHTML = errorsHtml;
                } else {
                    errorsDiv.innerHTML = `<p class="text-red-600 dark:text-red-400">${data.message}</p>`;
                }
                
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while changing password',
            });
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>
@endpush