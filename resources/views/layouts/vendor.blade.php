<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vendor Panel - BebaMart')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=2">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <style>
    :root {
        --sidebar-w  : 268px;
        --sidebar-bg : #0f172a;
        --accent     : #6366f1;
        --accent-rgb : 99,102,241;
        --accent-light: rgba(99,102,241,.12);
        --top-h      : 62px;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        background: #f1f5f9;
        height: 100%;
        overflow-x: hidden;
    }

    /* ── Sidebar ──────────────────────────────────────────── */
    .v-sidebar {
        position: fixed; top: 0; left: 0;
        width: var(--sidebar-w); height: 100vh;
        background: var(--sidebar-bg);
        display: flex; flex-direction: column;
        z-index: 50;
        transition: transform .3s cubic-bezier(.4,0,.2,1);
        overflow: hidden;
    }
    .v-sidebar::before {
        content: ''; position: absolute; top: -80px; left: -80px;
        width: 260px; height: 260px;
        background: radial-gradient(circle, rgba(99,102,241,.25) 0%, transparent 70%);
        pointer-events: none;
    }
    .v-sidebar::after {
        content: ''; position: absolute; bottom: -60px; right: -40px;
        width: 200px; height: 200px;
        background: radial-gradient(circle, rgba(139,92,246,.18) 0%, transparent 70%);
        pointer-events: none;
    }

    .sb-header {
        padding: 18px 18px 14px;
        border-bottom: 1px solid rgba(255,255,255,.06);
        display: flex; align-items: center; gap: 11px;
        position: relative; z-index: 1;
    }
    .sb-logo {
        width: 38px; height: 38px;
        background: linear-gradient(135deg, var(--accent), #8b5cf6);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(var(--accent-rgb),.4);
    }
    .sb-logo i { color: #fff; font-size: 16px; }
    .sb-brand  { font-size: 16px; font-weight: 800; color: #fff; }
    .sb-sub    { font-size: 11px; color: rgba(255,255,255,.45); font-weight: 500; }

    .sb-user {
        margin: 12px 12px 8px;
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 14px;
        padding: 12px;
        position: relative; z-index: 1;
    }
    .sb-avatar {
        width: 36px; height: 36px; border-radius: 10px;
        background: linear-gradient(135deg, var(--accent), #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; overflow: hidden;
    }
    .sb-avatar i { color: #fff; font-size: 14px; }
    .sb-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .user-name { font-size: 14px; font-weight: 800; color: #fff; }
    .user-role { font-size: 11px; color: rgba(255,255,255,.45); font-weight: 500; }
    .status-pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 9px; border-radius: 50px;
        font-size: 10px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .4px;
    }

    .sb-nav {
        flex: 1; overflow-y: auto; padding: 6px 10px;
        position: relative; z-index: 1;
    }
    .sb-nav::-webkit-scrollbar { width: 3px; }
    .sb-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 3px; }

    .nav-section {
        font-size: 10px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .8px;
        color: rgba(255,255,255,.3);
        padding: 14px 8px 6px;
    }
    .nav-link {
        display: flex; align-items: center; gap: 10px;
        padding: 7px 10px; border-radius: 10px;
        color: rgba(255,255,255,.62);
        text-decoration: none;
        font-size: 13px; font-weight: 500;
        transition: all .18s; position: relative; margin-bottom: 1px;
    }
    .nav-link:hover { background: rgba(255,255,255,.07); color: rgba(255,255,255,.92); }
    .nav-link.active { background: var(--accent-light); color: #fff; font-weight: 600; }
    .nav-link.active::before {
        content: ''; position: absolute; left: 0; top: 50%;
        transform: translateY(-50%);
        width: 3px; height: 60%;
        background: var(--accent); border-radius: 0 3px 3px 0;
    }
    .nav-icon {
        width: 34px; height: 34px; border-radius: 9px;
        background: rgba(255,255,255,.07);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; transition: background .18s;
    }
    .nav-icon i { font-size: 13px; color: rgba(255,255,255,.6); transition: color .18s; }
    .nav-link.active .nav-icon { background: var(--accent); }
    .nav-link.active .nav-icon i { color: #fff; }
    .nav-link:hover .nav-icon { background: rgba(255,255,255,.12); }
    .nav-link:hover .nav-icon i { color: rgba(255,255,255,.9); }

    .nav-badge {
        margin-left: auto; min-width: 20px; height: 20px;
        padding: 0 6px; border-radius: 10px;
        font-size: 10px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; color: #fff;
    }

    .sb-footer {
        padding: 10px 12px 12px;
        border-top: 1px solid rgba(255,255,255,.06);
        position: relative; z-index: 1;
    }
    .sb-logout {
        width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 9px 0; border-radius: 10px;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.07);
        color: rgba(255,255,255,.55);
        font-size: 13px; font-weight: 500;
        cursor: pointer; transition: all .2s;
    }
    .sb-logout:hover { background: rgba(239,68,68,.15); border-color: rgba(239,68,68,.3); color: #fca5a5; }

    /* ── Topbar ───────────────────────────────────────────── */
    .v-topbar {
        position: fixed; top: 0; right: 0; left: var(--sidebar-w);
        height: var(--top-h);
        background: rgba(248,250,252,.88);
        backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
        border-bottom: 1px solid rgba(15,23,42,.06);
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 26px; z-index: 30; transition: left .3s;
    }
    .topbar-title { font-size: 16px; font-weight: 700; color: #0f172a; }
    .topbar-sub   { font-size: 12px; color: #94a3b8; font-weight: 400; }
    .topbar-actions { display: flex; align-items: center; gap: 8px; }
    .topbar-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 7px 13px; border-radius: 9px;
        font-size: 12px; font-weight: 600;
        text-decoration: none; transition: all .2s;
        border: none; cursor: pointer; white-space: nowrap;
    }
    .topbar-btn-primary { background: var(--accent); color: #fff; }
    .topbar-btn-primary:hover { background: #4f46e5; box-shadow: 0 3px 10px rgba(var(--accent-rgb),.3); }
    .topbar-btn-green { background: rgba(16,185,129,.1); color: #059669; border: 1px solid rgba(16,185,129,.2); }
    .topbar-btn-green:hover { background: rgba(16,185,129,.18); }
    .topbar-btn-ghost { background: rgba(99,102,241,.08); color: var(--accent); border: 1px solid rgba(99,102,241,.15); }
    .topbar-btn-ghost:hover { background: rgba(99,102,241,.14); }

    /* ── Content ──────────────────────────────────────────── */
    .v-content {
        margin-left: var(--sidebar-w);
        padding: 20px 24px;
        padding-top: calc(var(--top-h) + 20px);
        min-height: 100vh;
        background: #f1f5f9;
        transition: margin-left .3s;
    }

    /* ── Overlay ──────────────────────────────────────────── */
    .sb-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,.55); backdrop-filter: blur(4px); z-index: 45;
    }
    .sb-overlay.open { display: block; }

    /* ── Mobile header ────────────────────────────────────── */
    .mob-header {
        display: none; position: fixed; top: 0; left: 0; right: 0;
        height: 58px; background: var(--sidebar-bg);
        align-items: center; justify-content: space-between;
        padding: 0 16px; z-index: 40;
        box-shadow: 0 2px 16px rgba(0,0,0,.25);
    }
    .mob-brand { font-size: 15px; font-weight: 800; color: #fff; }
    .mob-icon-btn {
        width: 36px; height: 36px; border-radius: 10px;
        background: rgba(255,255,255,.1); border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 16px; text-decoration: none; transition: background .2s;
    }
    .mob-icon-btn:hover { background: rgba(255,255,255,.18); }

    /* ── Mobile bottom nav ────────────────────────────────── */
    .mob-bottom {
        display: none; position: fixed; bottom: 0; left: 0; right: 0;
        background: #fff; border-top: 1px solid #e2e8f0;
        box-shadow: 0 -4px 24px rgba(0,0,0,.08);
        z-index: 35;
        padding-bottom: max(8px, env(safe-area-inset-bottom));
    }
    .mob-bottom-inner { display: flex; }
    .mob-nav-item {
        flex: 1; display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        padding: 8px 4px 6px; color: #94a3b8;
        font-size: 10px; font-weight: 600;
        text-decoration: none; transition: color .2s; position: relative;
    }
    .mob-nav-item i { font-size: 18px; margin-bottom: 3px; display: block; }
    .mob-nav-item.active { color: var(--accent); }
    .mob-nav-badge {
        position: absolute; top: 4px; left: 50%; transform: translateX(6px);
        min-width: 15px; height: 15px; padding: 0 3px; border-radius: 8px;
        font-size: 9px; font-weight: 700; color: #fff;
        display: flex; align-items: center; justify-content: center;
    }

    /* ── Alerts ───────────────────────────────────────────── */
    .alert {
        padding: 13px 16px; border-radius: 12px; margin-bottom: 16px;
        display: flex; align-items: flex-start; gap: 10px;
        font-size: 13px; font-weight: 500;
    }
    .alert-success { background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; }
    .alert-error   { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; }
    .alert-info    { background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; }

    /* ── Cards & Buttons ──────────────────────────────────── */
    .card {
        background: #fff; border-radius: 16px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 4px rgba(0,0,0,.04); transition: box-shadow .2s;
    }
    .card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.07); }
    .btn-primary {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #fff; padding: 9px 18px; border-radius: 10px;
        font-weight: 600; font-size: 13px;
        display: inline-flex; align-items: center; gap: 7px;
        transition: all .2s; border: none; cursor: pointer; text-decoration: none;
    }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79,70,229,.4); }
    .btn-secondary {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff; padding: 9px 18px; border-radius: 10px;
        font-weight: 600; font-size: 13px;
        display: inline-flex; align-items: center; gap: 7px;
        transition: all .2s; border: none; cursor: pointer; text-decoration: none;
    }
    .btn-secondary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,.4); }
    .btn-tertiary {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: #fff; padding: 9px 18px; border-radius: 10px;
        font-weight: 600; font-size: 13px;
        display: inline-flex; align-items: center; gap: 7px;
        transition: all .2s; border: none; cursor: pointer; text-decoration: none;
    }
    .btn-tertiary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(139,92,246,.4); }

    /* ── Responsive ───────────────────────────────────────── */
    @media (max-width: 1024px) {
        .v-sidebar  { transform: translateX(-100%); }
        .v-sidebar.open { transform: translateX(0); }
        .v-topbar   { display: none; }
        .v-content  { margin-left: 0; padding: 16px; padding-top: 74px; padding-bottom: 88px; }
        .mob-header { display: flex; }
        .mob-bottom { display: block; }
    }
    @media (max-width: 640px) {
        .v-content { padding: 12px; padding-top: 70px; padding-bottom: 84px; }
    }
    </style>
    @stack('styles')
</head>
<body>

{{-- Mobile header --}}
<div class="mob-header">
    <button class="mob-icon-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <span class="mob-brand">Vendor Panel</span>
    <a href="{{ route('vendor.orders.index') }}" class="mob-icon-btn" style="position:relative;">
        <i class="fas fa-receipt"></i>
        @php
            $pendingOrders = \App\Models\Order::where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0)
                ->whereIn('status', ['pending', 'paid'])->count();
        @endphp
        @if($pendingOrders > 0)
        <span style="position:absolute;top:2px;right:2px;background:#f97316;color:#fff;font-size:9px;font-weight:700;min-width:14px;height:14px;border-radius:7px;display:flex;align-items:center;justify-content:center;padding:0 3px;">
            {{ $pendingOrders > 9 ? '9+' : $pendingOrders }}
        </span>
        @endif
    </a>
</div>

{{-- Sidebar overlay --}}
<div class="sb-overlay" id="sbOverlay" onclick="toggleSidebar()"></div>

{{-- Sidebar --}}
<aside class="v-sidebar" id="sidebar">

    {{-- Header --}}
    <div class="sb-header">
        <div class="sb-logo"><i class="fas fa-store"></i></div>
        <div class="flex-1 min-w-0">
            <div class="sb-brand">Vendor Panel</div>
            <div class="sb-sub truncate">{{ auth()->user()->vendorProfile->business_name ?? 'My Store' }}</div>
        </div>
        <button onclick="toggleSidebar()" class="lg:hidden" style="background:none;border:none;color:rgba(255,255,255,.45);cursor:pointer;font-size:15px;flex-shrink:0;">
            <i class="fas fa-times"></i>
        </button>
    </div>

    {{-- User card --}}
    @php $vendor = auth()->user()->vendorProfile; @endphp
    <div class="sb-user">
        <div class="flex items-center gap-3 mb-2">
            <div class="sb-avatar">
                @if(auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="">
                @else
                    <i class="fas fa-user-tie"></i>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="user-name truncate">{{ Auth::user()->name }}</div>
                <div class="user-role">Vendor Account</div>
            </div>
        </div>
        @if($vendor)
        @php
            $vsMap = [
                'pending'  => ['background:#fef9c3;color:#854d0e;', 'fa-clock'],
                'approved' => ['background:#dcfce7;color:#15803d;', 'fa-check-circle'],
                'rejected' => ['background:#fee2e2;color:#991b1b;', 'fa-times-circle'],
            ];
            $vs = $vsMap[$vendor->vetting_status ?? 'pending'] ?? $vsMap['pending'];
        @endphp
        <div class="flex items-center justify-between mt-1">
            <span style="font-size:10px;color:rgba(255,255,255,.4);">Status</span>
            <span class="status-pill" style="{{ $vs[0] }}">
                <i class="fas {{ $vs[1] }}"></i> {{ ucfirst($vendor->vetting_status ?? 'pending') }}
            </span>
        </div>
        @endif
    </div>

    {{-- Navigation --}}
    <nav class="sb-nav">

        <a href="{{ route('vendor.dashboard') }}"
           class="nav-link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>Dashboard
        </a>

        <div class="nav-section">Products</div>

        <a href="{{ route('vendor.listings.index') }}"
           class="nav-link {{ request()->is('vendor/listings*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-boxes"></i></span>My Listings
        </a>

        <a href="{{ route('vendor.orders.index') }}"
           class="nav-link {{ request()->is('vendor/orders*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-shopping-cart"></i></span>Orders
            @if($pendingOrders > 0)
            <span class="nav-badge bg-orange-500">{{ $pendingOrders }}</span>
            @endif
        </a>

        <a href="{{ route('buyer.orders.index') }}"
           class="nav-link {{ request()->routeIs('buyer.orders*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-shopping-bag"></i></span>My Purchases
        </a>

        <div class="nav-section">Jobs &amp; Services</div>

        <a href="{{ route('vendor.jobs.index') }}"
           class="nav-link {{ request()->is('vendor/jobs*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-briefcase"></i></span>Job Listings
            @php
                $pendingApps = \App\Models\JobApplication::whereHas('job', fn($q) => $q->where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0))
                    ->where('status', 'pending')->count();
            @endphp
            @if($pendingApps > 0)
            <span class="nav-badge bg-blue-500">{{ $pendingApps }}</span>
            @endif
        </a>

        <a href="{{ route('vendor.services.index') }}"
           class="nav-link {{ request()->is('vendor/services') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-tools"></i></span>My Services
        </a>

        <a href="{{ route('vendor.services.requests') }}"
           class="nav-link {{ request()->is('vendor/services/requests*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-clipboard-list"></i></span>Service Requests
            @php
                $pendingRequests = \App\Models\ServiceRequest::where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0)
                    ->where('status', 'pending')->count();
            @endphp
            @if($pendingRequests > 0)
            <span class="nav-badge bg-green-500">{{ $pendingRequests }}</span>
            @endif
        </a>

        <div class="nav-section">Communication</div>

        <a href="{{ route('vendor.callbacks.index') }}"
           class="nav-link {{ request()->is('vendor/callbacks*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-phone-alt"></i></span>Callbacks
            @php
                $pendingCallbacks = \App\Models\CallbackRequest::where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0)
                    ->where('status', 'pending')->count();
            @endphp
            @if($pendingCallbacks > 0)
            <span class="nav-badge bg-orange-500">{{ $pendingCallbacks }}</span>
            @endif
        </a>

        <a href="{{ route('chat.index') }}"
           class="nav-link {{ request()->is('chat*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-comments"></i></span>Messages
            <span id="chatBadge" class="nav-badge bg-red-500 hidden">0</span>
        </a>

        <a href="{{ route('vendor.services.inquiries') }}"
           class="nav-link {{ request()->is('vendor/services/inquiries*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-envelope"></i></span>Inquiries
            @php
                $newInquiries = \App\Models\ServiceInquiry::where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0)
                    ->where('status', 'new')->count();
            @endphp
            @if($newInquiries > 0)
            <span class="nav-badge bg-purple-500">{{ $newInquiries }}</span>
            @endif
        </a>

        <div class="nav-section">More</div>

        <a href="{{ route('vendor.subscription.index') }}"
           class="nav-link {{ request()->is('vendor/subscription*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-crown"></i></span>Subscription
            @php
                $vendorSub = auth()->user()->vendorProfile?->activeSubscription;
                $subPlan   = $vendorSub?->plan;
            @endphp
            @if($subPlan && !$subPlan->is_free_plan)
            <span class="nav-badge {{ $subPlan->slug == 'gold' ? 'bg-yellow-500' : ($subPlan->slug == 'silver' ? 'bg-gray-400' : 'bg-orange-500') }}">
                {{ strtoupper(substr($subPlan->name, 0, 1)) }}
            </span>
            @endif
        </a>

        <a href="{{ route('vendor.services.reviews') }}"
           class="nav-link {{ request()->is('vendor/services/reviews*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-star"></i></span>Reviews
        </a>

        <a href="{{ route('vendor.imports.index') }}"
           class="nav-link {{ request()->is('vendor/imports*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-plane"></i></span>Import Goods
        </a>

        <a href="{{ route('vendor.promotions.index') }}"
           class="nav-link {{ request()->is('vendor/promotions*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-bullhorn"></i></span>Promotions
        </a>

        <a href="{{ route('vendor.analytics') }}"
           class="nav-link {{ request()->is('vendor/analytics*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-chart-line"></i></span>Analytics
        </a>

        <a href="{{ route('vendor.profile.show') }}"
           class="nav-link {{ request()->is('vendor/profile*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-user-circle"></i></span>Profile
        </a>

    </nav>

    {{-- Footer --}}
    <div class="sb-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="sb-logout">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </button>
        </form>
    </div>
