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
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            overflow-x: hidden;
        }
        
        .dark body {
            background: #0f172a;
        }
        
        /* Glass effect */
        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .dark .glass {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 50;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .dark .sidebar {
            background: #1e293b;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }
        
        .sidebar.collapsed {
            width: 70px;
        }
        
        .sidebar-header {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .dark .sidebar-header {
            border-bottom: 1px solid #334155;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
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
        }
        
        .logo-text {
            font-size: 1.25rem;
            font-weight: 700;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sidebar.collapsed .logo-text {
            display: none;
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
        
        /* Toggle button */
        .toggle-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        
        .dark .toggle-btn {
            background: #334155;
            border: 1px solid #475569;
            color: #cbd5e1;
        }
        
        .toggle-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        /* User profile */
        .user-profile {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .dark .user-profile {
            border-bottom: 1px solid #334155;
        }
        
        .sidebar.collapsed .user-profile {
            padding: 1rem;
            justify-content: center;
        }
        
        .user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            position: relative;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }
        
        .user-status {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 12px;
            height: 12px;
            background: #10b981;
            border: 2px solid white;
            border-radius: 50%;
        }
        
        .dark .user-status {
            border-color: #1e293b;
        }
        
        .user-info {
            min-width: 0;
        }
        
        .sidebar.collapsed .user-info {
            display: none;
        }
        
        .user-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.875rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .dark .user-name {
            color: white;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.125rem;
        }
        
        .dark .user-role {
            color: #94a3b8;
        }
        
        /* Navigation */
        .nav-container {
            flex: 1;
            padding: 1rem 0;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .nav-container {
    flex: 1;
    padding: 1rem 0;
    overflow-y: auto;
    overflow-x: hidden;
    /* Add these properties for proper scrolling */
    height: calc(100vh - 270px); /* Adjust based on header and footer heights */
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on mobile */
}

/* When sidebar is collapsed */
.sidebar.collapsed .nav-container {
    height: calc(100vh - 200px); /* Less height needed when collapsed */
}

/* For mobile view */
@media (max-width: 1024px) {
    .nav-container {
        height: calc(100vh - 230px);
    }
}

/* Also add a separate class for the main content scroll */
.content-scroll {
    overflow-y: auto;
    height: calc(100vh - 140px); /* Adjust based on header height */
}

/* Update the content wrapper */
.content-wrapper {
    flex: 1;
    padding: 1.5rem;
    overflow-y: auto; /* Add this */
    max-height: calc(100vh - 140px); /* Add this */
}
        
        .nav-section {
            margin-bottom: 1.5rem;
        }
        
        .nav-section-title {
            padding: 0 1.5rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
        }
        
        .sidebar.collapsed .nav-section-title {
            display: none;
        }
        
        .nav-item {
            margin: 0.125rem 0.75rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            color: #475569;
            text-decoration: none;
            transition: all 0.2s;
            position: relative;
        }
        
        .dark .nav-link {
            color: #cbd5e1;
        }
        
        .nav-link:hover {
            background: #f1f5f9;
            color: #4f46e5;
        }
        
        .dark .nav-link:hover {
            background: #334155;
            color: #818cf8;
        }
        
        .nav-link.active {
            background: #6366f1;
            color: white;
        }
        
        .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .nav-text {
            font-size: 0.875rem;
            font-weight: 500;
            margin-left: 0.75rem;
            white-space: nowrap;
        }
        
        .sidebar.collapsed .nav-text {
            display: none;
        }
        
        .badge {
            margin-left: auto;
            background: #ef4444;
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            min-width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sidebar.collapsed .badge {
            position: absolute;
            top: -4px;
            right: -4px;
        }
        
        /* Tooltip for collapsed sidebar */
        .nav-tooltip {
            position: absolute;
            left: calc(100% + 10px);
            top: 50%;
            transform: translateY(-50%);
            background: #1e293b;
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
            z-index: 1000;
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
        
        /* Sidebar bottom */
        .sidebar-bottom {
            padding: 1rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .dark .sidebar-bottom {
            border-top: 1px solid #334155;
        }
        
        /* Main content */
        .main-content {
            margin-left: 260px;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-content.expanded {
            margin-left: 70px;
        }
        
        /* Header */
        .main-header {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 40;
        }
        
        .dark .main-header {
            background: #1e293b;
            border-bottom: 1px solid #334155;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .mobile-menu-btn {
            display: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .dark .mobile-menu-btn {
            background: #334155;
            border: 1px solid #475569;
            color: #cbd5e1;
        }
        
        .header-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .dark .header-title {
            color: white;
        }
        
        .header-description {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.25rem;
        }
        
        .dark .header-description {
            color: #94a3b8;
        }
        
        /* Header controls */
        .header-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .search-container {
            position: relative;
        }
        
        .search-input {
            width: 240px;
            padding: 0.5rem 2.5rem 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: white;
            font-size: 0.875rem;
        }
        
        .dark .search-input {
            background: #334155;
            border: 1px solid #475569;
            color: white;
        }
        
        .search-icon {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        
        .icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        
        .dark .icon-btn {
            background: #334155;
            border: 1px solid #475569;
            color: #cbd5e1;
        }
        
        .icon-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 18px;
            height: 18px;
            background: #ef4444;
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .user-menu-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border-radius: 8px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .dark .user-menu-btn {
            background: #334155;
            border: 1px solid #475569;
            color: #cbd5e1;
        }
        
        .user-menu-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .user-menu-info {
            text-align: left;
        }
        
        .user-menu-name {
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .user-menu-role {
            font-size: 0.75rem;
            color: #64748b;
        }
        
        .dark .user-menu-role {
            color: #94a3b8;
        }
        
        /* Dropdowns */
        .dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 0.5rem);
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            min-width: 280px;
            z-index: 50;
            overflow: hidden;
            display: none;
        }
        
        .dark .dropdown {
            background: #1e293b;
            border: 1px solid #334155;
        }
        
        .dropdown-header {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .dark .dropdown-header {
            border-bottom: 1px solid #334155;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #475569;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .dark .dropdown-item {
            color: #cbd5e1;
        }
        
        .dropdown-item:hover {
            background: #f8fafc;
        }
        
        .dark .dropdown-item:hover {
            background: #334155;
        }
        
        /* Main content area */
        .content-wrapper {
            flex: 1;
            padding: 1.5rem;
        }
        
        /* Footer */
        .main-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid #e2e8f0;
            background: white;
        }
        
        .dark .main-footer {
            background: #1e293b;
            border-top: 1px solid #334155;
        }
        
        /* Mobile overlay */
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 45;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .mobile-menu-btn {
                display: flex;
            }
            
            .search-input {
                width: 200px;
            }
        }
        
        @media (max-width: 768px) {
            .search-container {
                display: none;
            }
            
            .user-menu-info {
                display: none;
            }
            
            .main-header {
                padding: 0 1rem;
            }
            
            .content-wrapper {
                padding: 1rem;
            }
        }
        
        @media (max-width: 640px) {
            .header-controls {
                gap: 0.5rem;
            }
            
            .main-footer {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
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
            background: #334155;
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
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-in {
            animation: fadeIn 0.3s ease-out;
        }
        
        /* Loading animation */
        .loading-spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @stack('styles')
</head>
<body class="dark:bg-dark-950">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar dark:bg-dark-800">
        <!-- Header -->
        <div class="sidebar-header">
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="fas fa-store text-white"></i>
                </div>
                <div>
                    <div class="logo-text">{{ config('app.name') }}</div>
                    <div class="logo-subtitle">{{ Auth::user()->role === 'support' ? 'SUPPORT PANEL' : 'ADMIN PANEL' }}</div>
                </div>
            </div>
            <button id="toggleSidebar" class="toggle-btn">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        
        <!-- User Profile -->
        <div class="user-profile">
            <div class="user-avatar">
                @if(Auth::user()->profile_photo)
                    <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}">
                @else
                    <span class="text-white font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                @endif
                <div class="user-status"></div>
            </div>
            <div class="user-info">
                <div class="user-name">{{ Auth::user()->name }}</div>
                <div class="user-role">{{ Auth::user()->role === 'support' ? 'Support Agent' : ucfirst(Auth::user()->role) }}</div>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="nav-container" id="sidebarNav">
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
                
                @if(Auth::user()->role !== 'support')
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
                @endif

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

                @if(Auth::user()->role !== 'support')
                <div class="nav-item">
                    <a href="{{ route('admin.advertisements.index') }}"
                       class="nav-link {{ request()->routeIs('admin.advertisements.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <span class="nav-text">Advertisements</span>
                        <div class="nav-tooltip">Advertisements</div>
                    </a>
                </div>
                @endif

                @if(Auth::user()->role !== 'support')
                <div class="nav-item">
                    <a href="{{ route('admin.subscriptions.index') }}"
                       class="nav-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <span class="nav-text">Subscriptions</span>
                        @php $activeSubscriptions = \App\Models\VendorSubscription::where('status', 'active')->count(); @endphp
                        @if($activeSubscriptions > 0)
                            <span class="badge">{{ $activeSubscriptions }}</span>
                        @endif
                        <div class="nav-tooltip">Subscriptions</div>
                    </a>
                </div>
                @endif
            </div>

            @if(Auth::user()->role !== 'support')
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
            @endif
            
            @if(Auth::user()->role !== 'support')
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
            @endif
            
            <!-- Settings / Account -->
            <div class="nav-section">
                <div class="nav-section-title">{{ Auth::user()->role === 'support' ? 'ACCOUNT' : 'SETTINGS' }}</div>

                @if(Auth::user()->role !== 'support')
                <div class="nav-item">
                    <a href="{{ route('admin.settings.index') }}"
                       class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <span class="nav-text">Settings</span>
                        <div class="nav-tooltip">Settings</div>
                    </a>
                </div>
                @endif

                <div class="nav-item">
                    <a href="{{ route('admin.profile.index') }}" 
                       class="nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="nav-text">Profile</span>
                        <div class="nav-tooltip">Profile</div>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Bottom Section -->
        <div class="sidebar-bottom">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center justify-center gap-2 w-full p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                    <i class="fas fa-sign-out-alt"></i>
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
    <div id="mobileOverlay" class="mobile-overlay"></div>
    
    <!-- Main Content -->
    <div id="mainContent" class="main-content">
        <!-- Header -->
        <header class="main-header glass dark:bg-dark-800">
            <div class="header-left">
                <button id="mobileMenuButton" class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h1 class="header-title">@yield('page-title', 'Dashboard')</h1>
                    <p class="header-description">@yield('page-description', 'Welcome to the admin dashboard')</p>
                </div>
            </div>
            
            <div class="header-controls">
                <!-- Search -->
                <div class="search-container">
                    <input type="text" placeholder="Search..." class="search-input">
                    <div class="search-icon">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                
                <!-- Theme Toggle -->
                <button id="themeToggle" class="icon-btn">
                    <i class="fas fa-sun"></i>
                </button>
                
                <!-- Notifications -->
                <div class="relative">
                    <button id="notificationButton" class="icon-btn">
                        <i class="fas fa-bell"></i>
                        @php
                            $totalNotifications = ($pendingWithdrawals ?? 0) + ($pendingVendors ?? 0) + ($pendingDisputes ?? 0) + ($newMessages ?? 0);
                        @endphp
                        @if($totalNotifications > 0)
                            <span class="notification-badge">{{ $totalNotifications }}</span>
                        @endif
                    </button>
                    
                    <!-- Notification Dropdown -->
                    <div id="notificationDropdown" class="dropdown">
                        <div class="dropdown-header">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-dark-900 dark:text-white">Notifications</h3>
                                @if($totalNotifications > 0)
                                    <button class="text-sm text-primary-600 hover:text-primary-700">Mark all read</button>
                                @endif
                            </div>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            @if($totalNotifications > 0)
                                @if($newMessages > 0)
                                    <a href="{{ route('admin.contact-messages.index') }}" class="dropdown-item">
                                        <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ $newMessages }} new message(s)</p>
                                            <p class="text-sm text-dark-500 dark:text-dark-400">Check contact messages</p>
                                        </div>
                                    </a>
                                @endif
                                
                                @if($pendingVendors > 0)
                                    <a href="{{ route('admin.vendors.pending') }}" class="dropdown-item">
                                        <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-300 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ $pendingVendors }} vendor application(s)</p>
                                            <p class="text-sm text-dark-500 dark:text-dark-400">Pending review</p>
                                        </div>
                                    </a>
                                @endif
                                
                                @if($pendingDisputes > 0)
                                    <a href="{{ route('admin.disputes.index') }}" class="dropdown-item">
                                        <div class="w-8 h-8 bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ $pendingDisputes }} open dispute(s)</p>
                                            <p class="text-sm text-dark-500 dark:text-dark-400">Requires attention</p>
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
                            <div class="border-t border-dark-200 dark:border-dark-700">
                                <a href="#" class="block text-center py-3 text-sm text-primary-600 hover:text-primary-700 font-medium">
                                    View all notifications
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- User Menu -->
                <div class="relative">
                    <button id="userMenuButton" class="user-menu-btn">
                        <div class="user-menu-avatar">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="user-menu-info">
                            <div class="user-menu-name">{{ Auth::user()->name }}</div>
                            <div class="user-menu-role">{{ Auth::user()->role === 'support' ? 'Support Agent' : ucfirst(Auth::user()->role) }}</div>
                        </div>
                        <i class="fas fa-chevron-down text-sm"></i>
                    </button>
                    
                    <!-- User Dropdown -->
                    <div id="userDropdown" class="dropdown" style="min-width: 200px;">
                        <div class="dropdown-header">
                            <div class="font-medium">{{ Auth::user()->name }}</div>
                            <div class="text-sm text-dark-500 dark:text-dark-400">{{ Auth::user()->email }}</div>
                        </div>
                        <div>
                            <a href="{{ route('admin.profile.index') }}" class="dropdown-item">
                                <i class="fas fa-user text-dark-400 dark:text-dark-500"></i>
                                <span>My Profile</span>
                            </a>
                            @if(Auth::user()->role !== 'support')
                            <a href="{{ route('admin.settings.index') }}" class="dropdown-item">
                                <i class="fas fa-cog text-dark-400 dark:text-dark-500"></i>
                                <span>Settings</span>
                            </a>
                            @endif
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item w-full text-left text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Sign Out</span>
                                </button>
                            </form>
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
        
        <!-- Main Content -->
        <div class="content-wrapper" id="mainContentScroll">
            @yield('content')
        </div>
        
        <!-- Footer -->
        <footer class="main-footer glass dark:bg-dark-800">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="text-sm text-dark-500 dark:text-dark-400">
                    © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </div>
                <div class="flex items-center gap-6 text-sm text-dark-500 dark:text-dark-400">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-success rounded-full animate-pulse"></div>
                        <span>System: <span class="font-medium text-success">Operational</span></span>
                    </div>
                    <div class="hidden md:block font-mono">
                        {{ now()->format('Y-m-d H:i:s') }}
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
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
            const sidebarNav = document.getElementById('sidebarNav');
            const mainContentScroll = document.getElementById('mainContentScroll');
            
            // State
            let isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            let isMobileOpen = false;
            
            // Initialize sidebar
            function initSidebar() {
                if (window.innerWidth < 1024) {
                    // Mobile - sidebar hidden by default
                    collapseSidebar();
                    isSidebarCollapsed = true;
                } else {
                    // Desktop - restore saved state
                    if (isSidebarCollapsed) {
                        collapseSidebar();
                    } else {
                        expandSidebar();
                    }
                }
            }

            
if (sidebarNav) {
    sidebarNav.addEventListener('wheel', function(e) {
        // If the sidebar has scrollable content and we're at the top/bottom
        const isScrollingUp = e.deltaY < 0;
        const isScrollingDown = e.deltaY > 0;
        
        const isAtTop = sidebarNav.scrollTop === 0;
        const isAtBottom = sidebarNav.scrollTop + sidebarNav.clientHeight >= sidebarNav.scrollHeight - 1;
        
        // If we're at the top and scrolling up, or at bottom and scrolling down
        // don't prevent default to allow main page scroll
        if ((isAtTop && isScrollingUp) || (isAtBottom && isScrollingDown)) {
            return;
        }
        
        // Otherwise, prevent the event from bubbling to main content
        e.stopPropagation();
    });
}

// Also add touch event handling for mobile
if (sidebarNav) {
    let startY = 0;
    let scrolling = false;
    
    sidebarNav.addEventListener('touchstart', function(e) {
        startY = e.touches[0].clientY;
        scrolling = true;
    }, { passive: true });
    
    sidebarNav.addEventListener('touchmove', function(e) {
        if (!scrolling) return;
        
        const currentY = e.touches[0].clientY;
        const deltaY = startY - currentY;
        
        const isScrollingUp = deltaY < 0;
        const isScrollingDown = deltaY > 0;
        
        const isAtTop = sidebarNav.scrollTop === 0;
        const isAtBottom = sidebarNav.scrollTop + sidebarNav.clientHeight >= sidebarNav.scrollHeight - 1;
        
        // If we're at the boundary and trying to scroll further, allow page scroll
        if ((isAtTop && isScrollingUp) || (isAtBottom && isScrollingDown)) {
            scrolling = false;
        }
    }, { passive: true });
    
    sidebarNav.addEventListener('touchend', function() {
        scrolling = false;
    }, { passive: true });
}

            
            // Collapse sidebar
            function collapseSidebar() {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                toggleBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
                localStorage.setItem('sidebarCollapsed', 'true');
                isSidebarCollapsed = true;
            }
            
            // Expand sidebar
            function expandSidebar() {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                toggleBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
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
                mobileOverlay.style.display = 'block';
                document.body.style.overflow = 'hidden';
                isMobileOpen = true;
            }
            
            function closeMobileSidebar() {
                sidebar.classList.remove('mobile-open');
                mobileOverlay.style.display = 'none';
                document.body.style.overflow = '';
                isMobileOpen = false;
            }
            
            function toggleMobileSidebar() {
                if (isMobileOpen) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            }
            
            // Theme toggle
            function toggleTheme() {
                const html = document.documentElement;
                if (html.classList.contains('dark')) {
                    html.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                    themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                } else {
                    html.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                    themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                }
            }
            
            // Initialize theme
            function initTheme() {
                const savedTheme = localStorage.getItem('theme') || 'light';
                if (savedTheme === 'dark') {
                    document.documentElement.classList.add('dark');
                    themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                } else {
                    document.documentElement.classList.remove('dark');
                    themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                }
            }
            
            // Close dropdowns
            function closeAllDropdowns() {
                notificationDropdown.style.display = 'none';
                userDropdown.style.display = 'none';
            }
            
            // Event listeners
            toggleBtn.addEventListener('click', toggleSidebar);
            mobileMenuBtn.addEventListener('click', toggleMobileSidebar);
            mobileOverlay.addEventListener('click', closeMobileSidebar);
            themeToggle.addEventListener('click', toggleTheme);
            
            // Notifications dropdown
            notificationBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.style.display = 'none';
                notificationDropdown.style.display = notificationDropdown.style.display === 'block' ? 'none' : 'block';
            });
            
            // User menu dropdown
            userMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.style.display = 'none';
                userDropdown.style.display = userDropdown.style.display === 'block' ? 'none' : 'block';
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function() {
                closeAllDropdowns();
            });
            
            // Prevent dropdown close when clicking inside
            [notificationDropdown, userDropdown].forEach(dropdown => {
                dropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });
            
            // Handle window resize
            function handleResize() {
                if (window.innerWidth >= 1024) {
                    closeMobileSidebar();
                    initSidebar();
                } else {
                    collapseSidebar();
                }
            }
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl + B to toggle sidebar
                if (e.ctrlKey && e.key === 'b') {
                    e.preventDefault();
                    toggleSidebar();
                }
                
                // Escape to close everything
                if (e.key === 'Escape') {
                    closeAllDropdowns();
                    if (window.innerWidth < 1024 && isMobileOpen) {
                        closeMobileSidebar();
                    }
                }
            });
            
            // Initialize
            initTheme();
            initSidebar();
            window.addEventListener('resize', handleResize);
            
            // Auto-hide notifications after 5 seconds
            setTimeout(() => {
                notificationDropdown.style.display = 'none';
            }, 5000);
            
            // Loading animation for forms (excluding logout)
            document.querySelectorAll('form').forEach(form => {
                if (!form.action.includes('logout')) {
                    form.addEventListener('submit', function() {
                        const submitBtn = this.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.innerHTML = `
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    Processing...
                                </span>
                            `;
                            submitBtn.disabled = true;
                        }
                    });
                }
            });
        });
    </script>
    
    @yield('scripts')
    @stack('scripts')
</body>
</html>