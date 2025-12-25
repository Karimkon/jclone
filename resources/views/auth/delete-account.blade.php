@extends('layouts.app')

@section('title', 'Delete Account - BebaMart')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">BebaMart Account Deletion</h1>
            <p class="mt-2 text-gray-600">Request to delete your account and associated data</p>
        </div>

        <!-- Info Card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                What happens when you delete your account?
            </h2>

            <div class="space-y-4">
                <div class="border-l-4 border-red-500 pl-4">
                    <h3 class="font-medium text-gray-900">Data that will be permanently deleted:</h3>
                    <ul class="mt-2 text-gray-600 list-disc list-inside space-y-1">
                        <li>Your profile information (name, email, phone number)</li>
                        <li>Your saved addresses</li>
                        <li>Your wishlist items</li>
                        <li>Your shopping cart</li>
                        <li>Your wallet balance (non-refundable)</li>
                        <li>Your chat messages and conversations</li>
                        <li>Your reviews and ratings</li>
                    </ul>
                </div>

                <div class="border-l-4 border-yellow-500 pl-4">
                    <h3 class="font-medium text-gray-900">Data retained for legal/business purposes (anonymized):</h3>
                    <ul class="mt-2 text-gray-600 list-disc list-inside space-y-1">
                        <li>Order history (retained for 7 years for tax/legal compliance)</li>
                        <li>Transaction records (retained for financial auditing)</li>
                        <li>If you're a vendor: Product listings will be deactivated</li>
                    </ul>
                </div>

                <div class="border-l-4 border-blue-500 pl-4">
                    <h3 class="font-medium text-gray-900">Processing time:</h3>
                    <p class="mt-2 text-gray-600">
                        Your deletion request will be processed within <strong>30 days</strong>.
                        You will receive an email confirmation once your account has been deleted.
                    </p>
                </div>
            </div>
        </div>

        <!-- Request Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-user-times text-red-500 mr-2"></i>
                Request Account Deletion
            </h2>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('delete-account.request') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Enter your account email"
                           value="{{ old('email') }}">
                    <p class="mt-1 text-sm text-gray-500">Enter the email associated with your BebaMart account</p>
                </div>

                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700">
                        Reason for deletion (optional)
                    </label>
                    <select name="reason" id="reason"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select a reason...</option>
                        <option value="no_longer_needed">I no longer need this account</option>
                        <option value="privacy_concerns">Privacy concerns</option>
                        <option value="too_many_emails">Too many emails/notifications</option>
                        <option value="found_alternative">Found an alternative service</option>
                        <option value="temporary_account">This was a temporary account</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label for="comments" class="block text-sm font-medium text-gray-700">
                        Additional comments (optional)
                    </label>
                    <textarea name="comments" id="comments" rows="3"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="Any additional feedback...">{{ old('comments') }}</textarea>
                </div>

                <div class="flex items-start">
                    <input type="checkbox" name="confirm" id="confirm" required
                           class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="confirm" class="ml-2 block text-sm text-gray-700">
                        I understand that this action is <strong>irreversible</strong> and all my data will be permanently deleted.
                    </label>
                </div>

                <div class="pt-4">
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Request Account Deletion
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-600 text-center">
                    Changed your mind?
                    <a href="{{ route('welcome') }}" class="text-indigo-600 hover:text-indigo-500 font-medium">
                        Return to BebaMart
                    </a>
                </p>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>
                Need help? Contact us at
                <a href="mailto:support@bebamart.com" class="text-indigo-600 hover:text-indigo-500">
                    support@bebamart.com
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
