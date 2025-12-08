<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .checkbox:checked {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }
        
        .checkbox:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('welcome') }}" class="flex items-center space-x-2">
                        <i class="fas fa-store text-xl text-primary"></i>
                        <span class="text-xl font-bold text-gray-800">{{ config('app.name') }}</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('welcome') }}" class="text-gray-600 hover:text-primary">
                        <i class="fas fa-home mr-1"></i> Home
                    </a>
                    <a href="{{ route('login') }}" class="text-primary hover:text-indigo-700 font-medium">
                        <i class="fas fa-sign-in-alt mr-1"></i> Sign In
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-gray-600 text-sm">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <div class="mt-2 space-x-4">
                    <a href="#" class="hover:text-primary">Terms</a>
                    <a href="#" class="hover:text-primary">Privacy</a>
                    <a href="#" class="hover:text-primary">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Password strength indicator
        document.getElementById('password')?.addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthIndicator = document.getElementById('password-strength');
            
            if (!strengthIndicator) return;
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            const strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'][strength];
            const strengthColors = ['text-red-600', 'text-orange-600', 'text-yellow-600', 'text-blue-600', 'text-green-600'];
            
            strengthIndicator.textContent = `Strength: ${strengthText}`;
            strengthIndicator.className = `text-xs ${strengthColors[strength]}`;
        });

        // Phone number formatting
        document.getElementById('phone')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 3 && value.length <= 6) {
                value = value.replace(/(\d{3})(\d+)/, '$1-$2');
            } else if (value.length > 6) {
                value = value.replace(/(\d{3})(\d{3})(\d+)/, '$1-$2-$3');
            }
            e.target.value = value;
        });
    </script>
</body>
</html>