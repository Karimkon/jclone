<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vendor Dashboard - BebaMart')</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    
    <style>
        .vendor-sidebar {
            width: 240px;
            background: linear-gradient(180deg, #4f46e5 0%, #3730a3 100%);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
        }
        
        .vendor-content {
            margin-left: 240px;
            padding: 20px;
            min-height: 100vh;
            background: #f8fafc;
        }
        
        .nav-section-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.7;
            padding: 12px 20px 6px;
            margin-top: 8px;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .nav-item:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: rgba(255,255,255,0.5);
        }
        
        .nav-item.active {
            background: rgba(255,255,255,0.15);
            border-left-color: #fff;
        }
        
        .nav-badge {
            margin-left: auto;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @media (max-width: 768px) {
            .vendor-sidebar { width: 100%; height: auto; position: relative; }
            .vendor-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="vendor-sidebar">
        <!-- Header -->
        <div class="p-5 border-b border-indigo-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                    <i class="fas fa-store text-indigo-600"></i>
                </div>
                <div>
                    <h2 class="font-bold">Vendor Panel</h2>
                    <p class="text-xs opacity-75 truncate max-w-[140px]">{{ auth()->user()->vendorProfile->business_name ?? 'My Store' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="py-4">
            <!-- Main -->
            <a href="{{ route('vendor.dashboard') }}" class="nav-item {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt w-5 mr-3"></i> Dashboard
            </a>
            
            <!-- Products Section -->
            <div class="nav-section-title">Products</div>
            
            <a href="{{ route('vendor.listings.index') }}" class="nav-item {{ request()->is('vendor/listings*') ? 'active' : '' }}">
                <i class="fas fa-boxes w-5 mr-3"></i> My Listings
            </a>
            
            <a href="{{ route('vendor.orders.index') }}" class="nav-item {{ request()->is('vendor/orders*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart w-5 mr-3"></i> Orders
                @php
                    $pendingOrders = \App\Models\Order::where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0)
                        ->whereIn('status', ['pending', 'paid'])->count();
                @endphp
                @if($pendingOrders > 0)
                <span class="nav-badge bg-orange-500">{{ $pendingOrders }}</span>
                @endif
            </a>
            
            <!-- Jobs & Services Section -->
            <div class="nav-section-title">Jobs & Services</div>
            
            <a href="{{ route('vendor.jobs.index') }}" class="nav-item {{ request()->is('vendor/jobs*') ? 'active' : '' }}">
                <i class="fas fa-briefcase w-5 mr-3"></i> Job Listings
                @php
                    $pendingApps = \App\Models\JobApplication::whereHas('job', fn($q) => $q->where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0))
                        ->where('status', 'pending')->count();
                @endphp
                @if($pendingApps > 0)
                <span class="nav-badge bg-blue-500">{{ $pendingApps }}</span>
                @endif
            </a>
            
            <a href="{{ route('vendor.services.index') }}" class="nav-item {{ request()->is('vendor/services') ? 'active' : '' }}">
                <i class="fas fa-tools w-5 mr-3"></i> My Services
            </a>
            
            <a href="{{ route('vendor.services.requests') }}" class="nav-item {{ request()->is('vendor/services/requests*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list w-5 mr-3"></i> Service Requests
                @php
                    $pendingRequests = \App\Models\ServiceRequest::where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0)
                        ->where('status', 'pending')->count();
                @endphp
                @if($pendingRequests > 0)
                <span class="nav-badge bg-green-500">{{ $pendingRequests }}</span>
                @endif
            </a>
            
            <!-- Communication Section -->
            <div class="nav-section-title">Communication</div>
            
            <a href="{{ route('vendor.callbacks.index') }}" class="nav-item {{ request()->is('vendor/callbacks*') ? 'active' : '' }}">
                <i class="fas fa-phone-alt w-5 mr-3"></i> Callbacks
                @php
                    $pendingCallbacks = \App\Models\CallbackRequest::where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0)
                        ->where('status', 'pending')->count();
                @endphp
                @if($pendingCallbacks > 0)
                <span class="nav-badge bg-orange-500">{{ $pendingCallbacks }}</span>
                @endif
            </a>
            
            <a href="{{ route('chat.index') }}" class="nav-item {{ request()->is('chat*') ? 'active' : '' }}">
                <i class="fas fa-comments w-5 mr-3"></i> Messages
                <span id="chatBadge" class="nav-badge bg-red-500 hidden">0</span>
            </a>
            
            <a href="{{ route('vendor.services.inquiries') }}" class="nav-item {{ request()->is('vendor/services/inquiries*') ? 'active' : '' }}">
                <i class="fas fa-envelope w-5 mr-3"></i> Inquiries
                @php
                    $newInquiries = \App\Models\ServiceInquiry::where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0)
                        ->where('status', 'new')->count();
                @endphp
                @if($newInquiries > 0)
                <span class="nav-badge bg-purple-500">{{ $newInquiries }}</span>
                @endif
            </a>
            
            <!-- More Section -->
            <div class="nav-section-title">More</div>
            
            <a href="{{ route('vendor.services.reviews') }}" class="nav-item {{ request()->is('vendor/services/reviews*') ? 'active' : '' }}">
                <i class="fas fa-star w-5 mr-3"></i> Reviews
            </a>
            
            <a href="{{ route('vendor.imports.index') }}" class="nav-item {{ request()->is('vendor/imports*') ? 'active' : '' }}">
                <i class="fas fa-plane w-5 mr-3"></i> Import Goods
            </a>
            
            <a href="{{ route('vendor.promotions.index') }}" class="nav-item {{ request()->is('vendor/promotions*') ? 'active' : '' }}">
                <i class="fas fa-bullhorn w-5 mr-3"></i> Promotions
            </a>
            
            <a href="{{ route('vendor.analytics') }}" class="nav-item {{ request()->is('vendor/analytics*') ? 'active' : '' }}">
                <i class="fas fa-chart-line w-5 mr-3"></i> Analytics
            </a>
            
            <a href="{{ route('vendor.profile.show') }}" class="nav-item {{ request()->is('vendor/profile*') ? 'active' : '' }}">
                <i class="fas fa-user-circle w-5 mr-3"></i> Profile
            </a>
        </nav>
        
        <!-- Footer -->
        <div class="p-4 border-t border-indigo-700 mt-auto">
            @php $vendor = auth()->user()->vendorProfile; @endphp
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs opacity-75">Account Status</span>
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-500',
                        'approved' => 'bg-green-500',
                        'rejected' => 'bg-red-500',
                    ];
                @endphp
                <span class="{{ $statusColors[$vendor->vetting_status ?? 'pending'] ?? 'bg-gray-500' }} text-white text-xs px-2 py-0.5 rounded-full">
                    {{ ucfirst($vendor->vetting_status ?? 'pending') }}
                </span>
            </div>
            
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-left text-sm opacity-75 hover:opacity-100 py-2">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </button>
            </form>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="vendor-content">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">@yield('page_title', 'Vendor Dashboard')</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('vendor.listings.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm">
                    <i class="fas fa-plus mr-2"></i> Add Listing
                </a>
                <a href="{{ route('vendor.jobs.create') }}" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 text-sm">
                    <i class="fas fa-briefcase mr-2"></i> Post Job
                </a>
                <a href="{{ route('vendor.services.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm">
                    <i class="fas fa-tools mr-2"></i> Add Service
                </a>
            </div>
        </div>
        
        <!-- Alerts -->
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif
        
        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
        @endif
        
        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        
        <!-- Page Content -->
        @yield('content')
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        updateChatBadge();
        setInterval(updateChatBadge, 30000);
    });

    async function updateChatBadge() {
        try {
            const response = await fetch('/chat/api/unread-count', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (data.success) {
                const badge = document.getElementById('chatBadge');
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        } catch (error) {
            console.error('Failed to update chat badge:', error);
        }
    }
    </script>
    
    @stack('scripts')
</body>
</html>
