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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background */
        .bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            z-index: 0;
        }

        .bg-pattern::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at center, transparent 0%, transparent 50%, rgba(255,255,255,0.03) 50%);
            background-size: 30px 30px;
            animation: float 30s linear infinite;
        }

        @keyframes float {
            0% { transform: translate(0, 0); }
            100% { transform: translate(30px, 30px); }
        }

        /* Glass morphism card */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Form inputs */
        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.2s ease;
            background: #f9fafb;
        }

        .form-input:focus {
            outline: none;
            border-color: #6366f1;
            background: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .form-input.has-icon {
            padding-left: 48px;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            transition: color 0.2s;
        }

        .input-group:focus-within .input-icon {
            color: #6366f1;
        }

        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            transition: color 0.2s;
            background: none;
            border: none;
            padding: 0;
        }

        .password-toggle:hover {
            color: #6366f1;
        }

        /* Buttons */
        .btn-primary {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Social buttons */
        .btn-social {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background: white;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-social:hover {
            border-color: #6366f1;
            background: #f8fafc;
        }

        /* Checkbox */
        .custom-checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            appearance: none;
            -webkit-appearance: none;
            position: relative;
        }

        .custom-checkbox:checked {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-color: #6366f1;
        }

        .custom-checkbox:checked::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 10px;
        }

        /* Links */
        .link {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .link:hover {
            color: #4f46e5;
            text-decoration: underline;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e5e7eb;
        }

        .divider span {
            padding: 0 16px;
        }

        /* Alerts */
        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        /* Password strength */
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: #e5e7eb;
            overflow: hidden;
            margin-top: 8px;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
        }

        /* Mobile navigation */
        .mobile-nav {
            display: none;
        }

        @media (max-width: 640px) {
            body {
                background: linear-gradient(180deg, #667eea 0%, #764ba2 50%, #f8fafc 50%);
            }

            .mobile-nav {
                display: flex;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                padding: 16px;
                z-index: 10;
            }

            .desktop-nav {
                display: none;
            }

            .auth-container {
                padding-top: 70px;
            }

            .glass-card {
                border-radius: 24px 24px 0 0;
                margin-top: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Background Pattern -->
    <div class="bg-pattern"></div>

    <!-- Desktop Navigation -->
    <nav class="desktop-nav relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="{{ route('welcome') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                        <i class="fas fa-store text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-bold text-white">{{ config('app.name') }}</span>
                </a>
                <div class="flex items-center gap-6">
                    <a href="{{ route('welcome') }}" class="text-white/80 hover:text-white transition flex items-center gap-2">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="{{ route('marketplace.index') }}" class="text-white/80 hover:text-white transition flex items-center gap-2">
                        <i class="fas fa-shopping-bag"></i> Shop
                    </a>
                    @if(request()->routeIs('register'))
                    <a href="{{ route('login') }}" class="px-5 py-2.5 bg-white/20 backdrop-blur text-white rounded-xl font-medium hover:bg-white/30 transition">
                        Sign In
                    </a>
                    @else
                    <a href="{{ route('register') }}" class="px-5 py-2.5 bg-white text-indigo-600 rounded-xl font-medium hover:bg-white/90 transition">
                        Get Started
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <a href="{{ route('welcome') }}" class="flex items-center gap-2">
            <div class="w-9 h-9 bg-white/20 backdrop-blur rounded-lg flex items-center justify-center">
                <i class="fas fa-store text-white"></i>
            </div>
            <span class="font-bold text-white">{{ config('app.name') }}</span>
        </a>
    </div>

    <!-- Main Content -->
    <main class="auth-container relative z-10 min-h-screen flex items-center justify-center px-4 py-8">
        @yield('content')
    </main>

    <!-- Footer (desktop only) -->
    <footer class="hidden sm:block relative z-10 py-6">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center text-white/60 text-sm">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <div class="mt-2 flex items-center justify-center gap-6">
                    <a href="#" class="hover:text-white transition">Terms</a>
                    <a href="#" class="hover:text-white transition">Privacy</a>
                    <a href="#" class="hover:text-white transition">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Password visibility toggle
        document.querySelectorAll('.password-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strength-fill');
        const strengthText = document.getElementById('strength-text');

        if (passwordInput && strengthBar) {
            passwordInput.addEventListener('input', function(e) {
                const password = e.target.value;
                let strength = 0;

                if (password.length >= 8) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;

                const widths = ['0%', '25%', '50%', '75%', '100%'];
                const colors = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#10b981'];
                const texts = ['', 'Weak', 'Fair', 'Good', 'Strong'];

                strengthBar.style.width = widths[strength];
                strengthBar.style.background = colors[strength];

                if (strengthText) {
                    strengthText.textContent = texts[strength];
                    strengthText.style.color = colors[strength];
                }
            });
        }

        // Form loading state
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Please wait...';
                }
            });
        });

        // Phone number formatting
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.startsWith('256')) {
                    value = value.slice(3);
                }
                if (value.length > 9) {
                    value = value.slice(0, 9);
                }
                e.target.value = value ? '+256 ' + value : '';
            });
        }
    </script>
</body>
</html>
