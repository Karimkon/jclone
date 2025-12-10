<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - {{ config('app.name') }}</title>
    @include('partials.head')
</head>
<body class="bg-gray-50">
    @include('partials.header')
    
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-sm p-6 md:p-8">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Terms & Conditions</h1>
                <p class="text-gray-600">Last updated: {{ date('F j, Y') }}</p>
            </div>
            
            <div class="space-y-6">
                <!-- Introduction -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">1. Introduction</h2>
                    <p class="text-gray-600 mb-2">
                        Welcome to {{ config('app.name') }} ("Platform", "we", "us", or "our"). These Terms and Conditions govern your access to and use of our marketplace platform, including any content, functionality, and services offered.
                    </p>
                    <p class="text-gray-600">
                        By accessing or using the Platform, you agree to be bound by these Terms. If you disagree with any part of these Terms, you may not access the Platform.
                    </p>
                </section>

                <!-- Definitions -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">2. Definitions</h2>
                    <ul class="list-disc pl-5 text-gray-600 space-y-2">
                        <li><strong>"Buyer"</strong> means a registered user who purchases products through the Platform.</li>
                        <li><strong>"Vendor"</strong> means a registered seller who lists and sells products through the Platform.</li>
                        <li><strong>"Escrow"</strong> means our payment protection system where funds are held securely until delivery is confirmed.</li>
                        <li><strong>"Listing"</strong> means any product or service offered for sale on the Platform.</li>
                        <li><strong>"Order"</strong> means a legally binding contract between Buyer and Vendor for the purchase of goods.</li>
                    </ul>
                </section>

                <!-- Account Registration -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">3. Account Registration</h2>
                    <div class="space-y-3">
                        <p class="text-gray-600">
                            To access certain features, you must register an account. You agree to:
                        </p>
                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                            <li>Provide accurate, current, and complete information</li>
                            <li>Maintain the security of your password</li>
                            <li>Accept responsibility for all activities under your account</li>
                            <li>Notify us immediately of any unauthorized access</li>
                            <li>Be at least 18 years old to register</li>
                        </ul>
                    </div>
                </section>

                <!-- Vendor Terms -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">4. Vendor Terms</h2>
                    <div class="space-y-4">
                        <h3 class="font-bold text-gray-700">4.1 Vendor Verification</h3>
                        <p class="text-gray-600">
                            All vendors must complete our verification process, which may include:
                        </p>
                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                            <li>Government-issued ID verification</li>
                            <li>Business registration documents</li>
                            <li>Tax identification number</li>
                            <li>Bank account verification</li>
                            <li>Guarantor validation</li>
                        </ul>

                        <h3 class="font-bold text-gray-700">4.2 Product Listings</h3>
                        <p class="text-gray-600">
                            Vendors agree to:
                        </p>
                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                            <li>Provide accurate product descriptions and images</li>
                            <li>Maintain adequate stock levels</li>
                            <li>Honor listed prices</li>
                            <li>Ship products within specified timeframes</li>
                            <li>Comply with all applicable laws and regulations</li>
                        </ul>

                        <h3 class="font-bold text-gray-700">4.3 Prohibited Items</h3>
                        <p class="text-gray-600">
                            Vendors may not list:
                        </p>
                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                            <li>Counterfeit or stolen goods</li>
                            <li>Illegal substances or weapons</li>
                            <li>Adult content or explicit material</li>
                            <li>Products infringing intellectual property rights</li>
                            <li>Hazardous materials</li>
                        </ul>
                    </div>
                </section>

                <!-- Buyer Terms -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">5. Buyer Terms</h2>
                    <div class="space-y-3">
                        <p class="text-gray-600">
                            Buyers agree to:
                        </p>
                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                            <li>Make payments through our secure escrow system</li>
                            <li>Confirm delivery within 48 hours of receipt</li>
                            <li>Initiate disputes within 7 days of delivery</li>
                            <li>Provide accurate shipping information</li>
                            <li>Not engage in fraudulent activities</li>
                        </ul>
                    </div>
                </section>

                <!-- Escrow System -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">6. Escrow Payment Protection</h2>
                    <div class="space-y-3">
                        <p class="text-gray-600">
                            Our escrow system works as follows:
                        </p>
                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                            <li>Buyer payment is held securely in escrow</li>
                            <li>Vendor ships product after payment confirmation</li>
                            <li>Buyer confirms receipt within 48 hours</li>
                            <li>Funds are released to vendor after confirmation</li>
                            <li>Disputes are mediated by our team</li>
                        </ul>
                        <p class="text-gray-600 italic">
                            Note: Escrow fees are 2% of transaction value, capped at $50.
                        </p>
                    </div>
                </section>

                <!-- Import Services -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">7. Import Services</h2>
                    <p class="text-gray-600">
                        For imported products, we provide:
                    </p>
                    <ul class="list-disc pl-5 text-gray-600 space-y-2">
                        <li>Shipping coordination</li>
                        <li>Customs clearance</li>
                        <li>Tax and duty calculation</li>
                        <li>Last-mile delivery</li>
                    </ul>
                    <p class="text-gray-600 mt-2">
                        Import duties and taxes are calculated at checkout and paid by the buyer.
                    </p>
                </section>

                <!-- Fees & Commissions -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">8. Fees & Commissions</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-gray-50 rounded-lg">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="py-3 px-4 text-left text-gray-700 font-bold">Service</th>
                                    <th class="py-3 px-4 text-left text-gray-700 font-bold">Fee</th>
                                    <th class="py-3 px-4 text-left text-gray-700 font-bold">Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="py-3 px-4">Vendor Commission</td>
                                    <td class="py-3 px-4">15%</td>
                                    <td class="py-3 px-4">Of total sale price</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4">Escrow Fee</td>
                                    <td class="py-3 px-4">2%</td>
                                    <td class="py-3 px-4">Capped at $50</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4">Withdrawal Fee</td>
                                    <td class="py-3 px-4">$2</td>
                                    <td class="py-3 px-4">Per withdrawal request</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4">Promotion Fee</td>
                                    <td class="py-3 px-4">Variable</td>
                                    <td class="py-3 px-4">Based on promotion type</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Returns & Refunds -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">9. Returns & Refunds</h2>
                    <div class="space-y-3">
                        <h3 class="font-bold text-gray-700">9.1 Return Policy</h3>
                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                            <li>Items may be returned within 7 days of delivery</li>
                            <li>Products must be unused and in original packaging</li>
                            <li>Buyer pays return shipping unless item is defective</li>
                            <li>Refunds processed within 14 business days</li>
                        </ul>

                        <h3 class="font-bold text-gray-700">9.2 Non-Returnable Items</h3>
                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                            <li>Perishable goods</li>
                            <li>Custom-made products</li>
                            <li>Digital products</li>
                            <li>Personal care items</li>
                        </ul>
                    </div>
                </section>

                <!-- Dispute Resolution -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">10. Dispute Resolution</h2>
                    <div class="space-y-3">
                        <p class="text-gray-600">
                            In case of disputes:
                        </p>
                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                            <li>Parties must attempt resolution through our platform first</li>
                            <li>We will mediate and provide resolution within 5 business days</li>
                            <li>If unsatisfied, disputes may be escalated to local authorities</li>
                            <li>Our decision is final for transactions under $1000</li>
                        </ul>
                    </div>
                </section>

                <!-- Intellectual Property -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">11. Intellectual Property</h2>
                    <p class="text-gray-600">
                        All platform content, features, and functionality are owned by {{ config('app.name') }} and are protected by international copyright laws. Vendors retain rights to their product images and descriptions but grant us a license to display them on our platform.
                    </p>
                </section>

                <!-- Limitation of Liability -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">12. Limitation of Liability</h2>
                    <p class="text-gray-600">
                        {{ config('app.name') }} acts as an intermediary platform. We are not liable for:
                    </p>
                    <ul class="list-disc pl-5 text-gray-600 space-y-2">
                        <li>Product quality or merchantability</li>
                        <li>Delivery delays beyond our control</li>
                        <li>Vendor misrepresentation</li>
                        <li>Losses exceeding the transaction value</li>
                    </ul>
                </section>

                <!-- Termination -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">13. Termination</h2>
                    <p class="text-gray-600">
                        We may terminate or suspend your account immediately, without prior notice, for conduct that we believe violates these Terms or is harmful to other users.
                    </p>
                </section>

                <!-- Changes to Terms -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">14. Changes to Terms</h2>
                    <p class="text-gray-600">
                        We reserve the right to modify these Terms at any time. We will notify users of material changes via email or platform notification. Continued use after changes constitutes acceptance.
                    </p>
                </section>

                <!-- Governing Law -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">15. Governing Law</h2>
                    <p class="text-gray-600">
                        These Terms shall be governed by the laws of Uganda. Any disputes shall be resolved in the courts of Kampala.
                    </p>
                </section>

                <!-- Contact -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">16. Contact Information</h2>
                    <p class="text-gray-600">
                        For questions about these Terms, contact us at:
                    </p>
                    <div class="mt-3 p-4 bg-gray-50 rounded-lg">
                        <p><strong>Email:</strong> legal@yourmarketplace.com</p>
                        <p><strong>Phone:</strong> +256 700 000 000</p>
                        <p><strong>Address:</strong> Plot 123, Kampala Road, Kampala, Uganda</p>
                    </div>
                </section>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    By using {{ config('app.name') }}, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.
                </p>
            </div>
        </div>
    </div>

    @include('partials.footer')
</body>
</html>