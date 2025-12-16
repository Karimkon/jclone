<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name') }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                        secondary: '#10b981',
                        accent: '#f59e0b',
                        danger: '#ef4444',
                        success: '#10b981',
                        warning: '#f59e0b',
                        info: '#3b82f6',
                        dark: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617'
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                        'mono': ['Fira Code', 'monospace']
                    },
                    animation: {
                        'slide-in': 'slideIn 0.3s ease-out',
                        'slide-out': 'slideOut 0.3s ease-in',
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'bounce-slow': 'bounce 2s infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'spin-slow': 'spin 3s linear infinite',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        slideIn: {
                            '0%': { transform: 'translateX(-100%)' },
                            '100%': { transform: 'translateX(0)' }
                        },
                        slideOut: {
                            '0%': { transform: 'translateX(0)' },
                            '100%': { transform: 'translateX(-100%)' }
                        },
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        }
                    }
                }
            },
            plugins: []
        }
    </script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@300;400;500&display=swap');
        
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 70px;
            --header-height: 70px;
            --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            --glass-bg: rgba(255, 255, 255, 0.8);
            --glass-border: rgba(255, 255, 255, 0.2);
        }
        
        .dark {
            --glass-bg: rgba(30, 41, 59, 0.8);
            --glass-border: rgba(255, 255, 255, 0.1);
        }
        
        /* Custom Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }
        
        .dark body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        
        /* Glassmorphism */
        .glass {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
        }
        
        /* Sidebar Styles */
        .sidebar-container {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 100;
            transition: var(--transition-smooth);
            display: flex;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            height: 100%;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: var(--shadow-xl);
            display: flex;
            flex-direction: column;
            transition: var(--transition-smooth);
            border-right: 1px solid rgba(226, 232, 240, 0.5);
            overflow: hidden;
        }
        
        .dark .sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            border-right: 1px solid rgba(71, 85, 105, 0.5);
        }
        
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar-header {
            height: var(--header-height);
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
        }
        
        .dark .sidebar-header {
            border-bottom: 1px solid rgba(71, 85, 105, 0.5);
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: var(--transition-smooth);
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
        }
        
        .logo-text {
            font-size: 1.25rem;
            font-weight: 700;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            white-space: nowrap;
            overflow: hidden;
            transition: var(--transition-smooth);
        }
        
        .sidebar.collapsed .logo-text {
            opacity: 0;
            width: 0;
        }
        
        .logo-subtitle {
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: 0.05em;
        }
        
        .sidebar.collapsed .logo-subtitle {
            display: none;
        }
        
        /* Toggle Button */
        .toggle-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border: 1px solid rgba(226, 232, 240, 0.5);
            color: #64748b;
            cursor: pointer;
            transition: var(--transition-smooth);
        }
        
        .dark .toggle-btn {
            background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
            border: 1px solid rgba(71, 85, 105, 0.5);
            color: #cbd5e1;
        }
        
        .toggle-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* User Profile */
        .user-profile {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            transition: var(--transition-smooth);
        }
        
        .dark .user-profile {
            border-bottom: 1px solid rgba(71, 85, 105, 0.5);
        }
        
        .sidebar.collapsed .user-profile {
            padding: 1rem;
        }
        
        .user-avatar {
            position: relative;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
            overflow: hidden;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .user-status {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 14px;
            height: 14px;
            background: #10b981;
            border: 2px solid white;
            border-radius: 50%;
        }
        
        .dark .user-status {
            border-color: #1e293b;
        }
        
        .user-info {
            transition: var(--transition-smooth);
            overflow: hidden;
        }
        
        .sidebar.collapsed .user-info {
            opacity: 0;
            width: 0;
            margin: 0;
        }
        
        /* Navigation */
        .nav-container {
            flex: 1;
            padding: 1rem 0.75rem;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        .nav-section {
            margin-bottom: 1.5rem;
        }
        
        .nav-section-title {
            padding: 0 1rem;
            margin-bottom: 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            transition: var(--transition-smooth);
            white-space: nowrap;
            overflow: hidden;
        }
        
        .sidebar.collapsed .nav-section-title {
            opacity: 0;
            height: 0;
            margin: 0;
            padding: 0;
        }
        
        .nav-item {
            position: relative;
            margin: 0.25rem 0;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.875rem 1rem;
            border-radius: 12px;
            color: #64748b;
            text-decoration: none;
            transition: var(--transition-smooth);
            gap: 0.75rem;
        }
        
        .nav-link:hover {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            color: #4f46e5;
            transform: translateX(5px);
        }
        
        .dark .nav-link:hover {
            background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
        }
        
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: white;
            border-radius: 0 2px 2px 0;
            opacity: 0.8;
        }
        
        .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.125rem;
        }
        
        .nav-text {
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
            transition: var(--transition-smooth);
        }
        
        .sidebar.collapsed .nav-text {
            opacity: 0;
            width: 0;
            margin: 0;
        }
        
        .badge {
            margin-left: auto;
            min-width: 20px;
            height: 20px;
            padding: 0 0.375rem;
            background: #ef4444;
            color: white;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }
        
        /* Tooltip for collapsed sidebar */
        .nav-tooltip {
            position: absolute;
            left: calc(100% + 10px);
            top: 50%;
            transform: translateY(-50%);
            padding: 0.5rem 0.75rem;
            background: #1e293b;
            color: white;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
        }
        
        .nav-tooltip::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 50%;
            transform: translateY(-50%);
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
            border-right: 6px solid #1e293b;
        }
        
        .sidebar.collapsed .nav-item:hover .nav-tooltip {
            opacity: 1;
        }
        
        /* Bottom Section */
        .sidebar-bottom {
            padding: 1rem;
            border-top: 1px solid rgba(226, 232, 240, 0.5);
            transition: var(--transition-smooth);
        }
        
        .dark .sidebar-bottom {
            border-top: 1px solid rgba(71, 85, 105, 0.5);
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: var(--transition-smooth);
            min-height: 100vh;
        }
        
        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        /* Header */
        .main-header {
            position: sticky;
            top: 0;
            z-index: 50;
            height: var(--header-height);
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .dark .main-header {
            border-bottom: 1px solid rgba(71, 85, 105, 0.5);
        }
        
        .header-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .dark .header-title {
            color: #f1f5f9;
        }
        
        /* Header Controls */
        .header-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        /* Notification Bell */
        .notification-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border: 1px solid rgba(226, 232, 240, 0.5);
            color: #64748b;
            cursor: pointer;
            transition: var(--transition-smooth);
        }
        
        .dark .notification-btn {
            background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
            border: 1px solid rgba(71, 85, 105, 0.5);
            color: #cbd5e1;
        }
        
        .notification-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* Theme Toggle */
        .theme-toggle {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border: 1px solid rgba(226, 232, 240, 0.5);
            color: #64748b;
            cursor: pointer;
            transition: var(--transition-smooth);
        }
        
        .dark .theme-toggle {
            background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
            border: 1px solid rgba(71, 85, 105, 0.5);
            color: #cbd5e1;
        }
        
        /* Mobile Overlay */
        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 90;
            display: none;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            :root {
                --sidebar-width: 260px;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .main-content.expanded {
                margin-left: 0;
            }
            
            .mobile-overlay.active {
                display: block;
            }
        }
        
        @media (max-width: 640px) {
            .main-header {
                padding: 0 1rem;
            }
            
            .header-controls {
                gap: 0.5rem;
            }
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        
        .dark ::-webkit-scrollbar-track {
            background: #1e293b;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Animations */
        .animate-in {
            animation: fadeIn 0.5s ease-out;
        }
        
        .slide-in-left {
            animation: slideIn 0.3s ease-out;
        }
        
        .slide-out-left {
            animation: slideOut 0.3s ease-in;
        }
        
        /* Loading States */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        /* Pulse Animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body class="dark:bg-dark-950">
    <!-- Sidebar Container -->
    <div class="sidebar-container">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar">
            <!-- Header -->
            <div class="sidebar-header">
                <div class="logo-container">
                    <div class="logo-icon">
                        <i class="fas fa-store text-white text-lg"></i>
                    </div>
                    <div>
                        <div class="logo-text">{{ config('app.name') }}</div>
                        <div class="logo-subtitle">ADMIN PANEL</div>
                    </div>
                </div>
                <button id="toggleSidebar" class="toggle-btn">
                    <i class="fas fa-chevron-left text-sm"></i>
                </button>
            </div>
            
            <!-- User Profile -->
            <div class="user-profile flex items-center gap-3">
                <div class="user-avatar">
                    @if(Auth::user()->profile_photo)
                        <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}">
                    @else
                        <span class="text-white font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    @endif
                    <div class="user-status"></div>
                </div>
                <div class="user-info">
                    <div class="font-semibold text-dark-900 dark:text-white truncate">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-dark-500 dark:text-dark-400 flex items-center gap-1">
                        <span class="bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 px-2 py-0.5 rounded-md text-xs font-medium">
                            {{ ucfirst(Auth::user()->role) }}
                        </span>
                        <span class="flex items-center">
                            <span class="w-1.5 h-1.5 bg-success rounded-full mr-1"></span>
                            Online
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <div class="nav-container scrollbar-thin">
                <!-- Dashboard -->
                <div class="nav-section">
                    <div class="nav-section-title">MAIN</div>
                    <div class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <div class="nav-icon">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <span class="nav-text">Dashboard</span>
                            <div class="nav-tooltip">Dashboard</div>
                        </a>
                    </div>
                </div>
                
                <!-- User Management -->
                <div class="nav-section">
                    <div class="nav-section-title">USER MANAGEMENT</div>
                    
                    <div class="nav-item">
                        <a href="{{ route('admin.users.index') }}" 
                           class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <div class="nav-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <span class="nav-text">Users</span>
                            <div class="nav-tooltip">Users</div>
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a href="{{ route('admin.vendors.pending') }}" 
                           class="nav-link {{ request()->routeIs('admin.vendors.pending') ? 'active' : '' }}">
                            <div class="nav-icon">
                                <i class="fas fa-store"></i>
                            </div>
                            <span class="nav-text">Vendor Applications</span>
                            @php $pendingVendors = \App\Models\VendorProfile::where('vetting_status', 'pending')->count(); @endphp
                            @if($pendingVendors > 0)
                                <span class="badge">{{ $pendingVendors }}</span>
                            @endif
                            <div class="nav-tooltip">Vendor Applications</div>
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a href="{{ route('admin.vendors.index') }}" 
                           class="nav-link {{ request()->routeIs('admin.vendors.index') ? 'active' : '' }}">
                            <div class="nav-icon">
                                <i class="fas fa-list-alt"></i>
                            </div>
                            <span class="nav-text">All Vendors</span>
                            <div class="nav-tooltip">All Vendors</div>
                        </a>
                    </div>
                </div>
                
                <!-- Content Management -->
                <div class="nav-section">
                    <div class="nav-section-title">CONTENT MANAGEMENT</div>
                    
                    <div class="nav-item">
                        <a href="{{ route('admin.categories.index') }}" 
                           class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                            <div class="nav-icon">
                                <i class="fas fa-tags"></i>
                            </div>
                            <span class="nav-text">Categories</span>
                            <div class="nav-tooltip">Categories</div>
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a href="{{ route('admin.listings.index') }}" 
                           class="nav-link {{ request()->routeIs('admin.listings.*') ? 'active' : '' }}">
                            <div class="nav-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <span class="nav-text">Products</span>
                            <div class="nav-tooltip">Products</div>
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a href="{{ route('admin.orders.index') }}" 
                           class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                            <div class="nav-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <span class="nav-text">Orders</span>
                            <div class="nav-tooltip">Orders</div>
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a href="{{ route('admin.disputes.index') }}" 
                           class="nav-link {{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}">
                            <div class="nav-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <span class="nav-text">Disputes</span>
                            @php $pendingDisputes = \App\Models\Dispute::where('status', 'open')->count(); @endphp
                            @if($pendingDisputes > 0)
                                <span class="badge">{{ $pendingDisputes }}</span>
                            @endif
                            <div class="nav-tooltip">Disputes</div>
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a href="{{ route('admin.contact-messages.index') }}" 
                           class="nav-link {{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}">
                            <div class="nav-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <span class="nav-text">Contact Messages</span>
                            @php $newMessages = \App\Models\ContactMessage::where('status', 'new')->count(); @endphp
                            @if($newMessages > 0)
                                <span class="badge">{{ $newMessages }}</span>
                            @endif
                            <div class="nav-tooltip">Contact Messages</div>
                        </a>
                    </div>
                </div>
                
                <!-- Finance -->
                <div class="nav-section">
                    <div class="nav-section-title">FINANCE</div>
                    
                    <div class="nav-item">
                        <a href="{{ route('admin.withdrawals.pending') }}" 
                           class="nav-link {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
                            <div class="nav-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <span class="nav-text">Withdrawals</span>
                            @php $pendingWithdrawals = \App\Models\VendorWithdrawal::whereIn('status', ['pending', 'processing'])->count(); @endphp
                            @if($pendingWithdrawals > 0)
                                <span class="badge">{{ $pendingWithdrawals }}</span>
                            @endif
                            <div class="nav-tooltip">Withdrawals</div>
                        </a>
                    </div>
                </div>
                
                <!-- Analytics -->
                <div class="nav-section">
                    <div class="nav-section-title">ANALYTICS</div>
                    
                    <div class="nav-item">
                        <a href="{{ route('admin.reports.index') }}" 
                           class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <div class="nav-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <span class="nav-text">Reports</span>
                            <div class="nav-tooltip">Reports</div>
                        </a>
                    </div>
                </div>
                
                <!-- Settings -->
                <div class="nav-section">
                    <div class="nav-section-title">SETTINGS</div>
                    
                    <div class="nav-item">
                        <a href="{{ route('admin.settings.index') }}" class="nav-link">
                            <div class="nav-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <span class="nav-text">Settings</span>
                            <div class="nav-tooltip">Settings</div>
                        </a>
                    </div>
                    
                    
                </div>
            </div>
            
            <!-- Bottom Section -->
            <div class="sidebar-bottom">
                <form id="dropdownLogoutForm" action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit" class="flex items-center gap-2 w-full px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
        <i class="fas fa-sign-out-alt w-4"></i>
        <span>Sign Out</span>
    </button>
</form>
                <div class="mt-3 text-center">
                    <div class="text-xs text-dark-500 dark:text-dark-400 font-mono">
                        {{ config('app.name') }} v1.0.0
                    </div>
                    <div class="text-xs text-dark-400 dark:text-dark-500 mt-1">
                        © {{ date('Y') }} All rights reserved
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Mobile Overlay -->
        <div id="mobileOverlay" class="mobile-overlay" onclick="toggleMobileSidebar()"></div>
    </div>

    <!-- Main Content -->
    <div id="mainContent" class="main-content">
        <!-- Header -->
        <header class="main-header glass">
            <div class="flex items-center justify-between w-full">
                <!-- Left Section -->
                <div class="flex items-center gap-4">
                    <!-- Mobile Menu Button -->
                    <button id="mobileMenuButton" class="lg:hidden p-2 rounded-lg hover:bg-dark-100 dark:hover:bg-dark-800 transition">
                        <i class="fas fa-bars text-dark-700 dark:text-dark-300"></i>
                    </button>
                    
                    <!-- Page Title -->
                    <div>
                        <h1 class="header-title">@yield('page-title', 'Dashboard')</h1>
                        <p class="text-sm text-dark-500 dark:text-dark-400">@yield('page-description', 'Welcome to the admin dashboard')</p>
                    </div>
                </div>
                
                <!-- Right Section -->
                <div class="header-controls">
                    <!-- Quick Search -->
                    <div class="relative hidden md:block">
                        <input type="text" 
                               placeholder="Search..." 
                               class="pl-10 pr-4 py-2 w-64 rounded-lg border border-dark-200 dark:border-dark-700 bg-white dark:bg-dark-800 text-dark-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-search text-dark-400 dark:text-dark-500"></i>
                        </div>
                    </div>
                    
                    <!-- Theme Toggle -->
                    <button id="themeToggle" class="theme-toggle">
                        <i class="fas fa-sun dark:fa-moon"></i>
                    </button>
                    
                    <!-- Notifications -->
                    <div class="relative">
                        <button id="notificationButton" class="notification-btn">
                            <i class="fas fa-bell"></i>
                            @php
                                $totalNotifications = $pendingWithdrawals + $pendingVendors + $pendingDisputes + $newMessages;
                            @endphp
                            @if($totalNotifications > 0)
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center animate-pulse">
                                    {{ $totalNotifications }}
                                </span>
                            @endif
                        </button>
                        
                        <!-- Notification Dropdown -->
                        <div id="notificationDropdown" class="absolute right-0 mt-2 w-80 bg-white dark:bg-dark-800 rounded-xl shadow-xl border border-dark-200 dark:border-dark-700 hidden z-50 animate-in">
                            <div class="p-4 border-b border-dark-200 dark:border-dark-700">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold text-dark-900 dark:text-white">Notifications</h3>
                                    @if($totalNotifications > 0)
                                        <button class="text-xs text-primary-600 hover:text-primary-700">Mark all as read</button>
                                    @endif
                                </div>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @if($totalNotifications > 0)
                                    <!-- Notification Items -->
                                    @if($newMessages > 0)
                                        <a href="{{ route('admin.contact-messages.index') }}" class="block p-3 border-b border-dark-100 dark:border-dark-700 hover:bg-dark-50 dark:hover:bg-dark-700 transition">
                                            <div class="flex items-start gap-3">
                                                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-envelope text-sm"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-sm text-dark-900 dark:text-white font-medium">{{ $newMessages }} new message(s)</p>
                                                    <p class="text-xs text-dark-500 dark:text-dark-400 mt-1">Check contact messages</p>
                                                </div>
                                                <span class="text-xs text-dark-400 dark:text-dark-500">Now</span>
                                            </div>
                                        </a>
                                    @endif
                                    
                                    @if($pendingVendors > 0)
                                        <a href="{{ route('admin.vendors.pending') }}" class="block p-3 border-b border-dark-100 dark:border-dark-700 hover:bg-dark-50 dark:hover:bg-dark-700 transition">
                                            <div class="flex items-start gap-3">
                                                <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-300 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-store text-sm"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-sm text-dark-900 dark:text-white font-medium">{{ $pendingVendors }} vendor application(s)</p>
                                                    <p class="text-xs text-dark-500 dark:text-dark-400 mt-1">Pending review</p>
                                                </div>
                                                <span class="text-xs text-dark-400 dark:text-dark-500">Now</span>
                                            </div>
                                        </a>
                                    @endif
                                    
                                    @if($pendingDisputes > 0)
                                        <a href="{{ route('admin.disputes.index') }}" class="block p-3 hover:bg-dark-50 dark:hover:bg-dark-700 transition">
                                            <div class="flex items-start gap-3">
                                                <div class="w-8 h-8 bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-exclamation-triangle text-sm"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-sm text-dark-900 dark:text-white font-medium">{{ $pendingDisputes }} open dispute(s)</p>
                                                    <p class="text-xs text-dark-500 dark:text-dark-400 mt-1">Requires attention</p>
                                                </div>
                                                <span class="text-xs text-dark-400 dark:text-dark-500">Now</span>
                                            </div>
                                        </a>
                                    @endif
                                @else
                                    <div class="p-8 text-center">
                                        <div class="w-12 h-12 bg-dark-100 dark:bg-dark-700 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-bell-slash text-dark-400 dark:text-dark-500"></i>
                                        </div>
                                        <p class="text-dark-500 dark:text-dark-400">No new notifications</p>
                                    </div>
                                @endif
                            </div>
                            @if($totalNotifications > 0)
                                <div class="p-3 border-t border-dark-200 dark:border-dark-700">
                                    <a href="#" class="block text-center text-sm text-primary-600 hover:text-primary-700 font-medium">
                                        View all notifications
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="relative">
                        <button id="userMenuButton" class="flex items-center gap-2 p-2 rounded-lg hover:bg-dark-100 dark:hover:bg-dark-800 transition">
                            <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-indigo-600 text-white rounded-lg flex items-center justify-center font-semibold">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div class="hidden md:block text-left">
                                <div class="text-sm font-medium text-dark-900 dark:text-white">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-dark-500 dark:text-dark-400">{{ ucfirst(Auth::user()->role) }}</div>
                            </div>
                            <i class="fas fa-chevron-down text-dark-400 dark:text-dark-500 text-xs"></i>
                        </button>
                        
                        <!-- User Dropdown -->
                        <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-white dark:bg-dark-800 rounded-xl shadow-xl border border-dark-200 dark:border-dark-700 hidden z-50 animate-in">
                            <div class="p-4 border-b border-dark-200 dark:border-dark-700">
                                <div class="font-medium text-dark-900 dark:text-white">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-dark-500 dark:text-dark-400">{{ Auth::user()->email }}</div>
                            </div>
                            <div class="p-2">
                                <a href="{{ route('admin.profile.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-dark-700 dark:text-dark-300 hover:bg-dark-100 dark:hover:bg-dark-700 rounded-lg transition">
                                    <i class="fas fa-user w-4 text-dark-400 dark:text-dark-500"></i>
                                    <span>My Profile</span>
                                </a>
                                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-dark-700 dark:text-dark-300 hover:bg-dark-100 dark:hover:bg-dark-700 rounded-lg transition">
                                    <i class="fas fa-cog w-4 text-dark-400 dark:text-dark-500"></i>
                                    <span>Settings</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Breadcrumb -->
        @hasSection('breadcrumb')
        <div class="px-6 py-3 bg-gradient-to-r from-dark-50 to-white dark:from-dark-900 dark:to-dark-800 border-b border-dark-200 dark:border-dark-700">
            <div class="flex items-center text-sm">
                @yield('breadcrumb')
            </div>
        </div>
        @endif

        <!-- Main Content Area -->
        <main class="p-4 sm:p-6 animate-in">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="mt-8 px-6 py-4 border-t border-dark-200 dark:border-dark-700">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="text-sm text-dark-500 dark:text-dark-400">
                    © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </div>
                <div class="flex items-center gap-6 text-sm text-dark-500 dark:text-dark-400">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-success rounded-full animate-pulse"></div>
                        <span>System: <span class="font-medium text-success">Operational</span></span>
                    </div>
                    <div class="hidden md:block">
                        <span class="font-mono">{{ now()->format('Y-m-d H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DOM Elements
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleBtn = document.getElementById('toggleSidebar');
            const mobileMenuBtn = document.getElementById('mobileMenuButton');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const themeToggle = document.getElementById('themeToggle');
            const notificationBtn = document.getElementById('notificationButton');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const userMenuBtn = document.getElementById('userMenuButton');
            const userDropdown = document.getElementById('userDropdown');
            
            // Sidebar state
            let isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            let isMobileSidebarOpen = false;
            
            // Initialize sidebar state
            function initSidebar() {
                if (window.innerWidth < 1024) {
                    // Mobile: always start collapsed
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                    isSidebarCollapsed = true;
                } else {
                    // Desktop: restore saved state
                    if (isSidebarCollapsed) {
                        collapseSidebar();
                    } else {
                        expandSidebar();
                    }
                }
            }
            
            // Collapse sidebar
            function collapseSidebar() {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                toggleBtn.innerHTML = '<i class="fas fa-chevron-right text-sm"></i>';
                localStorage.setItem('sidebarCollapsed', 'true');
                isSidebarCollapsed = true;
            }
            
            // Expand sidebar
            function expandSidebar() {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                toggleBtn.innerHTML = '<i class="fas fa-chevron-left text-sm"></i>';
                localStorage.setItem('sidebarCollapsed', 'false');
                isSidebarCollapsed = false;
            }
            
            // Toggle sidebar
            function toggleSidebar() {
                if (isSidebarCollapsed) {
                    expandSidebar();
                } else {
                    collapseSidebar();
                }
            }
            
            // Mobile sidebar functions
            function openMobileSidebar() {
                sidebar.classList.add('mobile-open');
                mobileOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                isMobileSidebarOpen = true;
            }
            
            function closeMobileSidebar() {
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = '';
                isMobileSidebarOpen = false;
            }
            
            function toggleMobileSidebar() {
                if (isMobileSidebarOpen) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            }
            
            // Theme toggle
            function toggleTheme() {
                const html = document.documentElement;
                const isDark = html.classList.contains('dark');
                
                if (isDark) {
                    html.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                    themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                } else {
                    html.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                    themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                }
            }
            
            // Load saved theme
            function initTheme() {
                const savedTheme = localStorage.getItem('theme') || 'light';
                const html = document.documentElement;
                
                if (savedTheme === 'dark') {
                    html.classList.add('dark');
                    themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                } else {
                    html.classList.remove('dark');
                    themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                }
            }
            
            // Close dropdowns
            function closeAllDropdowns() {
                notificationDropdown.classList.add('hidden');
                userDropdown.classList.add('hidden');
            }
            
            // Event Listeners
            toggleBtn.addEventListener('click', toggleSidebar);
            mobileMenuBtn.addEventListener('click', toggleMobileSidebar);
            mobileOverlay.addEventListener('click', closeMobileSidebar);
            themeToggle.addEventListener('click', toggleTheme);
            
            // Notification dropdown
            notificationBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.add('hidden');
                notificationDropdown.classList.toggle('hidden');
            });
            
            // User menu dropdown
            userMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.add('hidden');
                userDropdown.classList.toggle('hidden');
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.add('hidden');
                }
                if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.classList.add('hidden');
                }
            });
            
            // Set active nav item
            function setActiveNavItem() {
                const currentPath = window.location.pathname;
                const navLinks = document.querySelectorAll('.nav-link');
                
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    const href = link.getAttribute('href');
                    
                    if (href === currentPath || 
                        (href !== '/' && currentPath.startsWith(href))) {
                        link.classList.add('active');
                    }
                });
            }
            
            // Handle window resize
            function handleResize() {
                if (window.innerWidth >= 1024) {
                    // Desktop: close mobile sidebar if open
                    closeMobileSidebar();
                    initSidebar();
                } else {
                    // Mobile: always collapsed
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                    isSidebarCollapsed = true;
                }
            }
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl + B to toggle sidebar
                if (e.ctrlKey && e.key === 'b') {
                    e.preventDefault();
                    toggleSidebar();
                }
                
                // Escape to close dropdowns and mobile sidebar
                if (e.key === 'Escape') {
                    closeAllDropdowns();
                    if (window.innerWidth < 1024 && isMobileSidebarOpen) {
                        closeMobileSidebar();
                    }
                }
            });
            
            // Initialize
            initTheme();
            initSidebar();
            setActiveNavItem();
            window.addEventListener('resize', handleResize);
            
            // Auto-close notifications after 5 seconds
            setTimeout(() => {
                notificationDropdown.classList.add('hidden');
            }, 5000);
            
            // Add loading animation to form buttons (EXCLUDING LOGOUT)
document.querySelectorAll('form button[type="submit"]').forEach(button => {
    // Skip logout forms
    const form = button.closest('form');
    if (form && form.action.includes('logout')) {
        return; // Skip logout buttons
    }
    
    button.addEventListener('click', function() {
        const originalText = this.innerHTML;
        this.innerHTML = `
            <span class="flex items-center justify-center gap-2">
                <i class="fas fa-spinner fa-spin"></i>
                Processing...
            </span>
        `;
        this.disabled = true;
        
        // Revert after 1 seconds (fallback)
        setTimeout(() => {
            this.innerHTML = originalText;
            this.disabled = false;
        }, 1000);
    });
});
        });

        
    </script>
    
    @yield('scripts')
</body>
</html>