</aside>

{{-- Desktop Topbar --}}
<header class="v-topbar">
    <div>
        <div class="topbar-title">@yield('page_title', 'Dashboard')</div>
        <div class="topbar-sub">@yield('page_description', 'Welcome back, ' . Auth::user()->name)</div>
    </div>
    <div class="topbar-actions">
        <a href="{{ route('vendor.listings.create') }}" class="topbar-btn topbar-btn-primary">
            <i class="fas fa-plus"></i> Add Listing
        </a>
        <a href="{{ route('vendor.jobs.create') }}" class="topbar-btn topbar-btn-green">
            <i class="fas fa-briefcase"></i> Post Job
        </a>
        <a href="{{ route('vendor.services.create') }}" class="topbar-btn topbar-btn-ghost">
            <i class="fas fa-tools"></i> Add Service
        </a>
    </div>
</header>

{{-- Main Content --}}
<main class="v-content">

    {{-- Flash alerts --}}
    <div id="alertArea">
        @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle" style="color:#10b981;flex-shrink:0;"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle" style="color:#ef4444;flex-shrink:0;"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif
        @if(session('info'))
        <div class="alert alert-info">
            <i class="fas fa-info-circle" style="color:#3b82f6;flex-shrink:0;"></i>
            <span>{{ session('info') }}</span>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle" style="color:#ef4444;flex-shrink:0;"></i>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    @yield('content')
