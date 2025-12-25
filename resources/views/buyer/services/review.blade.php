@extends('layouts.buyer')

@section('title', 'Leave a Review - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8 pb-20 sm:pb-8">
    <!-- Back Button -->
    <a href="{{ route('buyer.service-requests.show', $request->id) }}" class="inline-flex items-center text-gray-600 hover:text-primary mb-4">
        <i class="fas fa-arrow-left mr-2"></i> Back to Request
    </a>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-4 sm:p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-star text-yellow-500 text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Leave a Review</h1>
                <p class="text-gray-600 mt-2">Share your experience with {{ $request->service->vendor->business_name ?? 'the service provider' }}</p>
            </div>

            <!-- Service Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-900">{{ $request->service->title ?? 'Service' }}</h3>
                <p class="text-sm text-gray-600 mt-1">
                    <i class="fas fa-store mr-1"></i>
                    {{ $request->service->vendor->business_name ?? 'Provider' }}
                </p>
                @if($request->final_price)
                <p class="text-sm text-green-600 mt-1">
                    <i class="fas fa-tag mr-1"></i>
                    UGX {{ number_format($request->final_price, 0) }}
                </p>
                @endif
            </div>

            <form action="{{ route('buyer.service-requests.review.submit', $request->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Star Rating -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Your Rating *</label>
                    <div class="flex justify-center gap-2" id="starRating">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" onclick="setRating({{ $i }})"
                                class="star-btn text-4xl text-gray-300 hover:text-yellow-400 transition focus:outline-none"
                                data-rating="{{ $i }}">
                            <i class="fas fa-star"></i>
                        </button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" value="" required>
                    <p class="text-center text-sm text-gray-500 mt-2" id="ratingText">Click to rate</p>
                    @error('rating')
                    <p class="text-red-500 text-sm mt-1 text-center">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Review Comment -->
                <div class="mb-6">
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Your Review (Optional)</label>
                    <textarea name="comment" id="comment" rows="5"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                              placeholder="Tell others about your experience...">{{ old('comment') }}</textarea>
                    @error('comment')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Photo Upload -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Add Photos (Optional)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition cursor-pointer"
                         onclick="document.getElementById('images').click()">
                        <i class="fas fa-camera text-gray-400 text-3xl mb-2"></i>
                        <p class="text-sm text-gray-600">Click to upload photos</p>
                        <p class="text-xs text-gray-400 mt-1">Max 5MB per image</p>
                        <input type="file" name="images[]" id="images" class="hidden" multiple accept="image/*">
                    </div>
                    <div id="imagePreview" class="flex flex-wrap gap-2 mt-3"></div>
                    @error('images.*')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-semibold transition">
                    <i class="fas fa-paper-plane mr-2"></i> Submit Review
                </button>
            </form>
        </div>

        <!-- Tips -->
        <div class="mt-6 bg-blue-50 rounded-xl p-4">
            <h3 class="font-semibold text-blue-800 mb-2"><i class="fas fa-lightbulb mr-2"></i>Tips for a Great Review</h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• Be specific about what the provider did well</li>
                <li>• Mention the quality of work and timeliness</li>
                <li>• Share if you would recommend this service</li>
                <li>• Keep it honest and constructive</li>
            </ul>
        </div>
    </div>
</div>

<script>
const ratingTexts = {
    1: 'Poor',
    2: 'Fair',
    3: 'Good',
    4: 'Very Good',
    5: 'Excellent'
};

function setRating(rating) {
    document.getElementById('ratingInput').value = rating;
    document.getElementById('ratingText').textContent = ratingTexts[rating];

    document.querySelectorAll('.star-btn').forEach((btn, index) => {
        const star = btn.querySelector('i');
        if (index < rating) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-400');
        } else {
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-300');
        }
    });
}

// Image preview
document.getElementById('images').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';

    Array.from(e.target.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-20 h-20 object-cover rounded-lg';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endsection
