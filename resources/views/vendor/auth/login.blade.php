<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Login - JClone</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }
        
        .vendor-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.1);
        }
        
        .btn-vendor {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
        
        .btn-vendor:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="vendor-card w-full max-w-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-8 text-white">
            <div class="flex items-center space-x-4">
                <div class="bg-white/20 p-4 rounded-2xl">
                    <i class="fas fa-store text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Seller Portal</h1>
                    <p class="opacity-90">Manage your store on JClone Marketplace</p>
                </div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Login Form -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Vendor Login</h2>
                    
                    <form action="{{ route('vendor.login.submit') }}" method="POST">
                        @csrf
                        
                        @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                        @endif
                        
                        @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul>
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="vendor@example.com"
                                   value="{{ old('email') }}">
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 mb-2">Password</label>
                            <input type="password" name="password" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="••••••••">
                        </div>
                        
                        <button type="submit" class="btn-vendor text-white w-full py-3 rounded-lg font-bold transition duration-300 hover:shadow-lg">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login to Seller Dashboard
                        </button>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-user mr-1"></i>Buyer login
                        </a>
                    </div>
                </div>
                
                <!-- Registration Info -->
                <div class="bg-blue-50 p-6 rounded-xl">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Not a vendor yet?</h3>
                    <p class="text-gray-600 mb-4">Join thousands of sellers on JClone Marketplace:</p>
                    
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Sell to millions of customers</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Integrated logistics & warehousing</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Secure escrow payments</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Import goods with duty calculator</span>
                        </li>
                    </ul>
                    
                    <a href="{{ route('vendor.onboard.create') }}" class="block text-center bg-white text-blue-600 border-2 border-blue-600 py-3 rounded-lg font-bold hover:bg-blue-50 transition">
                        <i class="fas fa-store-alt mr-2"></i>Become a Seller
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="bg-gray-50 p-6 border-t">
            <div class="flex justify-between items-center text-sm text-gray-600">
                <div>
                    <i class="fas fa-shield-alt mr-1"></i>Secure Vendor Portal
                </div>
                <div>
                    Need help? <a href="#" class="text-blue-600 hover:text-blue-800">Contact Support</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>