@extends('layouts.app')

@section('title', $service->title . ' - BebaMart Services')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('services.index') }}" class="hover:text-purple-600">Services</a>
            @if($service->category)
            <span class="mx-2">/</span>
            <a href="{{ route('services.index', ['category' => $service->category->id]) }}" class="hover:text-purple-600">{{ $service->category->name }}</a>
            @endif
            <span class="mx-2">/</span>
            <span class="text-gray-800">{{ $service->title }}</span>
        </nav>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Images -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    @if($service->images && count($service->images) > 0)
                    <div class="relative">
                        <img src="{{ asset('storage/' . $service->images[0]) }}" alt="{{ $service->title }}" 
                             class="w-full h-80 object-cover" id="mainImage">
                        
                        @if(count($service->images) > 1)
                        <div class="absolute bottom-4 left-4 right-4 flex gap-2 overflow-x-auto">
                            @foreach($service->images as $index => $image)
                            <img src="{{ asset('storage/' . $image) }}" 
                                 alt="Image {{ $index + 1 }}"
                                 class="w-20 h-20 object-cover rounded-lg cursor-pointer border-2 {{ $index === 0 ? 'border-purple-500' : 'border-white' }} hover:border-purple-500"
                                 onclick="document.getElementById('mainImage').src='{{ asset('storage/' . $image) }}'">
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="h-80 bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center">
                        <i class="fas fa-tools text-6xl text-white/30"></i>
                    </div>
                    @endif
                </div>
                
                <!-- Title & Info -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            @if($service->category)
                            <span class="text-sm text-purple-600 font-medium">{{ $service->category->name }}</span>
                            @endif
                            <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $service->title }}</h1>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-purple-600">{{ $service->formatted_price }}</div>
                            @if($service->duration)
                            <div class="text-sm text-gray-500">{{ $service->duration }}</div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Stats -->
                    <div class="flex flex-wrap items-center gap-4 mt-4 pt-4 border-t text-sm text-gray-500">
                        <span><i class="fas fa-map-marker-alt mr-1"></i> {{ $service->city }}@if($service->location), {{ $service->location }}@endif</span>
                        <span><i class="fas fa-eye mr-1"></i> {{ $service->views_count }} views</span>
                        @if($service->reviews_count > 0)
                        <span><i class="fas fa-star text-yellow-400 mr-1"></i> {{ number_format($service->average_rating, 1) }} ({{ $service->reviews_count }} reviews)</span>
                        @endif
                        @if($service->is_mobile)
                        <span class="text-green-600"><i class="fas fa-truck mr-1"></i> Mobile Service</span>
                        @endif
                    </div>
                </div>
                
                <!-- Description -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Description</h2>
                    <div class="prose max-w-none text-gray-600">
                        {!! nl2br(e($service->description)) !!}
                    </div>
                </div>
                
                <!-- Features -->
                @if($service->features && count($service->features) > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">What's Included</h2>
                    <ul class="space-y-2">
                        @foreach($service->features as $feature)
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <span class="text-gray-600">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                <!-- Reviews -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-800">Reviews</h2>
                        @if($service->reviews_count > 0)
                        <div class="flex items-center gap-1">
                            <i class="fas fa-star text-yellow-400"></i>
                            <span class="font-bold">{{ number_format($service->average_rating, 1) }}</span>
                            <span class="text-gray-500">({{ $service->reviews_count }})</span>
                        </div>
                        @endif
                    </div>
                    
                    @if($service->reviews->count() > 0)
                    <div class="space-y-4">
                        @foreach($service->reviews->take(5) as $review)
                        <div class="border-b pb-4 last:border-b-0 last:pb-0">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-gray-800">{{ $review->user->name }}</span>
                                        <span class="text-sm text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 mt-1">
                                        @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star text-sm {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                        @endfor
                                    </div>
                                    @if($review->comment)
                                    <p class="text-gray-600 mt-2">{{ $review->comment }}</p>
                                    @endif
                                    
                                    @if($review->vendor_response)
                                    <div class="bg-gray-50 rounded-lg p-3 mt-2">
                                        <span class="text-sm font-medium text-gray-700">Response from vendor:</span>
                                        <p class="text-sm text-gray-600 mt-1">{{ $review->vendor_response }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-500 text-center py-4">No reviews yet</p>
                    @endif
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Vendor Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-4 pb-4 border-b">
                        <div class="w-16 h-16 rounded-lg bg-gray-200 overflow-hidden">
                            @if($service->vendor->logo)
                                <img src="{{ $service->vendor->logo }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-purple-500 flex items-center justify-center text-white text-xl font-bold">
                                    {{ strtoupper(substr($service->vendor->business_name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ $service->vendor->business_name }}</h3>
                            <p class="text-sm text-gray-500">{{ $service->vendor->business_address ?? $service->city }}</p>
                        </div>
                    </div>
                    
                    <div class="py-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Member since</span>
                            <span class="text-gray-800">{{ $service->vendor->created_at->format('M Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Services offered</span>
                            <span class="text-gray-800">{{ $vendorServices->count() + 1 }}</span>
                        </div>
                    </div>
                    
                    <a href="{{ route('chat.start', ['vendor' => $service->vendor->id]) }}" 
                       class="w-full block text-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 mb-2">
                        <i class="fas fa-comments mr-2"></i> Chat with Vendor
                    </a>
                    
                    @if($service->vendor->phone)
                    <a href="tel:{{ $service->vendor->phone }}" 
                       class="w-full block text-center px-4 py-2 border border-purple-600 text-purple-600 rounded-lg hover:bg-purple-50">
                        <i class="fas fa-phone mr-2"></i> Call Now
                    </a>
                    @endif
                </div>
                
                <!-- Request Service -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Request This Service</h3>
                    
                    @auth
                    <form action="{{ route('services.request', $service->slug) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Your Name *</label>
                                <input type="text" name="customer_name" value="{{ auth()->user()->name }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                                <input type="text" name="customer_phone" value="{{ auth()->user()->phone }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Describe Your Need *</label>
                                <textarea name="description" rows="3" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                          placeholder="Tell us what you need..."></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Date</label>
                                <input type="date" name="preferred_date" min="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                <input type="text" name="location" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       placeholder="Where do you need the service?">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Urgency</label>
                                <select name="urgency" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <option value="normal">Normal</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="emergency">Emergency</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="w-full px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium">
                                <i class="fas fa-paper-plane mr-2"></i> Send Request
                            </button>
                        </div>
                    </form>
                    @else
                    <p class="text-gray-600 text-sm mb-4">Please login to request this service</p>
                    <a href="{{ route('login') }}" class="w-full block text-center px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Login to Continue
                    </a>
                    @endauth
                </div>
                
                <!-- Quick Inquiry -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Quick Inquiry</h3>
                    <form action="{{ route('services.inquiry', $service->slug) }}" method="POST" id="inquiryForm">
                        @csrf
                        <div class="space-y-3">
                            <input type="text" name="name" placeholder="Your Name *" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <input type="text" name="phone" placeholder="Phone Number *" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <textarea name="message" rows="2" placeholder="Your Message *" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                            <button type="submit" class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                                Send Inquiry
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Other Services by Vendor -->
        @if($vendorServices->count() > 0)
        <div class="mt-12">
            <h2 class="text-xl font-bold text-gray-800 mb-6">More from {{ $service->vendor->business_name }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                @foreach($vendorServices as $vs)
                <a href="{{ route('services.show', $vs->slug) }}" class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition group">
                    <div class="h-32 bg-gray-200">
                        @if($vs->primary_image)
                        <img src="{{ $vs->primary_image }}" class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full bg-gradient-to-br from-purple-400 to-indigo-500 flex items-center justify-center">
                            <i class="fas fa-tools text-2xl text-white/50"></i>
                        </div>
                        @endif
                    </div>
                    <div class="p-3">
                        <h3 class="font-medium text-gray-800 group-hover:text-purple-600 line-clamp-2 text-sm">{{ $vs->title }}</h3>
                        <p class="text-purple-600 font-semibold text-sm mt-1">{{ $vs->formatted_price }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- Similar Services -->
        @if($similarServices->count() > 0)
        <div class="mt-12">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Similar Services</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                @foreach($similarServices as $ss)
                <a href="{{ route('services.show', $ss->slug) }}" class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition group">
                    <div class="h-32 bg-gray-200">
                        @if($ss->primary_image)
                        <img src="{{ $ss->primary_image }}" class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full bg-gradient-to-br from-purple-400 to-indigo-500 flex items-center justify-center">
                            <i class="fas fa-tools text-2xl text-white/50"></i>
                        </div>
                        @endif
                    </div>
                    <div class="p-3">
                        <h3 class="font-medium text-gray-800 group-hover:text-purple-600 line-clamp-2 text-sm">{{ $ss->title }}</h3>
                        <p class="text-sm text-gray-500">{{ $ss->vendor->business_name }}</p>
                        <p class="text-purple-600 font-semibold text-sm mt-1">{{ $ss->formatted_price }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection