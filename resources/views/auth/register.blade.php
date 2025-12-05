@extends('layouts.guest')

@section('title', 'Create Buyer Account - ' . config('app.name'))

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <a href="{{ route('welcome') }}" class="inline-flex items-center mb-8">
                <i class="fas fa-store text-2xl text-primary mr-2"></i>
                <span class="text-2xl font-bold text-primary">{{ config('app.name') }}</span>
            </a>
            <h2 class="text-3xl font-extrabold text-gray-900">
                Create Buyer Account
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Already have an account?
                <a href="{{ route('login') }}" class="font-medium text-primary hover:text-indigo-700">
                    Sign in
                </a>
            </p>
        </div>
        
        <form class="mt-8 space-y-6" action="{{ route('register') }}" method="POST">
            @csrf
            <input type="hidden" name="role" value="buyer">
            
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="name" class="sr-only">Full Name</label>
                    <input id="name" name="name" type="text" required 
                           class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                           placeholder="Full Name" 
                           value="{{ old('name') }}"
                           autofocus>
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                           placeholder="Email address" 
                           value="{{ old('email') }}">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="phone" class="sr-only">Phone Number</label>
                    <input id="phone" name="phone" type="tel" required
                           class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                           placeholder="Phone Number" 
                           value="{{ old('phone') }}">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required
                           class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                           placeholder="Password (min. 8 characters)">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password-confirm" class="sr-only">Confirm Password</label>
                    <input id="password-confirm" name="password_confirmation" type="password" required
                           class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                           placeholder="Confirm Password">
                </div>
            </div>

            <div class="flex items-center">
                <input id="terms" name="terms" type="checkbox" required
                       class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                <label for="terms" class="ml-2 block text-sm text-gray-700">
                    I agree to the 
                    <a href="#" class="font-medium text-primary hover:text-indigo-700">Terms of Service</a>
                    and 
                    <a href="#" class="font-medium text-primary hover:text-indigo-700">Privacy Policy</a>
                </label>
            </div>
            @error('terms')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror

            <div class="space-y-3">
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition">
                    <i class="fas fa-user-plus mr-2"></i>
                    Create Buyer Account
                </button>
                
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Want to sell products?
                        <a href="{{ route('vendor.onboard.create') }}" 
                           class="font-medium text-primary hover:text-indigo-700">
                            Become a seller instead
                        </a>
                    </p>
                </div>
            </div>
        </form>
        
        <!-- Buyer Benefits -->
        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6">
            <h3 class="font-semibold text-gray-800 mb-3">🎁 Buyer Benefits</h3>
            <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                    <span>Instant wallet creation with $0 balance</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                    <span>Secure escrow protection for all purchases</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                    <span>Free buyer account - no monthly fees</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                    <span>Access to thousands of local and imported products</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<style>
    /* Add some animation */
    input:focus {
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }
</style>
@endsection