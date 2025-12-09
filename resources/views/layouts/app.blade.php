<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'JClone Marketplace')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    @yield('styles')
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

    <!-- Authentication Modal -->
    <div id="authModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Login Required</h3>
                    <button onclick="closeAuthModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <p class="text-gray-600 mb-6">
                    Please login or create an account to continue with this action.
                </p>
                <div class="flex space-x-3">
                    <a href="{{ route('login') }}" class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-lg text-center hover:bg-indigo-700 transition">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                    <a href="{{ route('register') }}" class="flex-1 border-2 border-indigo-600 text-indigo-600 py-2 px-4 rounded-lg text-center hover:bg-indigo-50 transition">
                        <i class="fas fa-user-plus mr-2"></i>Sign Up
                    </a>
                </div>
            </div>
        </div>
    </div>

    @yield('scripts')
    @stack('scripts')
</body>
</html>