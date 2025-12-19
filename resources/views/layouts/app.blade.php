<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    
    @include('partials.head')

    @yield('styles')
    @stack('styles')
</head>
<body class="bg-gray-50">

    @include('partials.header')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    @yield('scripts')
    @stack('scripts')

    {{-- Chat Badge Logic - Only runs for logged in users --}}
    @auth
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        updateChatBadge();
        setInterval(updateChatBadge, 30000);
    });

    async function updateChatBadge() {
        try {
            const response = await fetch('/chat/api/unread-count', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (data.success) {
                const badge = document.getElementById('chatBadge');
                if (badge) {
                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                        badge.classList.remove('hidden');
                        badge.classList.add('flex');
                    } else {
                        badge.classList.add('hidden');
                        badge.classList.remove('flex');
                    }
                }
            }
        } catch (error) {
            console.error('Failed to update chat badge:', error);
        }
    }
    </script>
    @endauth

    {{-- Analytics Logic --}}
    <script>
    class ProductAnalytics {
        constructor(listingId, source = 'direct') {
            this.listingId = listingId;
            this.source = source;
            this.tracked = new Set();
        }
        async track(type, meta = {}) {
            const key = `${type}_${this.listingId}`;
            if (this.tracked.has(key)) return;
            try {
                await fetch('/api/analytics/track', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        listing_id: this.listingId,
                        type: type,
                        source: this.source,
                        meta: meta
                    })
                });
                this.tracked.add(key);
            } catch (error) { console.error('Analytics error:', error); }
        }
        trackView() { this.track('view'); }
    }

    @if(isset($listing) && request()->routeIs('marketplace.show'))
    document.addEventListener('DOMContentLoaded', () => {
        const analytics = new ProductAnalytics({{ $listing->id }}, '{{ request()->input("source", "direct") }}');
        analytics.trackView();
    });
    @endif
    </script>
</body>
</html>