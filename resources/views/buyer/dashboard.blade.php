@extends('layouts.buyer')

@section('title', 'Dashboard - ' . config('app.name'))
@section('page_title', 'Dashboard')
@section('page_description', 'Welcome back, ' . Auth::user()->name)

@push('styles')
<style>
/* ── Animations ─────────────────────────────────── */
@keyframes fadeUp  { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
@keyframes float   { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }
.a1{animation:fadeUp .4s ease both}
.a2{animation:fadeUp .4s .07s ease both}
.a3{animation:fadeUp .4s .14s ease both}
.a4{animation:fadeUp .4s .21s ease both}
.a5{animation:fadeUp .4s .28s ease both}
.a6{animation:fadeUp .4s .35s ease both}

/* ── Hero ───────────────────────────────────────── */
.hero{
    background:linear-gradient(135deg,#4338ca 0%,#6366f1 45%,#8b5cf6 75%,#a855f7 100%);
    border-radius:18px;
    padding:20px 22px 0;
    position:relative;overflow:hidden;
    box-shadow:0 12px 40px rgba(99,102,241,.3);
}
.hero-orb{position:absolute;border-radius:50%;pointer-events:none;opacity:.12;}
.orb1{width:160px;height:160px;background:#fff;top:-50px;right:-40px;animation:float 6s ease-in-out infinite;}
.orb2{width:80px;height:80px;background:#a855f7;bottom:-20px;left:38%;animation:float 5s 1s ease-in-out infinite;}

.hero-greeting{font-size:10px;font-weight:700;color:rgba(255,255,255,.65);letter-spacing:.6px;text-transform:uppercase;margin-bottom:3px;}
.hero-name    {font-size:20px;font-weight:800;color:#fff;margin-bottom:4px;line-height:1.2;}
.hero-sub     {font-size:12px;color:rgba(255,255,255,.75);margin-bottom:14px;line-height:1.5;max-width:360px;}

.hero-btn{
    display:inline-flex;align-items:center;gap:6px;
    padding:7px 16px;border-radius:10px;font-size:12px;font-weight:700;
    text-decoration:none;transition:all .2s;
}
.hbp{background:#fff;color:#6366f1;box-shadow:0 3px 10px rgba(0,0,0,.1);}
.hbp:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(0,0,0,.15);}
.hbg{background:rgba(255,255,255,.15);backdrop-filter:blur(6px);color:#fff;border:1.5px solid rgba(255,255,255,.3);}
.hbg:hover{background:rgba(255,255,255,.23);transform:translateY(-1px);}

/* stats strip */
.hero-strip{display:flex;margin:14px -22px 0;background:rgba(0,0,0,.18);backdrop-filter:blur(4px);}
.hs-item{flex:1;padding:10px 12px;border-right:1px solid rgba(255,255,255,.1);text-align:center;}
.hs-item:last-child{border-right:none;}
.hs-val{font-size:17px;font-weight:800;color:#fff;display:block;}
.hs-lbl{font-size:10px;color:rgba(255,255,255,.6);font-weight:500;}

/* ── Compact stat cards ─────────────────────────── */
.sc{
    background:#fff;border-radius:14px;border:1px solid #f1f5f9;
    padding:14px 16px;box-shadow:0 1px 4px rgba(0,0,0,.04);
    display:flex;flex-direction:column;
    transition:transform .2s,box-shadow .2s;
    text-decoration:none;position:relative;overflow:hidden;
}
.sc:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.08);}
.sc::after{
    content:'';position:absolute;top:0;right:0;
    width:56px;height:56px;border-radius:0 14px 0 56px;
    opacity:.06;transition:opacity .3s;
}
.sc:hover::after{opacity:.14;}
.sc-green::after{background:#10b981;}
.sc-blue::after {background:#3b82f6;}
.sc-pink::after {background:#ec4899;}
.sc-amber::after{background:#f59e0b;}

.sc-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;}
.sc-icon{width:36px;height:36px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;}
.sc-lbl{font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.4px;}
.sc-val{font-size:22px;font-weight:800;color:#0f172a;line-height:1;margin:2px 0 6px;}
.sc-sub{font-size:11px;color:#94a3b8;margin-bottom:10px;}
.sc-btn{
    display:flex;align-items:center;justify-content:center;gap:5px;
    padding:7px 12px;border-radius:9px;font-size:11px;font-weight:700;
    text-decoration:none;transition:filter .2s;
}
.sc-btn:hover{filter:brightness(.93);}

/* ── Quick actions ──────────────────────────────── */
.qa{
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    gap:6px;padding:14px 8px;border-radius:14px;
    border:1.5px solid #f1f5f9;background:#fff;
    text-decoration:none;transition:all .2s;text-align:center;
}
.qa:hover{transform:translateY(-3px);border-color:transparent;box-shadow:0 8px 24px rgba(0,0,0,.09);}
.qa-icon{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:17px;transition:transform .2s;}
.qa:hover .qa-icon{transform:scale(1.1) rotate(-4deg);}
.qa-lbl{font-size:11px;font-weight:700;color:#1e293b;}
.qa-sub{font-size:10px;color:#94a3b8;}

/* ── Section cards ──────────────────────────────── */
.sec{background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 4px rgba(0,0,0,.04);overflow:hidden;}
.sec-hd{display:flex;align-items:center;justify-content:space-between;padding:14px 16px 12px;border-bottom:1px solid #f8fafc;}
.sec-title{font-size:13px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px;}
.sec-ti{width:26px;height:26px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:12px;}
.sec-link{font-size:11px;font-weight:600;color:#6366f1;text-decoration:none;display:inline-flex;align-items:center;gap:3px;transition:gap .2s;}
.sec-link:hover{gap:7px;}

/* ── Order row ──────────────────────────────────── */
.or{display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid #f8fafc;text-decoration:none;transition:background .15s;}
.or:last-child{border-bottom:none;}
.or:hover{background:#fafbff;}
.or-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;}
.or-num{font-size:12px;font-weight:700;color:#1e293b;font-family:'Courier New',monospace;}
.or-ven{font-size:11px;color:#94a3b8;margin-top:1px;}
.or-amt{font-size:13px;font-weight:800;color:#0f172a;}
.or-chip{font-size:10px;font-weight:700;padding:2px 8px;border-radius:50px;}

/* ── Transaction row ────────────────────────────── */
.tr{display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid #f8fafc;}
.tr:last-child{border-bottom:none;}
.tr-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;}
.tr-desc{font-size:12px;color:#374151;font-weight:500;line-height:1.3;}
.tr-time{font-size:10px;color:#94a3b8;margin-top:1px;}
.tr-amt{font-size:13px;font-weight:800;}
.tr-bal{font-size:10px;color:#94a3b8;text-align:right;margin-top:1px;}

/* ── Promo bar ──────────────────────────────────── */
.promo{
    background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 55%,#1e3a8a 100%);
    border-radius:14px;padding:16px 20px;
    display:flex;align-items:center;justify-content:space-between;
    gap:16px;flex-wrap:wrap;position:relative;overflow:hidden;
    box-shadow:0 6px 24px rgba(15,23,42,.22);
}
.promo::before{
    content:'';position:absolute;top:-20px;right:-20px;
    width:110px;height:110px;
    background:radial-gradient(circle,rgba(99,102,241,.35) 0%,transparent 70%);
    pointer-events:none;
}

/* ── Trust strip ────────────────────────────────── */
.trust-item{
    background:#fff;border-radius:12px;border:1px solid #f1f5f9;
    padding:12px 14px;display:flex;align-items:center;gap:10px;
    box-shadow:0 1px 3px rgba(0,0,0,.04);
}
.trust-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}

/* ── Empty mini ─────────────────────────────────── */
.empty-mini{padding:20px 16px;text-align:center;}
.empty-mini i{font-size:22px;color:#cbd5e1;margin-bottom:6px;display:block;}
.empty-mini p{font-size:12px;color:#94a3b8;}
</style>
@endpush

@section('content')
@php
    $wallet    = Auth::user()->buyerWallet;
    $balance   = $wallet ? $wallet->balance : 0;
    $cartCount = Auth::user()->cart ? count(Auth::user()->cart->items ?? []) : 0;
    $hour      = now()->hour;
    $greeting  = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

    $sc = [
        'pending'        =>['bg'=>'#fef9c3','c'=>'#854d0e','i'=>'fa-clock'],
        'confirmed'      =>['bg'=>'#dbeafe','c'=>'#1e40af','i'=>'fa-check'],
        'payment_pending'=>['bg'=>'#ffedd5','c'=>'#9a3412','i'=>'fa-credit-card'],
        'processing'     =>['bg'=>'#e0e7ff','c'=>'#3730a3','i'=>'fa-cog'],
        'shipped'        =>['bg'=>'#f3e8ff','c'=>'#6b21a8','i'=>'fa-truck'],
        'delivered'      =>['bg'=>'#dcfce7','c'=>'#15803d','i'=>'fa-check-double'],
        'completed'      =>['bg'=>'#d1fae5','c'=>'#065f46','i'=>'fa-circle-check'],
        'cancelled'      =>['bg'=>'#fee2e2','c'=>'#991b1b','i'=>'fa-times-circle'],
    ];
@endphp

<div class="space-y-4">

{{-- Hero --}}
<div class="hero a1">
    <div class="hero-orb orb1"></div>
    <div class="hero-orb orb2"></div>
    <div class="relative z-10 flex items-start justify-between gap-4">
        <div>
            <div class="hero-greeting"><i class="fas fa-sun mr-1"></i>{{ $greeting }}</div>
            <div class="hero-name">{{ Auth::user()->name }} 👋</div>
            <div class="hero-sub">Track orders, manage your wallet & discover amazing products.</div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('marketplace.index') }}"      class="hero-btn hbp"><i class="fas fa-store text-xs"></i> Shop Now</a>
                <a href="{{ route('buyer.wallet.index') }}"     class="hero-btn hbg"><i class="fas fa-wallet text-xs"></i> Wallet</a>
                @if($cartCount > 0)
                <a href="{{ route('buyer.cart.index') }}"       class="hero-btn hbg"><i class="fas fa-cart-shopping text-xs"></i> Cart ({{ $cartCount }})</a>
                @endif
            </div>
        </div>
        <div class="hidden sm:flex flex-col items-center gap-1 flex-shrink-0" style="animation:float 4s ease-in-out infinite;">
            <div style="width:60px;height:60px;border-radius:16px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-shopping-bag text-white text-2xl"></i>
            </div>
            @if($cartCount > 0)
            <span style="background:#ef4444;color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:50px;">{{ $cartCount }} in cart</span>
            @endif
        </div>
    </div>
    <div class="hero-strip">
        <div class="hs-item"><span class="hs-val counter" data-target="{{ $stats['total_orders'] }}">0</span><span class="hs-lbl">Orders</span></div>
        <div class="hs-item"><span class="hs-val counter" data-target="{{ $stats['wishlist_items'] }}">0</span><span class="hs-lbl">Wishlist</span></div>
        <div class="hs-item"><span class="hs-val counter" data-target="{{ $stats['pending_orders'] }}">0</span><span class="hs-lbl">Pending</span></div>
        <div class="hs-item"><span class="hs-val" style="font-size:13px;">UGX {{ number_format($balance,0) }}</span><span class="hs-lbl">Wallet</span></div>
    </div>
</div>

{{-- Stat cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 a2">
    <a href="{{ route('buyer.wallet.index') }}" class="sc sc-green">
        <div class="sc-top">
            <div class="sc-lbl">Wallet</div>
            <div class="sc-icon" style="background:#dcfce7;"><i class="fas fa-wallet" style="color:#16a34a;"></i></div>
        </div>
        <div class="sc-val" style="font-size:16px;">{{ number_format($balance,0) }}</div>
        <div class="sc-sub">Uganda Shillings</div>
        <span class="sc-btn" style="background:#dcfce7;color:#15803d;"><i class="fas fa-plus text-xs"></i> Add Funds</span>
    </a>

    <a href="{{ route('buyer.orders.index') }}" class="sc sc-blue">
        <div class="sc-top">
            <div class="sc-lbl">Orders</div>
            <div class="sc-icon" style="background:#dbeafe;"><i class="fas fa-shopping-bag" style="color:#2563eb;"></i></div>
        </div>
        <div class="sc-val counter" data-target="{{ $stats['total_orders'] }}">0</div>
        <div class="sc-sub">All time</div>
        <span class="sc-btn" style="background:#dbeafe;color:#1d4ed8;"><i class="fas fa-eye text-xs"></i> View Orders</span>
    </a>

    <a href="{{ route('buyer.wishlist.index') }}" class="sc sc-pink">
        <div class="sc-top">
            <div class="sc-lbl">Wishlist</div>
            <div class="sc-icon" style="background:#fce7f3;"><i class="fas fa-heart" style="color:#db2777;"></i></div>
        </div>
        <div class="sc-val counter" data-target="{{ $stats['wishlist_items'] }}">0</div>
        <div class="sc-sub">Saved items</div>
        <span class="sc-btn" style="background:#fce7f3;color:#be185d;"><i class="fas fa-heart text-xs"></i> View Wishlist</span>
    </a>

    <a href="{{ route('buyer.orders.index',['status'=>'pending']) }}" class="sc sc-amber">
        <div class="sc-top">
            <div class="sc-lbl">Pending</div>
            <div class="sc-icon" style="background:#fef9c3;"><i class="fas fa-clock" style="color:#ca8a04;"></i></div>
        </div>
        <div class="sc-val counter" data-target="{{ $stats['pending_orders'] }}">0</div>
        <div class="sc-sub">Awaiting</div>
        <span class="sc-btn" style="background:#fef9c3;color:#92400e;"><i class="fas fa-route text-xs"></i> Track Orders</span>
    </a>
</div>

{{-- Quick actions --}}
<div class="sec a3">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ti" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-bolt"></i></div>
            Quick Actions
        </div>
    </div>
    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 p-3">
        @foreach([
            [route('marketplace.index'),                    'fa-store',         '#dbeafe','#2563eb','Shop',     'Browse all'],
            [route('buyer.cart.index'),                     'fa-shopping-cart', '#e0e7ff','#6366f1','Cart',     $cartCount.' items'],
            [route('buyer.orders.index'),                   'fa-shopping-bag',  '#dcfce7','#16a34a','Orders',   'Track status'],
            [route('buyer.wishlist.index'),                 'fa-heart',         '#fce7f3','#db2777','Wishlist', 'Saved items'],
            [route('buyer.wallet.index'),                   'fa-wallet',        '#d1fae5','#059669','Wallet',   'Manage funds'],
            [route('buyer.profile'),                        'fa-user-circle',   '#fef3c7','#d97706','Profile',  'Settings'],
        ] as [$href,$icon,$bg,$color,$label,$sub])
        <a href="{{ $href }}" class="qa">
            <div class="qa-icon" style="background:{{ $bg }};color:{{ $color }};"><i class="fas {{ $icon }}"></i></div>
            <div class="qa-lbl">{{ $label }}</div>
            <div class="qa-sub">{{ $sub }}</div>
        </a>
        @endforeach
    </div>
</div>

{{-- Recent orders + transactions --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 a4">

    <div class="sec">
        <div class="sec-hd">
            <div class="sec-title">
                <div class="sec-ti" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-shopping-bag"></i></div>
                Recent Orders
            </div>
            <a href="{{ route('buyer.orders.index') }}" class="sec-link">View all <i class="fas fa-arrow-right"></i></a>
        </div>
        @forelse($recentOrders as $order)
        @php $s = $sc[$order->status] ?? ['bg'=>'#f1f5f9','c'=>'#64748b','i'=>'fa-circle']; @endphp
        <a href="{{ route('buyer.orders.show',$order) }}" class="or">
            <div class="or-icon" style="background:{{ $s['bg'] }};color:{{ $s['c'] }};"><i class="fas {{ $s['i'] }}"></i></div>
            <div class="flex-1 min-w-0">
                <div class="or-num">{{ $order->order_number }}</div>
                <div class="or-ven">{{ $order->vendorProfile->business_name ?? 'Vendor' }} · {{ $order->created_at->diffForHumans() }}</div>
            </div>
            <div class="text-right flex-shrink-0">
                <div class="or-amt">UGX {{ number_format($order->total,0) }}</div>
                <span class="or-chip" style="background:{{ $s['bg'] }};color:{{ $s['c'] }};">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span>
            </div>
            <i class="fas fa-chevron-right text-xs" style="color:#e2e8f0;flex-shrink:0;"></i>
        </a>
        @empty
        <div class="empty-mini">
            <i class="fas fa-shopping-bag"></i>
            <p>No orders yet — <a href="{{ route('marketplace.index') }}" style="color:#6366f1;font-weight:600;">start shopping!</a></p>
        </div>
        @endforelse
    </div>

    <div class="sec">
        <div class="sec-hd">
            <div class="sec-title">
                <div class="sec-ti" style="background:#d1fae5;color:#059669;"><i class="fas fa-receipt"></i></div>
                Transactions
            </div>
            <a href="{{ route('buyer.wallet.transactions') }}" class="sec-link">View all <i class="fas fa-arrow-right"></i></a>
        </div>
        @php
        $txS = [
            'deposit'      =>['bg'=>'#dcfce7','c'=>'#16a34a','i'=>'fa-arrow-down'],
            'withdrawal'   =>['bg'=>'#fee2e2','c'=>'#dc2626','i'=>'fa-arrow-up'],
            'payment'      =>['bg'=>'#e0e7ff','c'=>'#6366f1','i'=>'fa-credit-card'],
            'order_payment'=>['bg'=>'#e0e7ff','c'=>'#6366f1','i'=>'fa-credit-card'],
            'refund'       =>['bg'=>'#fef9c3','c'=>'#ca8a04','i'=>'fa-rotate-left'],
        ];
        @endphp
        @forelse($walletTransactions as $tx)
        @php $t = $txS[$tx->type] ?? ['bg'=>'#f1f5f9','c'=>'#64748b','i'=>'fa-exchange-alt']; $credit = $tx->amount > 0; @endphp
        <div class="tr">
            <div class="tr-icon" style="background:{{ $t['bg'] }};color:{{ $t['c'] }};"><i class="fas {{ $t['i'] }}"></i></div>
            <div class="flex-1 min-w-0">
                <div class="tr-desc truncate">{{ Str::limit($tx->description ?? ucfirst($tx->type), 38) }}</div>
                <div class="tr-time">{{ $tx->created_at->diffForHumans() }}</div>
            </div>
            <div class="text-right flex-shrink-0">
                <div class="tr-amt" style="color:{{ $credit ? '#16a34a' : '#dc2626' }};">{{ $credit ? '+' : '-' }}UGX {{ number_format(abs($tx->amount),0) }}</div>
                <div class="tr-bal">Bal: UGX {{ number_format($tx->balance_after,0) }}</div>
            </div>
        </div>
        @empty
        <div class="empty-mini">
            <i class="fas fa-receipt"></i>
            <p>No transactions yet — <a href="{{ route('buyer.wallet.index') }}" style="color:#059669;font-weight:600;">add funds!</a></p>
        </div>
        @endforelse
    </div>
</div>

{{-- Promo --}}
<div class="promo a5">
    <div class="relative z-10">
        <div style="font-size:10px;font-weight:700;color:rgba(165,180,252,.85);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">
            <i class="fas fa-shield-alt mr-1"></i>Escrow Protection — Always On
        </div>
        <div style="font-size:16px;font-weight:800;color:#fff;margin-bottom:3px;">Your money is 100% safe with us</div>
        <div style="font-size:12px;color:rgba(255,255,255,.6);max-width:380px;">We hold payment securely until you confirm delivery. Total peace of mind, always.</div>
    </div>
    <div class="flex gap-2 flex-wrap relative z-10">
        <a href="{{ route('marketplace.index') }}" class="hero-btn hbp" style="font-size:12px;padding:8px 16px;"><i class="fas fa-store text-xs"></i> Shop Now</a>
        <a href="{{ route('buyer.disputes.index') }}" class="hero-btn hbg" style="font-size:12px;padding:8px 16px;border-color:rgba(165,180,252,.4);"><i class="fas fa-file-shield text-xs"></i> Disputes</a>
    </div>
</div>

{{-- Trust strip --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 a6">
    @foreach([
        ['fa-shield-alt','#059669','#dcfce7','Escrow Protected',  'Money held until delivery'],
        ['fa-truck-fast','#2563eb','#dbeafe','Fast Delivery',      'From verified vendors'],
        ['fa-undo',      '#7c3aed','#ede9fe','30-Day Returns',     'Hassle-free policy'],
        ['fa-headset',   '#d97706','#fef3c7','24/7 Support',       'Always here to help'],
    ] as [$icon,$color,$bg,$title,$sub])
    <div class="trust-item">
        <div class="trust-icon" style="background:{{ $bg }};color:{{ $color }};"><i class="fas {{ $icon }}" style="font-size:14px;"></i></div>
        <div>
            <div style="font-size:12px;font-weight:700;color:#1e293b;">{{ $title }}</div>
            <div style="font-size:10px;color:#94a3b8;">{{ $sub }}</div>
        </div>
    </div>
    @endforeach
</div>

</div>
@endsection

@push('scripts')
<script>
function animateCounter(el) {
    const target = parseInt(el.dataset.target) || 0;
    if (!target) { el.textContent = '0'; return; }
    let cur = 0;
    const inc = target / (900 / 14);
    const t = setInterval(() => {
        cur = Math.min(cur + inc, target);
        el.textContent = Math.floor(cur).toLocaleString();
        if (cur >= target) clearInterval(t);
    }, 14);
}
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.counter').forEach(animateCounter);
});
</script>
@endpush
