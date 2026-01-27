@extends('layouts.vendor')

@section('title', 'Account Deactivated - BebaMart')
@section('page_title', 'Account Deactivated')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-8 text-center">
        <!-- Icon -->
        <div class="bg-red-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-ban text-red-500 text-4xl"></i>
        </div>

        <!-- Title -->
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Account Deactivated</h1>

        <!-- Message -->
        <p class="text-gray-600 mb-6">
            {{ $message ?? 'Your vendor account has been deactivated.' }}
        </p>

        <p class="text-gray-500 text-sm mb-8">
            If you believe this was a mistake or would like to appeal this decision, please contact our support team.
        </p>

        <!-- Contact Info -->
        <div class="bg-gray-50 rounded-xl p-6 mb-6">
            <h3 class="font-semibold text-gray-700 mb-4">Contact Support</h3>

            <div class="space-y-3">
                @if(isset($support_email))
                <a href="mailto:{{ $support_email }}" class="flex items-center justify-center text-indigo-600 hover:text-indigo-800">
                    <i class="fas fa-envelope mr-2"></i>
                    {{ $support_email }}
                </a>
                @endif

                @if(isset($support_phone))
                <a href="tel:{{ $support_phone }}" class="flex items-center justify-center text-indigo-600 hover:text-indigo-800">
                    <i class="fas fa-phone mr-2"></i>
                    {{ $support_phone }}
                </a>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col space-y-3">
            <a href="mailto:{{ $support_email ?? 'support@bebamart.com' }}?subject=Account Deactivation Appeal"
               class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition">
                <i class="fas fa-paper-plane mr-2"></i>
                Submit an Appeal
            </a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-gray-500 hover:text-gray-700">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