</main>

{{-- Mobile Bottom Nav --}}
<nav class="mob-bottom">
    <div class="mob-bottom-inner">
        <a href="{{ route('vendor.dashboard') }}"
           class="mob-nav-item {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i><span>Home</span>
        </a>
        <a href="{{ route('vendor.listings.index') }}"
           class="mob-nav-item {{ request()->is('vendor/listings*') ? 'active' : '' }}">
            <i class="fas fa-boxes"></i><span>Products</span>
        </a>
        <a href="{{ route('vendor.orders.index') }}"
           class="mob-nav-item {{ request()->is('vendor/orders*') ? 'active' : '' }}" style="position:relative;">
            <i class="fas fa-receipt"></i><span>Orders</span>
            @if($pendingOrders > 0)
            <span class="mob-nav-badge bg-orange-500">{{ $pendingOrders > 9 ? '9+' : $pendingOrders }}</span>
            @endif
        </a>
        <a href="{{ route('chat.index') }}"
           class="mob-nav-item {{ request()->is('chat*') ? 'active' : '' }}" style="position:relative;">
            <i class="fas fa-comments"></i><span>Chat</span>
            <span id="chatBadgeMobile" class="mob-nav-badge bg-red-500 hidden">0</span>
        </a>
        <a href="{{ route('vendor.profile.show') }}"
           class="mob-nav-item {{ request()->is('vendor/profile*') ? 'active' : '' }}">
            <i class="fas fa-user"></i><span>Profile</span>
        </a>
    </div>
