@extends('layouts.app')

@section('content')
<div class="bg-white py-16">
    <div class="container mx-auto px-4 max-w-4xl">
        <h1 class="text-4xl font-bold text-ink-900 mb-8 font-display">Returns & Refund Policy</h1>
        
        <div class="prose prose-blue max-w-none text-ink-600 space-y-6">
            <p class="text-lg">At <strong>{{ config('app.name') }}</strong>, we want you to shop with confidence. Our Escrow-backed protection ensures that your money is safe until you are satisfied with your purchase.</p>

            <section>
                <h2 class="text-2xl font-bold text-ink-800 mb-4">1. The 14-Day Buyer Protection</h2>
                <p>Most items purchased on Bebamart are covered by our Buyer Protection. You have <strong>14 days</strong> from the date of delivery to request a return or refund if:</p>
                <ul class="list-disc ml-6 space-y-2">
                    <li>The item is significantly different from the description.</li>
                    <li>The item arrived damaged or defective.</li>
                    <li>The item did not arrive at all (Escrow Refund).</li>
                </ul>
            </section>

            <section class="bg-brand-50 p-6 rounded-xl border border-brand-100">
                <h2 class="text-2xl font-bold text-brand-800 mb-4">2. How Escrow Protects You</h2>
                <p>When you pay for an item, {{ config('app.name') }} holds the funds securely. The payment is only released to the vendor <strong>after</strong> you confirm receipt and satisfaction, or after the 14-day protection period expires.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-ink-800 mb-4">3. Return Conditions</h2>
                <p>To be eligible for a return, the item must be:</p>
                <ul class="list-disc ml-6 space-y-2">
                    <li>In the same condition that you received it.</li>
                    <li>Unworn or unused, with tags, and in its original packaging.</li>
                    <li>Accompanied by the receipt or proof of purchase.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-ink-800 mb-4">4. Non-Returnable Items</h2>
                <p>Certain types of items cannot be returned, such as:</p>
                <ul class="list-disc ml-6 space-y-2">
                    <li>Perishable goods (food, flowers, or plants).</li>
                    <li>Custom products (special orders or personalized items).</li>
                    <li>Personal care goods (beauty products).</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-ink-800 mb-4">5. Refund Process</h2>
                <p>Once the vendor receives and inspects your return, we will notify you of the approval. If approved, the refund will be processed back to your original payment method (Mobile Money or Bank Card) within 5-7 business days.</p>
            </section>

            <div class="pt-8 border-t border-ink-100">
                <p class="text-sm text-ink-400">If you have questions, please contact our support team at <a href="{{ route('site.contact') }}" class="text-brand-600 underline">Help Center</a>.</p>
            </div>
        </div>
    </div>
</div>
@endsection