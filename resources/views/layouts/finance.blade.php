<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Finance Dashboard') - {{ config('app.name') }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        .sidebar { width: 260px; height: 100vh; position: fixed; left: 0; top: 0; z-index: 50; background: white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.3s; }
        .sidebar.collapsed { width: 70px; }
        .main-content { margin-left: 260px; min-height: 100vh; transition: margin-left 0.3s; }
        .main-content.expanded { margin-left: 70px; }
        .nav-link { display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: 8px; color: #475569; transition: all 0.2s; }
        .nav-link:hover { background: #ecfdf5; color: #059669; }
        .nav-link.active { background: #10b981; color: white; }
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar">
        <!-- Header -->
        <div class="h-16 flex items-center justify-between px-4 border-b">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
                <div>
                    <div class="font-bold text-green-600">{{ config('app.name') }}</div>
                    <div class="text-xs text-gray-500 font-semibold">FINANCE</div>
                </div>
            </div>
        </div>

        <!-- User Profile -->
        <div class="p-4 border-b flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center text-white font-semibold">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div>
                <div class="font-semibold text-gray-900 text-sm">{{ Auth::user()->name }}</div>
                <div class="text-xs text-gray-500">Finance Manager</div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="p-4 space-y-1">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Main</p>

            <a href="{{ route('finance.dashboard') }}" class="nav-link {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt w-5"></i>
                <span class="ml-3">Dashboard</span>
            </a>

            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-3">Money Management</p>

            <a href="{{ route('finance.payouts.pending') }}" class="nav-link {{ request()->routeIs('finance.payouts.pending') ? 'active' : '' }}">
                <i class="fas fa-clock w-5"></i>
                <span class="ml-3">Pending Payouts</span>
                @php $pendingCount = \App\Models\VendorWithdrawal::whereIn('status', ['pending', 'processing'])->count(); @endphp
                @if($pendingCount > 0)
                    <span class="ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                @endif
            </a>

            <a href="{{ route('finance.payouts.index') }}" class="nav-link {{ request()->routeIs('finance.payouts.index') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave w-5"></i>
                <span class="ml-3">All Payouts</span>
            </a>

            <a href="{{ route('finance.escrows.index') }}" class="nav-link {{ request()->routeIs('finance.escrows.*') ? 'active' : '' }}">
                <i class="fas fa-lock w-5"></i>
                <span class="ml-3">Escrows</span>
                @php $heldCount = \App\Models\Escrow::where('status', 'held')->count(); @endphp
                @if($heldCount > 0)
                    <span class="ml-auto bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $heldCount }}</span>
                @endif
            </a>

            <a href="{{ route('finance.transactions.index') }}" class="nav-link {{ request()->routeIs('finance.transactions.*') ? 'active' : '' }}">
                <i class="fas fa-exchange-alt w-5"></i>
                <span class="ml-3">Transactions</span>
            </a>

            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-3">Reports</p>

            <a href="{{ route('finance.transactions.payments') }}" class="nav-link {{ request()->routeIs('finance.transactions.payments') ? 'active' : '' }}">
                <i class="fas fa-credit-card w-5"></i>
                <span class="ml-3">Payments</span>
            </a>
        </nav>

        <!-- Bottom -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center gap-2 w-full p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div id="mainContent" class="main-content">
        <!-- Header -->
        <header class="h-16 flex items-center justify-between px-6 bg-white border-b sticky top-0 z-40">
            <div class="flex items-center gap-4">
                <button id="mobileMenuBtn" class="lg:hidden p-2 rounded-lg bg-gray-100">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h1 class="font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-sm text-gray-500">@yield('page-description', '')</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">{{ now()->format('l, F j, Y') }}</span>
            </div>
        </header>

        <!-- Content -->
        <div class="p-6">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script>
        document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('mobile-open');
        });
    </script>

    @yield('scripts')
</body>
</html>