</nav>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
function toggleSidebar() {
    const sb = document.getElementById('sidebar');
    const ov = document.getElementById('sbOverlay');
    sb.classList.toggle('open');
    ov.classList.toggle('open');
    document.body.style.overflow = sb.classList.contains('open') ? 'hidden' : '';
}

document.querySelectorAll('.v-sidebar .nav-link').forEach(l => {
    l.addEventListener('click', () => { if (window.innerWidth <= 1024) toggleSidebar(); });
});

window.addEventListener('resize', () => {
    if (window.innerWidth > 1024) {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sbOverlay').classList.remove('open');
        document.body.style.overflow = '';
    }
});

document.addEventListener('DOMContentLoaded', function () {
    updateChatBadge();
    setInterval(updateChatBadge, 30000);

    const area = document.getElementById('alertArea');
    if (area && area.querySelector('.alert')) {
        setTimeout(() => { area.style.transition = 'opacity .6s'; area.style.opacity = '0'; }, 5000);
        setTimeout(() => { area.style.display = 'none'; }, 5700);
    }
});

async function updateChatBadge() {
    try {
        const res  = await fetch('/chat/api/unread-count', { headers: { Accept: 'application/json' } });
        const data = await res.json();
        if (data.success) {
            const b  = document.getElementById('chatBadge');
            const bm = document.getElementById('chatBadgeMobile');
            const n  = data.unread_count > 9 ? '9+' : data.unread_count;
            if (data.unread_count > 0) {
                if (b)  { b.textContent  = n; b.classList.remove('hidden'); }
                if (bm) { bm.textContent = n; bm.classList.remove('hidden'); }
            } else {
                if (b)  b.classList.add('hidden');
                if (bm) bm.classList.add('hidden');
            }
        }
    } catch {}
}
</script>
@stack('scripts')
</body>
</html>
