@extends('layouts.app')

@section('title', 'Unsubscribe - ' . config('app.name'))

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 py-16">
    <div class="max-w-md w-full text-center">
        @if($success)
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-green-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-3">Unsubscribed</h1>
            <p class="text-gray-600 mb-6">{{ $message }}</p>
        @else
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-3">Oops!</h1>
            <p class="text-gray-600 mb-6">{{ $message }}</p>
        @endif

        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
            <i class="fas fa-home"></i> Back to Home
        </a>
    </div>
</div>
@endsection
