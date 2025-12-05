<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - JClone</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="login-card w-full max-w-md p-8">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-block p-4 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl mb-4 floating">
                <i class="fas fa-crown text-white text-4xl"></i>
            </div>
            <h1 class="text-3xl font-bold gradient-text">Admin Portal</h1>
            <p class="text-gray-600 mt-2">JClone Marketplace Management</p>
        </div>
        
        <!-- Login Form -->
        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf
            
            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
            @endif
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    <i class="fas fa-envelope mr-2"></i>Email Address
                </label>
                <input type="email" name="email" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                       placeholder="admin@jclone.com">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    <i class="fas fa-lock mr-2"></i>Password
                </label>
                <input type="password" name="password" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                       placeholder="••••••••">
            </div>
            
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-3 px-4 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition duration-300 transform hover:-translate-y-1">
                <i class="fas fa-sign-in-alt mr-2"></i>Login as Administrator
            </button>
        </form>
        
        <!-- Back Link -->
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to regular login
            </a>
        </div>
        
        <!-- Security Note -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-500 text-center">
                <i class="fas fa-shield-alt mr-1"></i>
                This portal is for authorized administrators only. All activities are logged.
            </p>
        </div>
    </div>
</body>
</html>