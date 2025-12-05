<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'JClone Marketplace')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Public Navigation -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <a href="{{ route('welcome') }}" class="text-2xl font-bold text-indigo-600">
                    <i class="fas fa-store mr-2"></i>JClone
                </a>

                <!-- Public Navigation -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('welcome') }}" class="text-gray-700 hover:text-indigo-600">Home</a>
                    <a href="{{ route('marketplace.index') }}" class="text-gray-700 hover:text-indigo-600">Marketplace</a>
                    <a href="{{ route('categories.index') }}" class="text-gray-700 hover:text-indigo-600">Categories</a>
                    
                    @auth
                        <!-- User Menu -->
                        <div class="relative inline-block">
                            <button class="flex items-center space-x-2">
                                <i class="fas fa-user-circle text-xl"></i>
                                <span>{{ auth()->user()->name }}</span>
                            </button>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-indigo-600">Login</a>
                        <a href="{{ route('vendor.login') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg">Sell on JClone</a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <button class="md:hidden" id="mobileMenuButton">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div class="md:hidden hidden" id="mobileMenu">
                <div class="py-4 border-t">
                    <a href="{{ route('welcome') }}" class="block py-2">Home</a>
                    <a href="{{ route('marketplace.index') }}" class="block py-2">Marketplace</a>
                    <a href="{{ route('categories.index') }}" class="block py-2">Categories</a>
                    
                    @auth
                        <div class="pt-4 border-t">
                            <p class="font-medium">{{ auth()->user()->name }}</p>
                            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit" class="text-red-600">Logout</button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="block py-2">Login</a>
                        <a href="{{ route('vendor.login') }}" class="block py-2 bg-indigo-600 text-white px-4 py-2 rounded-lg mt-2 text-center">Sell on JClone</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-4 py-8">
            <div class="text-center">
                <p>&copy; {{ date('Y') }} JClone Marketplace. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuButton').addEventListener('click', function() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        });
    </script>
    
    @stack('scripts')
</body>
</html>