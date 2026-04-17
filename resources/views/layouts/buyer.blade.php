<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Buyer Dashboard - BebaMart')</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.png') }}?v=2">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --sidebar-w: 268px;
        --sidebar-bg: #0f172a;
        --accent: #6366f1;
        --accent-dark: #4f46e5;
        --accent-light: rgba(99,102,241,.15);
        --text-muted: rgba(255,255,255,.45);
        --border-subtle: rgba(255,255,255,.07);
        --mobile-nav-h: 68px;
        --topbar-h: 68px;
    }

    html, body {
        font-family: 'Inter', system-ui, sans-serif;
        background: #f1f5f9;
        color: #1e293b;
    }

    /* ── SIDEBAR ─────────────────────────────────────────────── */
    .sidebar {
        position: fixed;
        top: 0; left: 0;
        width: var(--sidebar-w);
        height: 100vh;
        background: var(--sidebar-bg);
        display: flex;
        flex-direction: column;
        z-index: 60;
        transition: transform .3s cubic-bezier(.4,0,.2,1);
        overflow: hidden;
    }

    /* Decorative blur circle */
    .sidebar::before {
        content:'';
        position:absolute;
        top:-60px; left:-60px;
        width:220px; height:220px;
        background: radial-gradient(circle, rgba(99,102,241,.25) 0%, transparent 70%);
        pointer-events:none;
    }
    .sidebar::after {
        content:'';
        position:absolute;
        bottom:-40px; right:-40px;
        width:160px; height:160px;
        background: radial-gradient(circle, rgba(139,92,246,.2) 0%, transparent 70%);
        pointer-events:none;
    }

    /* ── Brand ── */
    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 20px 18px;
        border-bottom: 1px solid var(--border-subtle);
        text-decoration: none;
        flex-shrink: 0;
    }
    .brand-icon {
        width: 42px; height: 42px;
        background: linear-gradient(135deg, var(--accent) 0%, #8b5cf6 100%);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 4px 16px rgba(99,102,241,.4);
        flex-shrink: 0;
    }
    .brand-icon i { color: #fff; font-size: 18px; }
    .brand-name { font-size: 17px; font-weight: 800; color: #fff; line-height: 1.1; }
    .brand-sub  { font-size: 10px; color: var(--text-muted); font-weight: 500; letter-spacing: .5px; text-transform: uppercase; }

    /* Close btn (mobile only) */
    .sidebar-close {
        position: absolute;
        top: 16px; right: 14px;
        display: none;
        background: rgba(255,255,255,.08);
        border: none; cursor: pointer;
        width: 32px; height: 32px;
        border-radius: 10px;
        color: #fff;
        align-items: center; justify-content: center;
        font-size: 14px;
        transition: background .2s;
    }
    .sidebar-close:hover { background: rgba(255,255,255,.16); }

    /* ── User / Wallet block ── */
    .sidebar-user {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-subtle);
        flex-shrink: 0;
    }
    .user-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }
    .user-avatar {
        width: 44px; height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        border: 2.5px solid rgba(99,102,241,.4);
        overflow: hidden;
    }
    .user-avatar img { width:100%; height:100%; object-fit:cover; }
    .user-avatar i   { color:#fff; font-size:17px; }
    .user-name  { font-size: 15px; font-weight: 800; color: #fff; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; letter-spacing: -.2px; }
    .user-role  { font-size: 11px; color: var(--text-muted); font-weight: 500; }

    .wallet-card {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 14px;
        padding: 13px 14px;
        box-shadow: 0 4px 16px rgba(16,185,129,.3);
        cursor: pointer;
        text-decoration: none;
        display: block;
        transition: transform .2s, box-shadow .2s;
    }
    .wallet-card:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,185,129,.4); }
    .wallet-label  { font-size: 10px; color: rgba(255,255,255,.75); font-weight: 600; letter-spacing: .4px; text-transform: uppercase; margin-bottom: 4px; }
    .wallet-amount { font-size: 20px; font-weight: 800; color: #fff; }
    .wallet-link   { font-size: 11px; color: rgba(255,255,255,.8); margin-top: 5px; display: inline-flex; align-items: center; gap: 4px; }
    .wallet-link:hover { color: #fff; }

    /* ── Nav ── */
    .sidebar-nav {
        flex: 1;
        overflow-y: auto;
        padding: 10px 0 12px;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,.1) transparent;
    }
    .sidebar-nav::-webkit-scrollbar { width: 4px; }
    .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 2px; }

    .nav-section {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .8px;
        text-transform: uppercase;
        color: var(--text-muted);
        padding: 16px 20px 6px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 10px 14px 10px 20px;
        margin: 1px 10px;
        border-radius: 12px;
        color: rgba(255,255,255,.65);
        font-size: 13.5px;
        font-weight: 500;
        text-decoration: none;
        transition: background .2s, color .2s;
        position: relative;
    }
    .nav-link:hover {
        background: rgba(255,255,255,.07);
        color: rgba(255,255,255,.95);
    }
    .nav-link.active {
        background: var(--accent-light);
        color: #fff;
        font-weight: 600;
    }
    .nav-link.active::before {
        content: '';
        position: absolute;
        left: -10px;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 60%;
        background: var(--accent);
        border-radius: 0 4px 4px 0;
    }
    .nav-icon {
        width: 34px; height: 34px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        background: rgba(255,255,255,.06);
        flex-shrink: 0;
        transition: background .2s;
    }
    .nav-link:hover .nav-icon { background: rgba(255,255,255,.1); }
    .nav-link.active .nav-icon { background: var(--accent); box-shadow: 0 3px 10px rgba(99,102,241,.4); color: #fff; }

    .nav-badge {
        margin-left: auto;
        min-width: 20px; height: 20px;
        padding: 0 6px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    /* ── Sidebar footer ── */
    .sidebar-footer {
        padding: 14px 16px;
        border-top: 1px solid var(--border-subtle);
        flex-shrink: 0;
    }
    .btn-logout {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%;
        padding: 10px;
        border-radius: 12px;
        background: rgba(239,68,68,.12);
        color: rgba(255,100,100,.9);
        font-size: 13px; font-weight: 600;
        border: none; cursor: pointer;
        transition: background .2s;
        text-decoration: none;
    }
    .btn-logout:hover { background: rgba(239,68,68,.22); color: #fca5a5; }

    /* ── OVERLAY ─────────────────────────────────────────────── */
    .sidebar-overlay {
        display: none;
        position: fixed; inset: 0;
        background: rgba(15,23,42,.55);
        backdrop-filter: blur(3px);
        z-index: 55;
    }
    .sidebar-overlay.show { display: block; }

    /* ── MAIN CONTENT ────────────────────────────────────────── */
    .main-wrap {
        margin-left: var(--sidebar-w);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        transition: margin-left .3s ease;
    }

    /* ── TOPBAR (desktop) ─────────────────────────────────────── */
    .topbar {
        position: sticky; top: 0;
        height: var(--topbar-h);
        background: rgba(241,245,249,.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(0,0,0,.06);
        display: flex; align-items: center;
        padding: 0 28px;
        gap: 16px;
        z-index: 30;
    }
    .topbar-title { flex: 1; }
    .topbar-title h1 { font-size: 20px; font-weight: 800; color: #0f172a; line-height: 1.2; }
    .topbar-title p  { font-size: 12px; color: #94a3b8; margin-top: 1px; }

    .search-wrap {
        display: flex;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        transition: border-color .2s, box-shadow .2s;
    }
    .search-wrap:focus-within {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(99,102,241,.12);
    }
    .search-wrap input {
        border: none; outline: none;
        padding: 9px 14px;
        width: 220px;
        font-size: 13px;
        background: transparent;
        color: #1e293b;
    }
    .search-wrap button {
        background: var(--accent);
        border: none; cursor: pointer;
        padding: 9px 14px;
        color: #fff;
        font-size: 13px;
        transition: background .2s;
    }
    .search-wrap button:hover { background: var(--accent-dark); }

    .topbar-action {
        position: relative;
        width: 42px; height: 42px;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: #64748b;
        text-decoration: none;
        transition: border-color .2s, color .2s, box-shadow .2s;
    }
    .topbar-action:hover {
        border-color: var(--accent);
        color: var(--accent);
        box-shadow: 0 0 0 3px rgba(99,102,241,.1);
    }
    .topbar-badge {
        position: absolute;
        top: -5px; right: -5px;
        min-width: 18px; height: 18px;
        padding: 0 4px;
        background: #ef4444;
        color: #fff;
        font-size: 10px; font-weight: 700;
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        border: 2px solid #f1f5f9;
    }

    /* ── MOBILE TOP BAR ──────────────────────────────────────── */
    .mobile-topbar {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0;
        height: 58px;
        background: var(--sidebar-bg);
        align-items: center;
        padding: 0 16px;
        gap: 12px;
        z-index: 50;
        box-shadow: 0 2px 12px rgba(0,0,0,.3);
    }
    .mobile-menu-btn {
        width: 38px; height: 38px;
        border-radius: 11px;
        background: rgba(255,255,255,.08);
        border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        font-size: 16px;
        flex-shrink: 0;
        transition: background .2s;
    }
    .mobile-menu-btn:hover { background: rgba(255,255,255,.15); }
    .mobile-brand {
        flex: 1;
        display: flex; align-items: center; gap: 8px;
        text-decoration: none;
    }
    .mobile-brand-icon {
        width: 30px; height: 30px;
        background: linear-gradient(135deg, var(--accent), #8b5cf6);
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
    }
    .mobile-brand-icon i { color: #fff; font-size: 13px; }
    .mobile-brand-name { font-size: 15px; font-weight: 800; color: #fff; }
    .mobile-cart-btn {
        position: relative;
        width: 38px; height: 38px;
        border-radius: 11px;
        background: rgba(255,255,255,.08);
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        font-size: 16px;
        text-decoration: none;
        flex-shrink: 0;
    }

    /* ── MOBILE BOTTOM NAV ──────────────────────────────────── */
    .mobile-bottom-nav {
        display: none;
        position: fixed;
        bottom: 0; left: 0; right: 0;
        background: #fff;
        border-top: 1px solid #e2e8f0;
        padding: 6px 0 max(6px, env(safe-area-inset-bottom));
        z-index: 50;
        box-shadow: 0 -4px 24px rgba(0,0,0,.08);
    }
    .bottom-nav-inner { display: flex; }
    .bottom-nav-item {
        flex: 1;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: 5px 2px;
        color: #94a3b8;
        font-size: 10px; font-weight: 600;
        text-decoration: none;
        transition: color .2s;
        position: relative;
        gap: 3px;
    }
    .bottom-nav-item i { font-size: 19px; }
    .bottom-nav-item.active { color: var(--accent); }
    .bottom-nav-item.active i {
        filter: drop-shadow(0 2px 6px rgba(99,102,241,.4));
    }
    .bottom-nav-dot {
        position: absolute;
        top: 2px; right: calc(50% - 20px);
        min-width: 15px; height: 15px;
        padding: 0 3px;
        background: #ef4444;
        color: #fff;
        font-size: 9px; font-weight: 700;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        border: 2px solid #fff;
    }

    /* ── MAIN CONTENT AREA ──────────────────────────────────── */
    .content-area {
        flex: 1;
        padding: 20px 24px;
        padding-bottom: 32px;
    }

    /* ── ALERT STYLES ───────────────────────────────────────── */
    .alert {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 14px 18px;
        border-radius: 14px;
        margin-bottom: 20px;
        font-size: 14px;
        font-weight: 500;
    }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
    .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
    .alert-info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; }
    .alert-warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }

    /* ── CARDS ──────────────────────────────────────────────── */
    .card {
        background: #fff;
        border-radius: 18px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        transition: box-shadow .25s, transform .2s;
    }
    .card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.07); }

    /* ── RESPONSIVE ─────────────────────────────────────────── */
    @media (max-width: 1024px) {
        .sidebar { transform: translateX(-100%); }
        .sidebar.open { transform: translateX(0); }
        .sidebar-close { display: flex; }
        .main-wrap { margin-left: 0; }
        .topbar { display: none; }
        .mobile-topbar { display: flex; }
        .mobile-bottom-nav { display: block; }
        .content-area {
            padding: 16px;
            padding-top: calc(58px + 16px);
            padding-bottom: calc(var(--mobile-nav-h) + 12px);
        }
    }
    @media (max-width: 640px) {
        .content-area { padding: 12px; padding-top: calc(58px + 12px); padding-bottom: calc(var(--mobile-nav-h) + 10px); }
    }

    /* Scrollbar (content area) */
    .content-area::-webkit-scrollbar { width: 5px; }
    .content-area::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 3px; }

    @stack('layout-styles')
    </style>

    @yield('styles')
    @stack('styles')
</head>
<body>

@php
    $cartCount = Auth::user()->cart ? count(Auth::user()->cart->items ?? []) : 0;
@endphp

{{-- ── Mobile top bar ─────────────────────────────────────────── --}}
<header class="mobile-topbar">
    <button class="mobile-menu-btn" onclick="openSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <a href="{{ route('buyer.dashboard') }}" class="mobile-brand">
        <div class="mobile-brand-icon"><i class="fas fa-shopping-bag"></i></div>
        <span class="mobile-brand-name">BebaMart</span>
    </a>
    <a href="{{ route('buyer.cart.index') }}" class="mobile-cart-btn">
        <i class="fas fa-shopping-cart"></i>
        @if($cartCount > 0)
        <span class="topbar-badge cart-count">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
        @endif
    </a>
</header>

{{-- ── Overlay ─────────────────────────────────────────────────── --}}
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

<div class="flex">
    {{-- ── SIDEBAR ────────────────────────────────────────────── --}}
    <aside class="sidebar" id="sidebar">
        <button class="sidebar-close" onclick="closeSidebar()">
            <i class="fas fa-times"></i>
        </button>

        {{-- Brand --}}
        <a href="{{ route('buyer.dashboard') }}" class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-shopping-bag"></i></div>
            <div>
                <div class="brand-name">BebaMart</div>
                <div class="brand-sub">Buyer Portal</div>
            </div>
        </a>

        {{-- User + Wallet --}}
        <div class="sidebar-user">
            <div class="user-row">
                <div class="user-avatar">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="">
                    @else
                        <i class="fas fa-user"></i>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-role">{{ Auth::user()->isVendor() ? 'Vendor Account' : 'Buyer Account' }}</div>
                </div>
                <a href="{{ route('buyer.profile') }}" class="w-7 h-7 rounded-lg bg-white/8 flex items-center justify-center text-white/50 hover:text-white hover:bg-white/15 transition text-xs flex-shrink-0" title="Edit Profile">
                    <i class="fas fa-pen"></i>
                </a>
            </div>

            @php
                $wallet  = Auth::user()->buyerWallet;
                $balance = $wallet ? $wallet->balance : 0;
            @endphp
            <a href="{{ route('buyer.wallet.index') }}" class="wallet-card">
                <div class="flex items-center justify-between">
                    <div class="wallet-label">Wallet Balance</div>
                    <i class="fas fa-wallet text-white/60 text-sm"></i>
                </div>
                <div class="wallet-amount">UGX {{ number_format($balance, 0) }}</div>
                <div class="wallet-link">Add funds <i class="fas fa-arrow-right text-xs"></i></div>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="sidebar-nav">
            <a href="{{ route('buyer.dashboard') }}"
               class="nav-link {{ request()->routeIs('buyer.dashboard') ? 'active' : '' }}">
                <div class="nav-icon"><i class="fas fa-grid-2 fa-fw"></i></div>
                Dashboard
            </a>

            <a href="{{ route('marketplace.index') }}"
               class="nav-link {{ request()->routeIs('marketplace.*') && !request()->routeIs('buyer.cart.*') ? 'active' : '' }}">
                <div class="nav-icon"><i class="fas fa-store fa-fw"></i></div>
                Marketplace
            </a>

            <div class="nav-section">Shopping</div>

            <a href="{{ route('buyer.cart.index') }}"
               class="nav-link {{ request()->routeIs('buyer.cart.*') ? 'active' : '' }}">
                <div class="nav-icon"><i class="fas fa-shopping-cart fa-fw"></i></div>
                Cart
                @if($cartCount > 0)
                <span class="nav-badge bg-red-500 text-white cart-count">{{ $cartCount }}</span>
                @endif
            </a>

            <a href="{{ route('buyer.orders.index') }}"
               class="nav-link {{ request()->routeIs('buyer.orders.*') ? 'active' : '' }}">
                <div class="nav-icon"><i class="fas fa-shopping-bag fa-fw"></i></div>
                My Orders
            </a>

            <a href="{{ route('buyer.wishlist.index') }}"
               class="nav-link {{ request()->routeIs('buyer.wishlist.*') ? 'active' : '' }}">
                <div class="nav-icon"><i class="fas fa-heart fa-fw"></i></div>
                Wishlist
            </a>

            <div class="nav-section">Jobs & Services</div>

            <a href="{{ route('jobs.index') }}"
               class="nav-link {{ request()->is('jobs') ? 'active' : '' }}">
                <div class="nav-icon"><i class="fas fa-briefcase fa-fw"></i></div>
                Browse Jobs
            </a>

            <a href="{{ route('services.index') }}"
               class="nav-link {{ request()->is('services') ? 'active' : '' }}">
                <div class="nav-icon"><i class="fas fa-tools fa-fw"></i></div>
                Browse Services
            </a>

            <a href="{{ route('buyer.applications.index') }}"
               class="nav-link {{ request()->is('buyer/my-applications*') ? 'active' : '' }}">
                <div class="nav-icon"><i class="fas fa-file-alt fa-fw"></i></div>
                My Applications
                @php
                    $activeApps = \App\Models\JobApplication::where('user_id', auth()->id())
                        ->whereIn('status', ['pending', 'reviewed', 'shortlisted'])->count();
                @endphp
                @if($activeApps > 0)
                <span class="nav-badge bg-indigo-500 text-white">{{ $activeApps }}</span>
                @endif
            </a>

            <a href="{{ route('buyer.service-requests.index') }}"
               class="nav-link {{ request()->is('buyer/service-requests*') ? 'active' : '' }}">
                <div class="nav-icon"><i class="fas fa-clipboard-list fa-fw"></i></div>
                Service Requests
                @php
                    $activeRequests = \App\Models\ServiceRequest::where('user_id', auth()->id())
                        ->whereIn('status', ['pending', 'quoted', 'accepted', 'in_progress'])->count();
                @endphp
                @if($activeRequests > 0)
                <span class="nav-badge bg-green-500 text-white">{{ $activeRequests }}</span>
                @endif
            </a>

            <div class="nav-section">Account</div>

            <a href="{{ route('chat.index') }}"
               class="nav-link {{ request()->is('chat*') ? 'active' : '' }}">
                <div class="nav-icon"><i class="fas fa-comments fa-fw"></i></div>
                Messages
                <span id="chatBadge" class="nav-badge bg-red-500 text-white hidden">0</span>
            </a>

            <a href="{{ route('buyer.wallet.index') }}"
               class="nav-link {{ request()->routeIs('buyer.wallet.*') ? 'active' : '' }}">
                <div class="nav-icon"><i class="fas fa-wallet fa-fw"></i></div>
                Wallet
            </a>

            <a href="{{ route('buyer.disputes.index') }}"
               class="nav-link {{ request()->routeIs('buyer.disputes.*') ? 'active' : '' }}">
                <div class="nav-icon"><i class="fas fa-shield-halved fa-fw"></i></div>
                Disputes
            </a>

            <a href="{{ route('buyer.profile') }}"
               class="nav-link {{ request()->routeIs('buyer.profile') ? 'active' : '' }}">
                <div class="nav-icon"><i class="fas fa-user-circle fa-fw"></i></div>
                Profile
            </a>

            <div class="nav-section">Quick Links</div>

            <a href="{{ route('categories.index') }}" class="nav-link">
                <div class="nav-icon"><i class="fas fa-tags fa-fw"></i></div>
                Categories
            </a>

            @if(Auth::user()->isVendor())
            <a href="{{ route('vendor.dashboard') }}" class="nav-link">
                <div class="nav-icon"><i class="fas fa-chart-line fa-fw"></i></div>
                Vendor Dashboard
            </a>
            <a href="{{ route('vendor.listings.index') }}" class="nav-link">
                <div class="nav-icon"><i class="fas fa-box-open fa-fw"></i></div>
                My Products
            </a>
            <a href="{{ route('vendor.orders.index') }}" class="nav-link">
                <div class="nav-icon"><i class="fas fa-receipt fa-fw"></i></div>
                My Sales
            </a>
            @else
            <a href="{{ route('vendor.onboard.create') }}" class="nav-link">
                <div class="nav-icon"><i class="fas fa-store-alt fa-fw"></i></div>
                Become a Seller
            </a>
            @endif
        </nav>

        {{-- Logout --}}
        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="fas fa-arrow-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- ── MAIN CONTENT ────────────────────────────────────── --}}
    <div class="main-wrap flex-1">

        {{-- Desktop topbar --}}
        <header class="topbar">
            <div class="topbar-title">
                <h1>@yield('page_title', 'Dashboard')</h1>
                <p>@yield('page_description', 'Welcome back, ' . Auth::user()->name)</p>
            </div>

            <form action="{{ route('marketplace.index') }}" method="GET" class="search-wrap">
                <input type="text" name="search" placeholder="Search products…" autocomplete="off">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>

            <a href="{{ route('chat.index') }}" class="topbar-action" title="Messages">
                <i class="fas fa-comments"></i>
                <span id="chatBadgeTop" class="topbar-badge hidden">0</span>
            </a>

            <a href="{{ route('buyer.wishlist.index') }}" class="topbar-action" title="Wishlist">
                <i class="fas fa-heart"></i>
            </a>

            <a href="{{ route('buyer.cart.index') }}" class="topbar-action" title="Cart">
                <i class="fas fa-shopping-cart"></i>
                @if($cartCount > 0)
                <span class="topbar-badge cart-count">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
                @endif
            </a>
        </header>

        {{-- Flash alerts --}}
        <div id="flash-area" style="padding: 0 28px; display:none;" class="pt-5">
            @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle text-lg flex-shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle text-lg flex-shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif
            @if(session('info'))
            <div class="alert alert-info">
                <i class="fas fa-info-circle text-lg flex-shrink-0"></i>
                <span>{{ session('info') }}</span>
            </div>
            @endif
            @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle text-lg flex-shrink-0"></i>
                <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif
        </div>

        {{-- Page content --}}
        <div class="content-area">
            @yield('content')
        </div>
    </div>
</div>

{{-- Mobile bottom nav --}}
<nav class="mobile-bottom-nav">
    <div class="bottom-nav-inner">
        <a href="{{ route('buyer.dashboard') }}" class="bottom-nav-item {{ request()->routeIs('buyer.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i><span>Home</span>
        </a>
        <a href="{{ route('marketplace.index') }}" class="bottom-nav-item {{ request()->routeIs('marketplace.*') && !request()->routeIs('buyer.cart.*') ? 'active' : '' }}">
            <i class="fas fa-store"></i><span>Shop</span>
        </a>
        <a href="{{ route('buyer.cart.index') }}" class="bottom-nav-item {{ request()->routeIs('buyer.cart.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart"></i>
            <span>Cart</span>
            @if($cartCount > 0)
            <span class="bottom-nav-dot cart-count">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
            @endif
        </a>
        <a href="{{ route('buyer.wishlist.index') }}" class="bottom-nav-item {{ request()->routeIs('buyer.wishlist.*') ? 'active' : '' }}">
            <i class="fas fa-heart"></i><span>Wishlist</span>
        </a>
        <a href="{{ route('buyer.orders.index') }}" class="bottom-nav-item {{ request()->routeIs('buyer.orders.*') ? 'active' : '' }}">
            <i class="fas fa-bag-shopping"></i><span>Orders</span>
        </a>
        <a href="{{ route('buyer.profile') }}" class="bottom-nav-item {{ request()->routeIs('buyer.profile') ? 'active' : '' }}">
            <i class="fas fa-user"></i><span>Profile</span>
        </a>
    </div>
</nav>

<script>
// ── Show flash area if it has content ──────────────────────
(function() {
    const fa = document.getElementById('flash-area');
    if (fa && fa.querySelector('.alert')) fa.style.display = 'block';
})();

// ── Sidebar ───────────────────────────────────────────────
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebar-overlay');

function openSidebar() {
    sidebar.classList.add('open');
    overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
    document.body.style.overflow = '';
}

// Close on nav-link click (mobile)
document.querySelectorAll('.sidebar .nav-link').forEach(a => {
    a.addEventListener('click', () => { if (window.innerWidth <= 1024) closeSidebar(); });
});

window.addEventListener('resize', () => {
    if (window.innerWidth > 1024) closeSidebar();
});

// ── Chat badge ────────────────────────────────────────────
async function updateChatBadge() {
    try {
        const r = await fetch('/chat/api/unread-count', { headers: { 'Accept': 'application/json' } });
        const d = await r.json();
        if (!d.success) return;
        const count = d.unread_count;
        ['chatBadge', 'chatBadgeTop'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            if (count > 0) { el.textContent = count > 9 ? '9+' : count; el.classList.remove('hidden'); }
            else el.classList.add('hidden');
        });
    } catch {}
}
document.addEventListener('DOMContentLoaded', () => { updateChatBadge(); setInterval(updateChatBadge, 30000); });
</script>

@yield('scripts')
@stack('scripts')
</body>
</html>
