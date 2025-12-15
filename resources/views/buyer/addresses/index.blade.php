@extends('layouts.buyer')

@section('title', 'My Addresses - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">My Shipping Addresses</h1>
        <a href="{{ route('buyer.addresses.create') }}" 
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i> Add New Address
        </a>
    </div>

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

    @if($addresses->isEmpty())
    <div class="text-center py-12 bg-gray-50 rounded-lg">
        <i class="fas fa-map-marker-alt text-gray-300 text-5xl mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-600 mb-2">No shipping addresses yet</h3>
        <p class="text-gray-500 mb-6">Add your first shipping address to get started</p>
        <a href="{{ route('buyer.addresses.create') }}" 
           class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700">
            Add Address
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($addresses as $address)
        <div class="bg-white rounded-lg shadow-sm border {{ $address->is_default ? 'border-indigo-300 border-2' : 'border-gray-200' }}">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-2">
                        @if($address->label)
                        <span class="bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1 rounded">
                            {{ $address->label }}
                        </span>
                        @endif
                        @if($address->is_default)
                        <span class="bg-indigo-100 text-indigo-700 text-xs font-medium px-2 py-1 rounded">
                            Default
                        </span>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('buyer.addresses.edit', $address->id) }}" 
                           class="text-gray-500 hover:text-indigo-600">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('buyer.addresses.destroy', $address->id) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this address?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-500 hover:text-red-600">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Recipient</p>
                        <p class="font-medium">{{ $address->recipient_name }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Phone</p>
                        <p class="font-medium">{{ $address->recipient_phone }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Address</p>
                        <p class="text-gray-700">
                            {{ $address->address_line_1 }}<br>
                            @if($address->address_line_2)
                            {{ $address->address_line_2 }}<br>
                            @endif
                            {{ $address->city }}, 
                            @if($address->state_region){{ $address->state_region }}, @endif
                            @if($address->postal_code){{ $address->postal_code }}, @endif
                            {{ $address->country }}
                        </p>
                    </div>

                    @if($address->delivery_instructions)
                    <div>
                        <p class="text-sm text-gray-500">Delivery Instructions</p>
                        <p class="text-gray-700 text-sm italic">{{ $address->delivery_instructions }}</p>
                    </div>
                    @endif
                </div>

                @if(!$address->is_default)
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <form action="{{ route('buyer.addresses.set-default', $address->id) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                            Set as Default
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-8 text-sm text-gray-500">
        <p>Note: You can have up to 3 shipping addresses. The default address will be used for checkout.</p>
    </div>
    @endif
</div>
@endsection