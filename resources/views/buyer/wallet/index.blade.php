@extends('layouts.buyer')

@section('title', 'My Wallet - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8 pb-20 sm:pb-8">
    <h1 class="text-2xl sm:text-3xl font-bold mb-6 sm:mb-8">My Wallet</h1>
    
    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
        <p class="text-green-700">{{ session('success') }}</p>
    </div>
    @endif
    
    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
        <p class="text-red-700">{{ session('error') }}</p>
    </div>
    @endif
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-8">
        <!-- Wallet Balance Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <div class="text-center mb-4 sm:mb-6">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-primary text-white rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                        <i class="fas fa-wallet text-2xl sm:text-3xl"></i>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Wallet Balance</h2>
                </div>

                <div class="text-center mb-4 sm:mb-6">
                    <div class="text-3xl sm:text-5xl font-bold text-primary mb-2">UGX {{ number_format($wallet->balance, 0) }}</div>
                    <p class="text-sm sm:text-base text-gray-600">Available Balance</p>

                    @if($wallet->locked_balance > 0)
                    <div class="mt-3 sm:mt-4">
                        <p class="text-xs sm:text-sm text-gray-600">Locked: UGX {{ number_format($wallet->locked_balance, 0) }}</p>
                        <p class="text-xs text-gray-500">(Pending withdrawals)</p>
                    </div>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="space-y-2 sm:space-y-3">
                    <button onclick="openDepositModal()"
                            class="w-full bg-primary text-white py-2.5 sm:py-3 rounded-lg font-semibold hover:bg-indigo-700 transition text-sm sm:text-base">
                        <i class="fas fa-plus mr-2"></i> Add Funds
                    </button>

                    <button onclick="openWithdrawModal()"
                            class="w-full border border-primary text-primary py-2.5 sm:py-3 rounded-lg font-semibold hover:bg-primary hover:text-white transition text-sm sm:text-base">
                        <i class="fas fa-arrow-up mr-2"></i> Withdraw Funds
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Transactions -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <div class="flex justify-between items-center mb-4 sm:mb-6">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-800">Recent Transactions</h2>
                    <a href="{{ route('buyer.wallet.transactions') }}" class="text-primary hover:text-indigo-700 text-xs sm:text-sm font-medium">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                @if($transactions->count() > 0)
                <div class="space-y-3 sm:space-y-4">
                    @foreach($transactions as $transaction)
                    <div class="border border-gray-200 rounded-lg p-3 sm:p-4 hover:bg-gray-50 transition">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2 sm:gap-4">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium
                                        @if($transaction->type === 'deposit') bg-green-100 text-green-800
                                        @elseif($transaction->type === 'withdrawal') bg-red-100 text-red-800
                                        @elseif($transaction->type === 'payment') bg-blue-100 text-blue-800
                                        @elseif($transaction->type === 'refund') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                    <span class="text-xs sm:text-sm text-gray-500">
                                        {{ $transaction->created_at->format('M d, Y') }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-700">{{ Str::limit($transaction->description, 50) }}</p>
                                @if($transaction->reference)
                                <p class="text-xs text-gray-500 mt-1">Ref: {{ Str::limit($transaction->reference, 20) }}</p>
                                @endif
                            </div>

                            <div class="flex items-center justify-between sm:block sm:text-right border-t sm:border-0 pt-2 sm:pt-0">
                                <div class="font-bold text-base sm:text-lg @if($transaction->amount > 0) text-green-600 @else text-red-600 @endif">
                                    @if($transaction->amount > 0)
                                    +UGX {{ number_format($transaction->amount, 0) }}
                                    @else
                                    -UGX {{ number_format(abs($transaction->amount), 0) }}
                                    @endif
                                </div>
                                <div class="flex sm:flex-col items-center sm:items-end gap-2">
                                    <span class="text-xs text-gray-500 hidden sm:block">
                                        Bal: UGX {{ number_format($transaction->balance_after, 0) }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                        @if($transaction->status === 'completed') bg-green-100 text-green-800
                                        @elseif($transaction->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($transaction->status === 'failed') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-4 sm:mt-6">
                    {{ $transactions->links() }}
                </div>
                @else
                <div class="text-center py-8 sm:py-12">
                    <div class="text-gray-400 text-4xl sm:text-5xl mb-4">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">No transactions yet</h3>
                    <p class="text-sm text-gray-500 mb-4">Your wallet transaction history will appear here</p>
                    <button onclick="openDepositModal()"
                            class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 bg-primary text-white rounded-lg font-semibold hover:bg-indigo-700 transition text-sm">
                        <i class="fas fa-plus mr-2"></i> Make Your First Deposit
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Deposit Modal -->
<div id="depositModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Add Funds to Wallet</h3>
            <button onclick="closeDepositModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="{{ route('buyer.wallet.deposit') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Amount ($)</label>
                <input type="number" name="amount" required min="1" step="0.01"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="e.g., 100.00">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Payment Method</label>
                <select name="payment_method" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Select Method</option>
                    <option value="card">Credit/Debit Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="mobile_money">Mobile Money</option>
                </select>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Reference (Optional)</label>
                <input type="text" name="reference"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="Transaction reference">
            </div>
            
            <div class="flex space-x-3">
                <button type="submit" 
                        class="flex-1 bg-primary text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    <i class="fas fa-lock mr-2"></i> Proceed to Payment
                </button>
                <button type="button" onclick="closeDepositModal()"
                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Withdraw Modal -->
<div id="withdrawModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Withdraw Funds</h3>
            <button onclick="closeWithdrawModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="{{ route('buyer.wallet.withdraw') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Amount ($)</label>
                <input type="number" name="amount" required min="1" step="0.01"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="e.g., 100.00"
                       max="{{ $wallet->balance }}">
                <p class="text-sm text-gray-500 mt-1">Available: ${{ number_format($wallet->balance, 2) }}</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Withdrawal Method</label>
                <select name="withdrawal_method" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Select Method</option>
                    <option value="bank_account">Bank Account</option>
                    <option value="mobile_money">Mobile Money</option>
                </select>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Account Details</label>
                <textarea name="account_details" required rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                          placeholder="Bank account number or mobile money details..."></textarea>
            </div>
            
            <div class="flex space-x-3">
                <button type="submit" 
                        class="flex-1 bg-primary text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    <i class="fas fa-paper-plane mr-2"></i> Submit Request
                </button>
                <button type="button" onclick="closeWithdrawModal()"
                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDepositModal() {
    document.getElementById('depositModal').classList.remove('hidden');
    document.getElementById('depositModal').classList.add('flex');
}

function closeDepositModal() {
    document.getElementById('depositModal').classList.add('hidden');
    document.getElementById('depositModal').classList.remove('flex');
}

function openWithdrawModal() {
    document.getElementById('withdrawModal').classList.remove('hidden');
    document.getElementById('withdrawModal').classList.add('flex');
}

function closeWithdrawModal() {
    document.getElementById('withdrawModal').classList.add('hidden');
    document.getElementById('withdrawModal').classList.remove('flex');
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    const depositModal = document.getElementById('depositModal');
    const withdrawModal = document.getElementById('withdrawModal');
    
    if (depositModal && !depositModal.contains(event.target) && event.target.closest('[onclick="openDepositModal()"]') === null) {
        closeDepositModal();
    }
    
    if (withdrawModal && !withdrawModal.contains(event.target) && event.target.closest('[onclick="openWithdrawModal()"]') === null) {
        closeWithdrawModal();
    }
});
</script>
@endsection