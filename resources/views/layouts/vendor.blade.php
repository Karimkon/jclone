<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vendor Dashboard - JClone')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    
    <style>
        .vendor-sidebar {
            width: 220px;
            background: linear-gradient(180deg, #4f46e5 0%, #3730a3 100%);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
        }
        
        .vendor-content {
            margin-left: 220px;
            padding: 20px;
            min-height: 100vh;
            background: #f8fafc;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {
            .vendor-sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .vendor-content {
                margin-left: 0;
            }
        }
        
        /* Simple Select2 styling */
        .select2-container--default .select2-selection--single {
            height: 50px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 48px !important;
            padding-left: 16px !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 48px !important;
            right: 10px !important;
        }
        
        .select2-dropdown {
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Vendor Sidebar -->
    <div class="vendor-sidebar">
        <div class="p-6">
            <h2 class="text-xl font-bold">
                <i class="fas fa-store mr-2"></i>Vendor Dashboard
            </h2>
            <p class="text-sm opacity-75 mt-1">{{ auth()->user()->vendorProfile->business_name ?? 'My Store' }}</p>
        </div>
        
        <nav class="mt-6">
            <a href="{{ route('vendor.dashboard') }}" class="block py-3 px-6 hover:bg-indigo-700 {{ request()->routeIs('vendor.dashboard') ? 'bg-indigo-800' : '' }}">
                <i class="fas fa-tachometer-alt mr-3"></i> Overview
            </a>
            
            <a href="{{ route('vendor.listings.index') }}" class="block py-3 px-6 hover:bg-indigo-700 {{ request()->is('vendor/listings*') ? 'bg-indigo-800' : '' }}">
                <i class="fas fa-boxes mr-3"></i> My Listings
            </a>
            
            <a href="{{ route('vendor.orders.index') }}" class="block py-3 px-6 hover:bg-indigo-700 {{ request()->is('vendor/orders*') ? 'bg-indigo-800' : '' }}">
                <i class="fas fa-shopping-cart mr-3"></i> Orders
            </a>
            
            <a href="{{ route('vendor.profile.show') }}" class="block py-3 px-6 hover:bg-indigo-700 {{ request()->is('vendor/profile*') ? 'bg-indigo-800' : '' }}">
                <i class="fas fa-user-circle mr-3"></i> Profile
            </a>
            
            <a href="{{ route('vendor.imports.index') }}" class="block py-3 px-6 hover:bg-indigo-700 {{ request()->is('vendor/imports*') ? 'bg-indigo-800' : '' }}">                <i class="fas fa-plane mr-3"></i> Import Goods
            </a>
            
            <a href="{{ route('vendor.promotions.index') }}" class="block py-3 px-6 hover:bg-indigo-700 {{ request()->is('vendor/promotions*') ? 'bg-indigo-800' : '' }}">
                <i class="fas fa-bullhorn mr-3"></i> Promotions
            </a>
            
            <a href="{{ route('vendor.analytics') }}" class="block py-3 px-6 hover:bg-indigo-700 {{ request()->is('vendor/analytics*') ? 'bg-indigo-800' : '' }}">
                <i class="fas fa-chart-line mr-3"></i> Analytics
            </a>
        </nav>
        
        <!-- Vendor Status -->
        @php
            $vendor = auth()->user()->vendorProfile;
            $statusColor = [
                'pending' => 'bg-yellow-500',
                'approved' => 'bg-green-500',
                'rejected' => 'bg-red-500',
                'manual_review' => 'bg-blue-500'
            ][$vendor->vetting_status ?? 'pending'];
        @endphp
        
        <div class="absolute bottom-0 w-full p-6">
            <div class="bg-indigo-800 p-4 rounded-lg">
                <p class="text-sm opacity-75">Vendor Status</p>
                <div class="flex items-center justify-between mt-2">
                    <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $vendor->vetting_status)) }}</span>
                    <span class="{{ $statusColor }} text-white text-xs px-2 py-1 rounded-full">
                        {{ ucfirst($vendor->vetting_status) }}
                    </span>
                </div>
            </div>
            
            <!-- Logout -->
            <form action="{{ route('logout') }}" method="POST" class="mt-4">
                @csrf
                <button type="submit" class="w-full text-left text-gray-300 hover:text-white">
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
            <div class="flex items-center space-x-4">
                <div class="bg-white rounded-lg shadow px-4 py-2">
                    <span class="text-sm text-gray-600">Balance:</span>
                    <span class="font-bold text-green-600 ml-2">$0.00</span>
                </div>
                <a href="{{ route('vendor.listings.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-plus mr-2"></i> Add Listing
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
        
        <!-- Page Content -->
        @yield('content')
    </div>
    
    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    
    @stack('scripts')
</body>
</html>