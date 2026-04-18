@extends('layouts.vendor')

@section('title', 'Dashboard - BebaMart Vendor')
@section('page_title', 'Dashboard')
@section('page_description', 'Manage your store, track sales and grow your business')

@push('styles')
<style>
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes float {
    0%,100% { transform: translateY(0) rotate(-2deg); }
    50%      { transform: translateY(-8px) rotate(2deg); }
}
.anim { opacity: 0; animation: fadeUp .45s ease forwards; }
.s1 { animation-delay: .05s; }
.s2 { animation-delay: .10s; }
.s3 { animation-delay: .15s; }
.s4 { animation-delay: .20s; }
.s5 { animation-delay: .25s; }

/* ── Hero ───────────────────────────────────────────── */
.hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 55%, #312e81 100%);
    border-radius: 20px;
    padding: 20px 22px 0;
    overflow: hidden;
    position: relative;
}
.hero::before {
    content: ''; position: absolute; top: -60px; right: -60px;
    width: 220px; height: 220px;
    background: radial-gradient(circle, rgba(99,102,241,.35) 0%, transparent 70%);
    pointer-events: none;
}
.hero-name { font-size: 20px; font-weight: 800; color: #fff; }
.hero-sub  { font-size: 12px; color: rgba(255,255,255,.6); margin-top: 2px; }
.hero-icon {
    width: 64px; height: 64px;
    background: rgba(255,255,255,.12);
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; color: #fff;
    animation: float 4s ease-in-out infinite;
    flex-shrink: 0;
}
.hero-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 16px; border-radius: 10px;
    font-size: 12px; font-weight: 700;
    text-decoration: none; transition: all .2s; white-space: nowrap;
}
.hero-btn-white { background: #fff; color: #4f46e5; }
.hero-btn-white:hover { background: #e0e7ff; }
.hero-btn-glass {
    background: rgba(255,255,255,.15);
    border: 1.5px solid rgba(255,255,255,.3);
    color: #fff;
}
.hero-btn-glass:hover { background: rgba(255,255,255,.25); }

.hero-strip {
    margin: 16px -22px 0;
    padding: 12px 22px;
    background: rgba(0,0,0,.25);
    display: grid;
    grid-template-columns: repeat(3, 1fr);
}
.hs-item { text-align: center; }
.hs-val { font-size: 17px; font-weight: 800; color: #fff; }
.hs-lbl { font-size: 10px; color: rgba(255,255,255,.5); font-weight: 500; margin-top: 2px; }

/* ── Stat cards ─────────────────────────────────────── */
.sc {
    background: #fff; border-radius: 16px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    padding: 14px 16px;
    display: flex; align-items: center; gap: 12px;
    transition: box-shadow .2s, transform .2s;
}
.sc:hover { box-shadow: 0 6px 24px rgba(0,0,0,.07); transform: translateY(-1px); }
.sc-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; flex-shrink: 0;
}
.sc-val { font-size: 22px; font-weight: 800; color: #0f172a; line-height: 1; }
.sc-lbl { font-size: 11px; color: #94a3b8; font-weight: 500; margin-top: 3px; }

/* ── Quick actions ──────────────────────────────────── */
.qa {
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 6px; padding: 14px 8px;
    border-radius: 14px; background: #fff;
    border: 1px solid #f1f5f9;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    text-decoration: none; transition: all .2s; text-align: center;
}
.qa:hover { box-shadow: 0 6px 20px rgba(0,0,0,.08); transform: translateY(-2px); }
.qa-icon {
    width: 40px; height: 40px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; font-size: 17px;
}
.qa span { font-size: 11px; font-weight: 600; color: #374151; }

/* ── Section ────────────────────────────────────────── */
.sec {
    background: #fff; border-radius: 16px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    overflow: hidden;
}
.sec-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid #f8fafc;
}
.sec-title { font-size: 13px; font-weight: 700; color: #0f172a; }
.sec-link  { font-size: 12px; font-weight: 600; color: #6366f1; text-decoration: none; }
.sec-link:hover { color: #4f46e5; }

/* ── Order rows ─────────────────────────────────────── */
.ord-row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 16px; border-bottom: 1px solid #f8fafc;
    transition: background .15s;
}
.ord-row:last-child { border-bottom: none; }
.ord-row:hover { background: #fafbff; }
.ord-icon {
    width: 32px; height: 32px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 12px;
}
.ord-num   { font-size: 13px; font-weight: 700; color: #1e293b; }
.ord-buyer { font-size: 11px; color: #94a3b8; }
.ord-amt   { font-size: 13px; font-weight: 800; color: #0f172a; }
.ord-badge {
    font-size: 10px; font-weight: 700;
    padding: 3px 8px; border-radius: 50px; display: inline-block; white-space: nowrap;
}

/* ── Listing rows ───────────────────────────────────── */
.lst-row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 16px; border-bottom: 1px solid #f8fafc;
    transition: background .15s;
}
.lst-row:last-child { border-bottom: none; }
.lst-row:hover { background: #fafbff; }
.lst-img {
    width: 36px; height: 36px; border-radius: 8px;
    object-fit: cover; flex-shrink: 0;
}
.lst-placeholder {
    width: 36px; height: 36px; border-radius: 8px;
    background: #f1f5f9;
    display: flex; align-items: center; justify-content: center;
    color: #cbd5e1; font-size: 13px; flex-shrink: 0;
}
.lst-title { font-size: 13px; font-weight: 600; color: #1e293b; }
.lst-price { font-size: 11px; color: #6366f1; font-weight: 700; }
</style>
@endpush

@section('content')
@php
    $vendorSubscription = auth()->user()->vendorProfile?->activeSubscription;
    $subPlan = $vendorSubscription?->plan;
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp

<div class="space-y-4">

{{-- ── Hero ────────────────────────────────────────────── --}}
<div class="hero anim s1">
    <div class="flex items-center justify-between mb-3">
        <div>
            <div class="hero-name">{{ $greeting }}, {{ Auth::user()->name }}!</div>
            <div class="hero-sub">
                {{ auth()->user()->vendorProfile->business_name ?? 'Your Store' }}
                @if($subPlan && !$subPlan->is_free_plan)
                &middot; <span style="color:#fbbf24;font-weight:700;"><i class="fas fa-crown text-xs"></i> {{ $subPlan->name }}</span>
                @endif
            </div>
            <div class="flex flex-wrap gap-2 mt-3">
                <a href="{{ route('vendor.listings.create') }}" class="hero-btn hero-btn-white">
                    <i class="fas fa-plus"></i> Add Product
                </a>
                <a href="{{ route('vendor.orders.index') }}" class="hero-btn hero-btn-glass">
                    <i class="fas fa-receipt"></i> View Orders
                </a>
            </div>
        </div>
        <div class="hero-icon ml-4"><i class="fas fa-store"></i></div>
    </div>

    <div class="hero-strip">
        <div class="hs-item">
            <div class="hs-val" data-count="{{ $stats['total_sales'] ?? 0 }}">0</div>
            <div class="hs-lbl">UGX Sales</div>
        </div>
        <div class="hs-item">
            <div class="hs-val" data-count="{{ $stats['active_listings'] ?? 0 }}">0</div>
            <div class="hs-lbl">Active Listings</div>
        </div>
        <div class="hs-item">
            <div class="hs-val" data-count="{{ $stats['pending_orders'] ?? 0 }}">0</div>
            <div class="hs-lbl">Pending Orders</div>
        </div>
    </div>
</div>

{{-- ── Stat Cards ──────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 anim s2">
    <div class="sc">
        <div class="sc-icon" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0);">
            <i class="fas fa-coins" style="color:#16a34a;"></i>
        </div>
        <div>
            <div class="sc-val" data-count="{{ $stats['total_sales'] ?? 0 }}">0</div>
            <div class="sc-lbl">Sales (UGX)</div>
        </div>
    </div>
    <div class="sc">
        <div class="sc-icon" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);">
            <i class="fas fa-boxes" style="color:#2563eb;"></i>
        </div>
        <div>
            <div class="sc-val" data-count="{{ $stats['active_listings'] ?? 0 }}">0</div>
            <div class="sc-lbl">Listings</div>
        </div>
    </div>
    <div class="sc">
        <div class="sc-icon" style="background:linear-gradient(135deg,#fef9c3,#fde68a);">
            <i class="fas fa-shopping-cart" style="color:#ca8a04;"></i>
        </div>
        <div>
            <div class="sc-val" data-count="{{ $stats['pending_orders'] ?? 0 }}">0</div>
            <div class="sc-lbl">Pending Orders</div>
        </div>
    </div>
    <div class="sc">
        <div class="sc-icon" style="background:linear-gradient(135deg,#f3e8ff,#e9d5ff);">
            <i class="fas fa-star" style="color:#9333ea;"></i>
        </div>
        <div>
            <div class="sc-val">4.8</div>
            <div class="sc-lbl">Store Rating</div>
        </div>
    </div>
</div>

{{-- ── Quick Actions ───────────────────────────────────── --}}
<div class="anim s3">
    <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px;">Quick Actions</p>
    <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
        <a href="{{ route('vendor.listings.create') }}" class="qa">
            <div class="qa-icon" style="background:linear-gradient(135deg,#e0e7ff,#c7d2fe);">
                <i class="fas fa-plus" style="color:#4f46e5;"></i>
            </div>
            <span>Add Product</span>
        </a>
        <a href="{{ route('vendor.orders.index') }}" class="qa">
            <div class="qa-icon" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0);">
                <i class="fas fa-receipt" style="color:#16a34a;"></i>
            </div>
            <span>Orders</span>
        </a>
        <a href="{{ route('vendor.jobs.create') }}" class="qa">
            <div class="qa-icon" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);">
                <i class="fas fa-briefcase" style="color:#2563eb;"></i>
            </div>
            <span>Post Job</span>
        </a>
        <a href="{{ route('vendor.promotions.index') }}" class="qa">
            <div class="qa-icon" style="background:linear-gradient(135deg,#fce7f3,#fbcfe8);">
                <i class="fas fa-bullhorn" style="color:#db2777;"></i>
            </div>
            <span>Promote</span>
        </a>
        <a href="{{ route('vendor.imports.index') }}" class="qa">
            <div class="qa-icon" style="background:linear-gradient(135deg,#fef9c3,#fde68a);">
                <i class="fas fa-plane" style="color:#ca8a04;"></i>
            </div>
            <span>Import</span>
        </a>
        <a href="{{ route('vendor.subscription.index') }}" class="qa">
            <div class="qa-icon" style="background:linear-gradient(135deg,#f3e8ff,#e9d5ff);">
                <i class="fas fa-crown" style="color:#9333ea;"></i>
            </div>
            <span>Upgrade</span>
        </a>
    </div>
</div>

{{-- ── Recent Orders + Listings ────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 anim s4">

    {{-- Recent Orders --}}
    <div class="sec">
        <div class="sec-head">
            <span class="sec-title"><i class="fas fa-receipt text-indigo-400 mr-2"></i>Recent Orders</span>
            <a href="{{ route('vendor.orders.index') }}" class="sec-link">View All <i class="fas fa-arrow-right text-xs ml-1"></i></a>
        </div>
        @if($recentOrders && $recentOrders->count() > 0)
            @foreach($recentOrders as $order)
            @php
                $oMap = [
                    'pending'    => ['background:#fef9c3;color:#854d0e;', 'fa-clock'],
                    'paid'       => ['background:#dbeafe;color:#1e40af;', 'fa-check'],
                    'delivered'  => ['background:#dcfce7;color:#15803d;', 'fa-check-double'],
                    'cancelled'  => ['background:#fee2e2;color:#991b1b;', 'fa-times'],
                    'processing' => ['background:#e0e7ff;color:#3730a3;', 'fa-cog'],
                    'shipped'    => ['background:#f3e8ff;color:#6b21a8;', 'fa-truck'],
                ];
                $oc = $oMap[$order->status] ?? $oMap['pending'];
            @endphp
            <div class="ord-row">
                <div class="ord-icon" style="{{ $oc[0] }}">
                    <i class="fas {{ $oc[1] }}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="ord-num">#{{ $order->order_number }}</div>
                    <div class="ord-buyer">{{ $order->buyer->name ?? 'Customer' }}</div>
                </div>
                <div class="text-right flex-shrink-0">
                    <div class="ord-amt">{{ number_format($order->total, 0) }}</div>
                    <span class="ord-badge" style="{{ $oc[0] }}">{{ ucfirst($order->status) }}</span>
                </div>
            </div>
            @endforeach
        @else
            <div style="padding:32px 16px;text-align:center;">
                <div style="width:44px;height:44px;background:linear-gradient(135deg,#e0e7ff,#c7d2fe);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                    <i class="fas fa-receipt" style="color:#6366f1;font-size:17px;"></i>
                </div>
                <p style="font-size:13px;font-weight:600;color:#475569;">No orders yet</p>
                <p style="font-size:11px;color:#94a3b8;margin-top:3px;">Add products to start selling</p>
            </div>
        @endif
    </div>

    {{-- Recent Listings --}}
    <div class="sec">
        <div class="sec-head">
            <span class="sec-title"><i class="fas fa-boxes text-blue-400 mr-2"></i>Your Listings</span>
            <a href="{{ route('vendor.listings.index') }}" class="sec-link">View All <i class="fas fa-arrow-right text-xs ml-1"></i></a>
        </div>
        @if($recentListings && $recentListings->count() > 0)
            @foreach($recentListings as $listing)
            <div class="lst-row">
                @if($listing->images->first())
                <img src="{{ asset('storage/' . $listing->images->first()->path) }}"
                     alt="{{ $listing->title }}" class="lst-img">
                @else
                <div class="lst-placeholder"><i class="fas fa-image"></i></div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="lst-title truncate">{{ $listing->title }}</div>
                    <div class="lst-price">UGX {{ number_format($listing->price, 0) }}</div>
                </div>
                <div class="text-right flex-shrink-0">
                    <div style="font-size:11px;color:#94a3b8;">Stock: {{ $listing->stock }}</div>
                    <span class="ord-badge" style="{{ $listing->is_active ? 'background:#dcfce7;color:#15803d;' : 'background:#f1f5f9;color:#64748b;' }}">
                        {{ $listing->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            @endforeach
        @else
            <div style="padding:32px 16px;text-align:center;">
                <div style="width:44px;height:44px;background:linear-gradient(135deg,#dbeafe,#bfdbfe);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                    <i class="fas fa-box-open" style="color:#2563eb;font-size:17px;"></i>
                </div>
                <p style="font-size:13px;font-weight:600;color:#475569;">No listings yet</p>
                <p style="font-size:11px;color:#94a3b8;margin-top:3px;">Create your first product</p>
                <a href="{{ route('vendor.listings.create') }}"
                   style="display:inline-flex;align-items:center;gap:5px;margin-top:10px;padding:7px 16px;border-radius:9px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:12px;font-weight:700;text-decoration:none;">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </div>
        @endif
    </div>

</div>
</div>

@push('scripts')
<script>
function animateCounter(el) {
    const raw = parseFloat((el.dataset.count || '0').toString().replace(/,/g, '')) || 0;
    if (raw === 0) return;
    const isLarge = raw > 999;
    const steps   = 45;
    const inc     = raw / steps;
    let cur = 0, step = 0;
    const t = setInterval(() => {
        cur  = Math.min(cur + inc, raw);
        step++;
        el.textContent = isLarge
            ? Math.round(cur).toLocaleString()
            : (Number.isInteger(raw) ? Math.round(cur) : cur.toFixed(1));
        if (step >= steps) {
            el.textContent = isLarge ? raw.toLocaleString() : raw;
            clearInterval(t);
        }
    }, 900 / steps);
}

document.addEventListener('DOMContentLoaded', () => {
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.querySelectorAll('[data-count]').forEach(animateCounter);
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.2 });
    document.querySelectorAll('.sc, .hero').forEach(el => obs.observe(el));
});
</script>
@endpush
@endsection
