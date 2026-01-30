<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CEO Dashboard') - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
                            400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
                            800: '#3730a3', 900: '#312e81',
                        },
                        accent: {
                            50: '#faf5ff', 100: '#f3e8ff', 200: '#e9d5ff', 300: '#d8b4fe',
                            400: '#c084fc', 500: '#a855f7', 600: '#9333ea', 700: '#7c3aed',
                            800: '#6b21a8', 900: '#581c87',
                        },
                        dark: {
                            50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1',
                            400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155',
                            800: '#1e293b', 900: '#0f172a', 950: '#0a0f1a',
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0a0f1a; color: #e2e8f0; overflow-x: hidden; }
        
        /* Sidebar */
        .sidebar { 
            width: 260px; 
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1); 
            height: 100vh; 
            position: fixed; 
            left: 0; 
            top: 0; 
            z-index: 50; 
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            border-right: 1px solid rgba(99,102,241,0.15); 
        }
        .sidebar.collapsed { width: 70px; }
        
        .sidebar-header { 
            height: 70px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 0 1.5rem; 
            border-bottom: 1px solid rgba(99,102,241,0.15); 
        }
        
        .logo-container { display: flex; align-items: center; gap: 0.75rem; min-width: 0; }
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
            background: linear-gradient(135deg, #818cf8 0%, #a78bfa 100%); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            white-space: nowrap; 
        }
        .logo-subtitle { 
            font-size: 0.7rem; 
            font-weight: 600; 
            color: #818cf8; 
            letter-spacing: 0.1em; 
        }
        
        .sidebar.collapsed .logo-text, 
        .sidebar.collapsed .logo-subtitle, 
        .sidebar.collapsed .user-info, 
        .sidebar.collapsed .nav-text { display: none; }
        
        .toggle-btn { 
            width: 36px; 
            height: 36px; 
            border-radius: 8px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background: rgba(99,102,241,0.1); 
            border: 1px solid rgba(99,102,241,0.2); 
            color: #818cf8; 
            cursor: pointer; 
            transition: all 0.2s; 
            flex-shrink: 0; 
        }
        .toggle-btn:hover { background: rgba(99,102,241,0.2); }
        
        .user-profile { 
            padding: 1.25rem 1.5rem; 
            border-bottom: 1px solid rgba(99,102,241,0.1); 
            display: flex; 
            align-items: center; 
            gap: 0.75rem; 
        }
        .sidebar.collapsed .user-profile { padding: 1rem; justify-content: center; }
        
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
        .user-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 12px; }
        .user-status { 
            position: absolute; 
            bottom: -2px; 
            right: -2px; 
            width: 12px; 
            height: 12px; 
            background: #10b981; 
            border: 2px solid #1e293b; 
            border-radius: 50%; 
        }
        .user-name { font-weight: 600; color: #f1f5f9; font-size: 0.875rem; }
        .user-role { font-size: 0.75rem; color: #818cf8; margin-top: 0.125rem; }
        
        .nav-container { flex: 1; padding: 1rem 0; overflow-y: auto; height: calc(100vh - 270px); }
        .nav-item { margin: 0.25rem 0.75rem; }
        .nav-link { 
            display: flex; 
            align-items: center; 
            padding: 0.75rem 1rem; 
            border-radius: 8px; 
            color: #94a3b8; 
            text-decoration: none; 
            transition: all 0.2s; 
            position: relative; 
        }
        .nav-link:hover { background: rgba(99,102,241,0.08); color: #818cf8; }
        .nav-link.active { 
            background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(139,92,246,0.1)); 
            color: #818cf8; 
            border: 1px solid rgba(99,102,241,0.2); 
        }
        .nav-icon { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .nav-text { font-size: 0.875rem; font-weight: 500; margin-left: 0.75rem; white-space: nowrap; }
        .nav-tooltip { 
            position: absolute; 
            left: calc(100% + 10px); 
            top: 50%; 
            transform: translateY(-50%); 
            background: #1e293b; 
            color: #818cf8; 
            padding: 0.5rem 0.75rem; 
            border-radius: 6px; 
            font-size: 0.75rem; 
            font-weight: 500; 
            white-space: nowrap; 
            pointer-events: none; 
            opacity: 0; 
            transition: opacity 0.2s; 
            z-index: 1000; 
            border: 1px solid rgba(99,102,241,0.2); 
        }
        .sidebar.collapsed .nav-item:hover .nav-tooltip { opacity: 1; }
        
        .sidebar-bottom { padding: 1rem; border-top: 1px solid rgba(99,102,241,0.1); }
        
        /* Main content */
        .main-content { 
            margin-left: 260px; 
            transition: margin-left 0.3s cubic-bezier(0.4,0,0.2,1); 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
        }
        .main-content.expanded { margin-left: 70px; }
        
        .main-header { 
            height: 70px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 0 2rem; 
            background: rgba(15,23,42,0.8); 
            backdrop-filter: blur(16px); 
            border-bottom: 1px solid rgba(99,102,241,0.1); 
            position: sticky; 
            top: 0; 
            z-index: 40; 
        }
        .header-title { font-size: 1.25rem; font-weight: 600; color: #f1f5f9; }
        .header-description { font-size: 0.875rem; color: #64748b; margin-top: 0.125rem; }
        
        .content-wrapper { flex: 1; padding: 1.5rem; overflow-y: auto; }
        
        .mobile-overlay { 
            display: none; 
            position: fixed; 
            top: 0; left: 0; right: 0; bottom: 0; 
            background: rgba(0,0,0,0.6); 
            backdrop-filter: blur(4px); 
            z-index: 45; 
        }
        .mobile-menu-btn { 
            display: none; 
            width: 40px; 
            height: 40px; 
            border-radius: 8px; 
            background: rgba(99,102,241,0.1); 
            border: 1px solid rgba(99,102,241,0.2); 
            color: #818cf8; 
            align-items: center; 
            justify-content: center; 
            cursor: pointer; 
        }
        
        /* Filter & export bar */
        .filter-btn { 
            padding: 0.5rem 1rem; 
            border-radius: 6px; 
            font-size: 0.8rem; 
            font-weight: 500; 
            border: 1px solid rgba(99,102,241,0.2); 
            background: transparent; 
            color: #94a3b8; 
            cursor: pointer; 
            transition: all 0.2s; 
        }
        .filter-btn:hover { border-color: #6366f1; color: #818cf8; }
        .filter-btn.active { background: rgba(99,102,241,0.15); color: #818cf8; border-color: #6366f1; }
        
        .export-btn { 
            padding: 0.5rem 1rem; 
            border-radius: 6px; 
            font-size: 0.8rem; 
            font-weight: 500; 
            background: linear-gradient(135deg, #6366f1, #8b5cf6); 
            color: white; 
            cursor: pointer; 
            border: none; 
            transition: all 0.2s; 
        }
        .export-btn:hover { opacity: 0.9; transform: translateY(-1px); }
        
        /* KPI cards */
        .kpi-card { 
            background: rgba(30,41,59,0.6); 
            backdrop-filter: blur(16px); 
            border: 1px solid rgba(99,102,241,0.1); 
            border-radius: 12px; 
            padding: 1.25rem; 
            transition: all 0.3s; 
            position: relative; 
            overflow: hidden; 
        }
        .kpi-card:hover { border-color: rgba(99,102,241,0.3); transform: translateY(-2px); }
        .kpi-card::before { 
            content: ''; 
            position: absolute; 
            top: 0; left: 0; right: 0; 
            height: 2px; 
            background: linear-gradient(90deg, transparent, #6366f1, transparent); 
            opacity: 0; 
            transition: opacity 0.3s; 
        }
        .kpi-card:hover::before { opacity: 1; }
        
        /* Chart containers */
        .chart-card { 
            background: rgba(30,41,59,0.6); 
            backdrop-filter: blur(16px); 
            border: 1px solid rgba(99,102,241,0.1); 
            border-radius: 12px; 
            padding: 1.5rem; 
        }
        .chart-card:hover { border-color: rgba(99,102,241,0.2); }
        
        /* Tables */
        .data-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .data-table thead th { 
            padding: 0.75rem 1rem; 
            text-align: left; 
            font-size: 0.75rem; 
            font-weight: 600; 
            text-transform: uppercase; 
            letter-spacing: 0.05em; 
            color: #818cf8; 
            border-bottom: 1px solid rgba(99,102,241,0.15); 
        }
        .data-table tbody td { 
            padding: 0.75rem 1rem; 
            font-size: 0.875rem; 
            color: #cbd5e1; 
            border-bottom: 1px solid rgba(99,102,241,0.05); 
        }
        .data-table tbody tr:hover { background: rgba(99,102,241,0.05); }
        .data-table tbody tr:nth-child(even) { background: rgba(30,41,59,0.3); }
        .data-table tbody tr:nth-child(even):hover { background: rgba(99,102,241,0.05); }
        
        .tabular-nums { font-variant-numeric: tabular-nums; }
        
        /* Footer */
        .main-footer { 
            padding: 1rem 2rem; 
            border-top: 1px solid rgba(99,102,241,0.1); 
            background: rgba(15,23,42,0.8); 
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0a0f1a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
            .mobile-menu-btn { display: flex; }
        }
        @media (max-width: 768px) {
            .main-header { padding: 0 1rem; }
            .content-wrapper { padding: 1rem; }
            .filter-bar { flex-wrap: wrap; }
        }
        
        /* Status badges */
        .status-badge { 
            display: inline-flex; 
            align-items: center; 
            padding: 0.25rem 0.75rem; 
            border-radius: 9999px; 
            font-size: 0.75rem; 
            font-weight: 500; 
        }
        .status-pending { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .status-completed, .status-delivered { background: rgba(16,185,129,0.15); color: #34d399; }
        .status-failed, .status-cancelled { background: rgba(239,68,68,0.15); color: #f87171; }
        .status-processing { background: rgba(99,102,241,0.15); color: #818cf8; }
        
        /* Trend indicators */
        .trend-up { color: #34d399; }
        .trend-down { color: #f87171; }
        
        /* Dropdown */
        .export-dropdown { position: relative; display: inline-block; }
        .export-dropdown-content { 
            display: none; 
            position: absolute; 
            right: 0; 
            top: 100%; 
            margin-top: 0.5rem; 
            background: #1e293b; 
            border: 1px solid rgba(99,102,241,0.2); 
            border-radius: 8px; 
            min-width: 160px; 
            z-index: 50; 
            overflow: hidden; 
        }
        .export-dropdown-content a { 
            display: block; 
            padding: 0.625rem 1rem; 
            color: #cbd5e1; 
            text-decoration: none; 
            font-size: 0.8rem; 
            transition: all 0.2s; 
        }
        .export-dropdown-content a:hover { background: rgba(99,102,241,0.1); color: #818cf8; }
        .export-dropdown:hover .export-dropdown-content { display: block; }
        
        /* Icon colors for nav */
        .nav-link .fa-th-large { color: #818cf8; }
        .nav-link .fa-chart-line { color: #34d399; }
        .nav-link .fa-dollar-sign { color: #fbbf24; }
        .nav-link .fa-trophy { color: #f472b6; }
        .nav-link .fa-users { color: #60a5fa; }
        .nav-link .fa-store { color: #a78bfa; }
        .nav-link .fa-cog { color: #94a3b8; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="fas fa-crown text-white"></i>
                </div>
                <div>
                    <div class="logo-text">{{ config('app.name') }}</div>
                    <div class="logo-subtitle">CEO DASHBOARD</div>
                </div>
            </div>
            <button id="toggleSidebar" class="toggle-btn">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <div class="user-profile">
            <div class="user-avatar">
                @if(Auth::user()->profile_photo ?? false)
                    <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}">
                @else
                    <span class="text-white font-semibold text-lg">{{ substr(Auth::user()->name, 0, 1) }}</span>
                @endif
                <div class="user-status"></div>
            </div>
            <div class="user-info">
                <div class="user-name">{{ Auth::user()->name }}</div>
                <div class="user-role">Chief Executive</div>
            </div>
        </div>

        <div class="nav-container" id="sidebarNav">
            <div class="nav-item">
                <a href="{{ route('ceo.dashboard') }}" class="nav-link {{ request()->routeIs('ceo.dashboard') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="fas fa-th-large"></i></div>
                    <span class="nav-text">Dashboard</span>
                    <div class="nav-tooltip">Dashboard</div>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('ceo.analytics') }}" class="nav-link {{ request()->routeIs('ceo.analytics') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="fas fa-chart-line"></i></div>
                    <span class="nav-text">Analytics</span>
                    <div class="nav-tooltip">Analytics</div>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('ceo.financials') }}" class="nav-link {{ request()->routeIs('ceo.financials') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="fas fa-dollar-sign"></i></div>
                    <span class="nav-text">Financials</span>
                    <div class="nav-tooltip">Financials</div>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('ceo.performance') }}" class="nav-link {{ request()->routeIs('ceo.performance') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="fas fa-trophy"></i></div>
                    <span class="nav-text">Performance</span>
                    <div class="nav-tooltip">Performance</div>
                </a>
            </div>
            
            <!-- Divider -->
            <div class="my-4 mx-4 border-t border-slate-700/50"></div>
            
            <!-- Quick Access -->
            <div class="px-4 mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Quick Access</span>
            </div>
            <div class="nav-item">
                <a href="{{ route('ceo.users') }}" class="nav-link {{ request()->routeIs('ceo.users') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="fas fa-users"></i></div>
                    <span class="nav-text">Users</span>
                    <div class="nav-tooltip">Users</div>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('ceo.vendors') }}" class="nav-link {{ request()->routeIs('ceo.vendors') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="fas fa-store"></i></div>
                    <span class="nav-text">Vendors</span>
                    <div class="nav-tooltip">Vendors</div>
                </a>
            </div>
        </div>

        <div class="sidebar-bottom">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center justify-center gap-2 w-full p-2 text-red-400 hover:bg-red-900/20 rounded-lg transition text-sm">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sign Out</span>
                </button>
            </form>
            <div class="mt-3 text-center">
                <div class="text-xs text-slate-500 font-mono">{{ config('app.name') }} v1.0</div>
            </div>
        </div>
    </aside>

    <div id="mobileOverlay" class="mobile-overlay"></div>

    <div id="mainContent" class="main-content">
        <header class="main-header">
            <div class="flex items-center gap-4">
                <button id="mobileMenuButton" class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h1 class="header-title">@yield('page-title', 'Dashboard')</h1>
                    <p class="header-description">@yield('page-description', '')</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @php $currentPeriod = request('period', '30days'); @endphp
                <!-- Date Range Filter (Desktop) -->
                <div class="filter-bar hidden lg:flex items-center gap-2">
                    @foreach(['today' => 'Today', '7days' => '7D', '30days' => '30D', '90days' => '90D', 'year' => 'Year', 'all' => 'All'] as $key => $label)
                        <a href="?period={{ $key }}" class="filter-btn {{ $currentPeriod === $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
                <!-- Date Range Filter (Mobile) -->
                <div class="lg:hidden">
                    <select onchange="window.location.href='?period='+this.value" class="bg-slate-800 text-indigo-400 border border-indigo-500/30 rounded-md px-2 py-1.5 text-sm cursor-pointer focus:outline-none focus:border-indigo-500">
                        @foreach(['today' => 'Today', '7days' => '7 Days', '30days' => '30 Days', '90days' => '90 Days', 'year' => 'Year', 'all' => 'All Time'] as $key => $label)
                            <option value="{{ $key }}" {{ $currentPeriod === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Export Dropdown -->
                <div class="export-dropdown">
                    <button class="export-btn"><i class="fas fa-download mr-1"></i><span class="hidden sm:inline"> Export</span></button>
                    <div class="export-dropdown-content">
                        <a href="{{ route('ceo.export.' . (request()->route()->getName() === 'ceo.dashboard' ? 'dashboard' : (request()->route()->getName() === 'ceo.analytics' ? 'analytics' : (request()->route()->getName() === 'ceo.financials' ? 'financials' : 'performance'))), ['format' => 'csv', 'period' => $currentPeriod]) }}">
                            <i class="fas fa-file-csv mr-2"></i> Download CSV
                        </a>
                        <a href="{{ route('ceo.export.' . (request()->route()->getName() === 'ceo.dashboard' ? 'dashboard' : (request()->route()->getName() === 'ceo.analytics' ? 'analytics' : (request()->route()->getName() === 'ceo.financials' ? 'financials' : 'performance'))), ['format' => 'pdf', 'period' => $currentPeriod]) }}">
                            <i class="fas fa-file-pdf mr-2"></i> Print / PDF
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="content-wrapper">
            @yield('content')
        </div>

        <footer class="main-footer">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 text-xs text-slate-500">
                <span>&copy; {{ date('Y') }} {{ config('app.name') }}. Executive Dashboard.</span>
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 bg-emerald-400 rounded-full inline-block animate-pulse"></span> 
                        Live
                    </span>
                    <span class="font-mono">{{ now()->format('Y-m-d H:i') }}</span>
                </div>
            </div>
        </footer>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('toggleSidebar');
        const mobileMenuBtn = document.getElementById('mobileMenuButton');
        const mobileOverlay = document.getElementById('mobileOverlay');
        let isSidebarCollapsed = localStorage.getItem('ceoSidebarCollapsed') === 'true';
        let isMobileOpen = false;

        function collapseSidebar() {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
            toggleBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            localStorage.setItem('ceoSidebarCollapsed', 'true');
            isSidebarCollapsed = true;
        }
        function expandSidebar() {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('expanded');
            toggleBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            localStorage.setItem('ceoSidebarCollapsed', 'false');
            isSidebarCollapsed = false;
        }
        function initSidebar() {
            if (window.innerWidth < 1024) { collapseSidebar(); }
            else if (isSidebarCollapsed) { collapseSidebar(); }
            else { expandSidebar(); }
        }
        toggleBtn.addEventListener('click', function() { isSidebarCollapsed ? expandSidebar() : collapseSidebar(); });
        mobileMenuBtn.addEventListener('click', function() {
            if (isMobileOpen) { 
                sidebar.classList.remove('mobile-open'); 
                mobileOverlay.style.display = 'none'; 
                document.body.style.overflow = ''; 
                isMobileOpen = false; 
            } else { 
                sidebar.classList.add('mobile-open'); 
                mobileOverlay.style.display = 'block'; 
                document.body.style.overflow = 'hidden'; 
                isMobileOpen = true; 
            }
        });
        mobileOverlay.addEventListener('click', function() { 
            sidebar.classList.remove('mobile-open'); 
            mobileOverlay.style.display = 'none'; 
            document.body.style.overflow = ''; 
            isMobileOpen = false; 
        });
        window.addEventListener('resize', function() { 
            if (window.innerWidth >= 1024) { 
                sidebar.classList.remove('mobile-open'); 
                mobileOverlay.style.display = 'none'; 
                document.body.style.overflow = ''; 
                isMobileOpen = false; 
                initSidebar(); 
            } 
        });
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'b') { e.preventDefault(); isSidebarCollapsed ? expandSidebar() : collapseSidebar(); }
            if (e.key === 'Escape' && isMobileOpen) { 
                sidebar.classList.remove('mobile-open'); 
                mobileOverlay.style.display = 'none'; 
                document.body.style.overflow = ''; 
                isMobileOpen = false; 
            }
        });
        initSidebar();
    });
    </script>
    @yield('scripts')
</body>
</html>