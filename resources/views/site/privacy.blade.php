<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - {{ config('app.name') }}</title>
    @include('partials.head')
</head>
<body class="bg-gray-50">
    @include('partials.header')
    
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-sm p-6 md:p-8">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Privacy Policy</h1>
                <p class="text-gray-600">Last updated: {{ date('F j, Y') }}</p>
            </div>
            
            <div class="space-y-6">
                <!-- Introduction -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">1. Introduction</h2>
                    <p class="text-gray-600">
                        {{ config('app.name') }} ("we", "us", "our") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our marketplace platform.
                    </p>
                </section>

                <!-- Information We Collect -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">2. Information We Collect</h2>
                    <div class="space-y-3">
                        <h3 class="font-bold text-gray-700">2.1 Personal Information</h3>
                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                            <li>Name, email address, phone number</li>
                            <li>Shipping and billing addresses</li>
                            <li>Payment information (processed securely by payment providers)</li>
                            <li>Government ID (for vendor verification)</li>
                            <li>Business registration documents</li>
                        </ul>

                        <h3 class="font-bold text-gray-700">2.2 Usage Data</h3>
                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                            <li>IP address, browser type, device information</li>
                            <li>Pages visited, time spent on pages</li>
                            <li>Search queries, clicked products</li>
                            <li>Purchase history and preferences</li>
                        </ul>
                    </div>
                </section>

                <!-- How We Use Information -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">3. How We Use Your Information</h2>
                    <div class="space-y-3">
                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                            <li>Process transactions and manage orders</li>
                            <li>Provide and improve our services</li>
                            <li>Verify vendor identities and business information</li>
                            <li>Communicate about orders, products, and services</li>
                            <li>Personalize your shopping experience</li>
                            <li>Detect and prevent fraud</li>
                            <li>Comply with legal obligations</li>
                        </ul>
                    </div>
                </section>

                <!-- Data Sharing -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">4. Data Sharing and Disclosure</h2>
                    <div class="space-y-3">
                        <p class="text-gray-600">We may share your information with:</p>
                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                            <li><strong>Service Providers:</strong> Payment processors, logistics partners, customer support</li>
                            <li><strong>Legal Authorities:</strong> When required by law or to protect our rights</li>
                            <li><strong>Business Transfers:</strong> In connection with mergers or acquisitions</li>
                            <li><strong>With Your Consent:</strong> When you explicitly authorize sharing</li>
                        </ul>
                    </div>
                </section>

                <!-- Data Security -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">5. Data Security</h2>
                    <p class="text-gray-600">
                        We implement appropriate technical and organizational security measures to protect your personal information, including:
                    </p>
                    <ul class="list-disc pl-5 text-gray-600 space-y-2 mt-2">
                        <li>SSL encryption for data transmission</li>
                        <li>Secure servers and firewalls</li>
                        <li>Regular security assessments</li>
                        <li>Access controls and authentication</li>
                        <li>Employee training on data protection</li>
                    </ul>
                </section>

                <!-- Data Retention -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">6. Data Retention</h2>
                    <div class="space-y-3">
                        <p class="text-gray-600">We retain your personal information for as long as necessary to:</p>
                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                            <li>Fulfill the purposes outlined in this policy</li>
                            <li>Comply with legal obligations</li>
                            <li>Resolve disputes and enforce agreements</li>
                            <li>Maintain business records</li>
                        </ul>
                        <p class="text-gray-600 italic">
                            Transaction records are retained for 7 years as required by tax laws.
                        </p>
                    </div>
                </section>

                <!-- Your Rights -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">7. Your Rights</h2>
                    <div class="space-y-3">
                        <p class="text-gray-600">You have the right to:</p>
                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                            <li>Access your personal information</li>
                            <li>Correct inaccurate data</li>
                            <li>Request deletion of your data</li>
                            <li>Object to data processing</li>
                            <li>Data portability</li>
                            <li>Withdraw consent</li>
                        </ul>
                        <p class="text-gray-600">
                            To exercise these rights, contact us at privacy@yourmarketplace.com
                        </p>
                    </div>
                </section>

                <!-- Cookies -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">8. Cookies and Tracking</h2>
                    <p class="text-gray-600">
                        We use cookies and similar tracking technologies to:
                    </p>
                    <ul class="list-disc pl-5 text-gray-600 space-y-2 mt-2">
                        <li>Remember your preferences</li>
                        <li>Analyze site traffic and usage</li>
                        <li>Improve user experience</li>
                        <li>Personalize content and ads</li>
                    </ul>
                    <p class="text-gray-600 mt-2">
                        You can control cookies through your browser settings. However, disabling cookies may affect platform functionality.
                    </p>
                </section>

                <!-- Third-Party Links -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">9. Third-Party Links</h2>
                    <p class="text-gray-600">
                        Our platform may contain links to third-party websites. We are not responsible for the privacy practices or content of these external sites. Please review their privacy policies before providing any information.
                    </p>
                </section>

                <!-- Children's Privacy -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">10. Children's Privacy</h2>
                    <p class="text-gray-600">
                        Our services are not directed to individuals under 18 years of age. We do not knowingly collect personal information from children. If you become aware that a child has provided us with personal information, please contact us immediately.
                    </p>
                </section>

                <!-- International Transfers -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">11. International Data Transfers</h2>
                    <p class="text-gray-600">
                        Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place for international data transfers, including standard contractual clauses and adequacy decisions.
                    </p>
                </section>

                <!-- Changes to Policy -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">12. Changes to This Policy</h2>
                    <p class="text-gray-600">
                        We may update this Privacy Policy periodically. We will notify you of material changes via email or platform notification. Continued use after changes constitutes acceptance of the revised policy.
                    </p>
                </section>

                <!-- Contact -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3">13. Contact Us</h2>
                    <p class="text-gray-600">For questions about this Privacy Policy, contact us at:</p>
                    <div class="mt-3 p-4 bg-gray-50 rounded-lg">
                        <p><strong>Email:</strong> support@bebamart.com</p>
                        <p><strong>Phone:</strong> +256 782 971 912</p>
                        <p><strong>Address:</strong> Plot 28, Katula Road, Kisaasi, Uganda</p>
                    </div>
                </section>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    By using {{ config('app.name') }}, you acknowledge that you have read, understood, and agree to this Privacy Policy.
                </p>
            </div>
        </div>
    </div>

    @include('partials.footer')
</body>
</html>