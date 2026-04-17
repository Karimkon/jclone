@extends('layouts.buyer')

@section('title', 'My Orders - ' . config('app.name'))
@section('page_title', 'My Orders')
@section('page_description', 'Track and manage all your purchases')

@push('styles')
<style>
/* ── Status helpers ─────────────────────────── */
.status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}
.status-pending          { background:#fef9c3; color:#854d0e; }
.status-confirmed        { background:#dbeafe; color:#1e40af; }
.status-payment_pending  { background:#ffedd5; color:#9a3412; }
.status-processing       { background:#e0e7ff; color:#3730a3; }
.status-shipped          { background:#f3e8ff; color:#6b21a8; }
.status-delivered        { background:#dcfce7; color:#15803d; }
.status-completed        { background:#d1fae5; color:#065f46; }
.status-cancelled        { background:#fee2e2; color:#991b1b; }
.status-disputed         { background:#fce7f3; color:#9d174d; }
.status-refunded         { background:#e0f2fe; color:#0369a1; }

/* ── Stat cards ─────────────────────────────── */
.stat-card {
    background: #fff;
    border-radius: 18px;
    padding: 20px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    display: flex; align-items: center; gap: 16px;
}
.stat-icon {
    width: 50px; height: 50px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.stat-val  { font-size: 22px; font-weight: 800; color: #0f172a; line-height: 1; }
.stat-lbl  { font-size: 12px; color: #94a3b8; font-weight: 500; margin-top: 3px; }

/* ── Filter tabs ────────────────────────────── */
.filter-tabs { display: flex; gap: 6px; flex-wrap: wrap; }
.filter-tab {
    padding: 7px 16px;
    border-radius: 50px;
    font-size: 13px; font-weight: 600;
    border: 1.5px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    cursor: pointer;
    text-decoration: none;
    transition: all .2s;
}
.filter-tab:hover       { border-color: #6366f1; color: #6366f1; }
.filter-tab.active      { background: #6366f1; color: #fff; border-color: #6366f1; box-shadow: 0 3px 10px rgba(99,102,241,.3); }

/* ── Order card ─────────────────────────────── */
.order-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    overflow: hidden;
    transition: box-shadow .25s, transform .2s;
}
.order-card:hover { box-shadow: 0 6px 28px rgba(0,0,0,.08); transform: translateY(-1px); }

.order-card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #f8fafc;
    gap: 12px;
    flex-wrap: wrap;
}
.order-number {
    font-size: 13px; font-weight: 700;
    color: #6366f1;
    font-family: 'Courier New', monospace;
    letter-spacing: .3px;
}
.order-date {
    font-size: 12px; color: #94a3b8; font-weight: 500;
    margin-top: 2px;
}

.order-card-body {
    padding: 16px 20px;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
    align-items: center;
}
@media(max-width: 600px) {
    .order-card-body { grid-template-columns: 1fr 1fr; }
}

.order-meta-label { font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; }
.order-meta-val   { font-size: 14px; font-weight: 600; color: #1e293b; }

.vendor-chip {
    display: inline-flex; align-items: center; gap: 7px;
    font-size: 13px; font-weight: 600; color: #374151;
}
.vendor-chip-icon {
    width: 28px; height: 28px;
    border-radius: 8px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.vendor-chip-icon i { color: #fff; font-size: 11px; }

.order-total  { font-size: 18px; font-weight: 800; color: #0f172a; }

.order-card-footer {
    display: flex; align-items: center; justify-content: flex-end;
    gap: 8px;
    padding: 12px 20px;
    background: #fafbff;
    border-top: 1px solid #f1f5f9;
}

/* ── Buttons ────────────────────────────────── */
.btn-view {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px;
    border-radius: 10px;
    background: #6366f1;
    color: #fff;
    font-size: 13px; font-weight: 600;
    text-decoration: none;
    transition: background .2s, box-shadow .2s;
    border: none; cursor: pointer;
}
.btn-view:hover { background: #4f46e5; box-shadow: 0 3px 12px rgba(99,102,241,.3); }

.btn-pay {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px;
    border-radius: 10px;
    background: linear-gradient(135deg, #f97316, #ea580c);
    color: #fff;
    font-size: 13px; font-weight: 600;
    text-decoration: none;
    transition: opacity .2s, box-shadow .2s;
    border: none; cursor: pointer;
    animation: pulse-pay 2s infinite;
}
.btn-pay:hover { opacity: .9; box-shadow: 0 3px 12px rgba(234,88,12,.35); }
@keyframes pulse-pay {
    0%,100% { box-shadow: 0 0 0 0 rgba(234,88,12,.4); }
    50%      { box-shadow: 0 0 0 6px rgba(234,88,12,.0); }
}

/* ── Empty state ────────────────────────────── */
.empty-state {
    background: #fff;
    border-radius: 24px;
    border: 1px solid #f1f5f9;
    padding: 60px 40px;
    text-align: center;
}
.empty-icon {
    width: 90px; height: 90px;
    background: linear-gradient(135deg, #e0e7ff, #f3e8ff);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px;
}
.empty-icon i { font-size: 36px; color: #6366f1; }
</style>
@endpush

@section('content')
@php
    $statusColors = [
        'pending'         => 'status-pending',
        'confirmed'       => 'status-confirmed',
        'payment_pending' => 'status-payment_pending',
        'processing'      => 'status-processing',
        'shipped'         => 'status-shipped',
        'delivered'       => 'status-delivered',
        'completed'       => 'status-completed',
        'cancelled'       => 'status-cancelled',
        'disputed'        => 'status-disputed',
        'refunded'        => 'status-refunded',
    ];
    $statusIcons = [
        'pending'         => 'fa-clock',
        'confirmed'       => 'fa-check',
        'payment_pending' => 'fa-credit-card',
        'processing'      => 'fa-cog',
        'shipped'         => 'fa-truck',
        'delivered'       => 'fa-check-double',
        'completed'       => 'fa-circle-check',
        'cancelled'       => 'fa-times-circle',
        'disputed'        => 'fa-exclamation-circle',
        'refunded'        => 'fa-rotate-left',
    ];
    $totalSpent = $orders->sum('total');
    $delivered  = $orders->where('status', 'delivered')->count() + $orders->where('status', 'completed')->count();
    $pending    = $orders->whereIn('status', ['pending','confirmed','processing','shipped'])->count();
@endphp

{{-- ── Stats row ──────────────────────────────────────────── --}}
@if(!$orders->isEmpty())
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#e0e7ff,#c7d2fe);">
            <i class="fas fa-shopping-bag" style="color:#6366f1;"></i>
        </div>
        <div>
            <div class="stat-val">{{ $orders->total() ?? $orders->count() }}</div>
            <div class="stat-lbl">Total Orders</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0);">
            <i class="fas fa-check-double" style="color:#16a34a;"></i>
        </div>
        <div>
            <div class="stat-val">{{ $delivered }}</div>
            <div class="stat-lbl">Delivered</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#fef9c3,#fde68a);">
            <i class="fas fa-clock" style="color:#ca8a04;"></i>
        </div>
        <div>
            <div class="stat-val">{{ $pending }}</div>
            <div class="stat-lbl">In Progress</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#f3e8ff,#e9d5ff);">
            <i class="fas fa-wallet" style="color:#9333ea;"></i>
        </div>
        <div>
            <div class="stat-val" style="font-size:15px;">{{ number_format($totalSpent, 0) }}</div>
            <div class="stat-lbl">UGX Spent</div>
        </div>
    </div>
</div>

{{-- ── Status filter tabs ─────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-5 flex items-center justify-between flex-wrap gap-3">
    <div class="filter-tabs">
        <a href="{{ route('buyer.orders.index') }}"
           class="filter-tab {{ !request('status') ? 'active' : '' }}">All</a>
        @foreach(['pending','payment_pending','processing','shipped','delivered','cancelled'] as $st)
        <a href="{{ route('buyer.orders.index', ['status' => $st]) }}"
           class="filter-tab {{ request('status') == $st ? 'active' : '' }}">
            {{ ucfirst(str_replace('_',' ',$st)) }}
        </a>
        @endforeach
    </div>
    <span class="text-sm text-slate-400 font-medium whitespace-nowrap">
        {{ $orders->total() ?? $orders->count() }} orders found
    </span>
</div>
@endif

{{-- ── Orders list ────────────────────────────────────────── --}}
@if($orders->isEmpty())
<div class="empty-state">
    <div class="empty-icon"><i class="fas fa-shopping-bag"></i></div>
    <h3 class="text-xl font-bold text-slate-800 mb-2">No orders yet</h3>
    <p class="text-slate-500 mb-8 max-w-xs mx-auto">Your order history will appear here once you make a purchase.</p>
    <a href="{{ route('marketplace.index') }}"
       class="btn-view px-8 py-3 text-base">
        <i class="fas fa-store"></i> Browse Marketplace
    </a>
</div>
@else
<div class="space-y-4">
    @foreach($orders as $order)
    @php
        $statusClass = $statusColors[$order->status] ?? 'status-pending';
        $statusIcon  = $statusIcons[$order->status]  ?? 'fa-circle';
        $statusLabel = ucfirst(str_replace('_', ' ', $order->status));
    @endphp

    <div class="order-card">

        {{-- Card header --}}
        <div class="order-card-header">
            <div>
                <div class="order-number">
                    <i class="fas fa-hashtag text-xs mr-1 opacity-60"></i>{{ $order->order_number }}
                </div>
                <div class="order-date">
                    <i class="fas fa-calendar-alt mr-1 opacity-60"></i>
                    {{ $order->created_at->format('M d, Y · h:i A') }}
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="status-badge {{ $statusClass }}">
                    <i class="fas {{ $statusIcon }} text-xs"></i>
                    {{ $statusLabel }}
                </span>
                @if($order->status === 'payment_pending')
                <span class="text-xs font-semibold text-orange-600 animate-pulse">
                    <i class="fas fa-exclamation-triangle mr-1"></i>Action needed
                </span>
                @endif
            </div>
        </div>

        {{-- Card body --}}
        <div class="order-card-body">
            <div>
                <div class="order-meta-label">Vendor</div>
                <div class="vendor-chip">
                    <div class="vendor-chip-icon"><i class="fas fa-store"></i></div>
                    {{ $order->vendorProfile->business_name ?? 'Vendor' }}
                </div>
            </div>
            <div>
                <div class="order-meta-label">Items</div>
                <div class="order-meta-val">
                    <i class="fas fa-box-open text-indigo-400 mr-1 text-sm"></i>
                    {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                </div>
            </div>
            <div>
                <div class="order-meta-label">Total</div>
                <div class="order-total">UGX {{ number_format($order->total, 0) }}</div>
            </div>
        </div>

        {{-- Card footer --}}
        <div class="order-card-footer">
            @if($order->status === 'payment_pending')
            <a href="{{ route('buyer.orders.payment', $order) }}" class="btn-pay">
                <i class="fas fa-credit-card"></i> Complete Payment
            </a>
            @endif
            <a href="{{ route('buyer.orders.show', $order) }}" class="btn-view">
                <i class="fas fa-eye"></i> View Details
            </a>
        </div>
    </div>
    @endforeach
</div>

{{-- Pagination --}}
@if($orders->hasPages())
<div class="mt-6 flex justify-center">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3">
        {{ $orders->appends(request()->query())->links() }}
    </div>
</div>
@endif
@endif
@endsection
