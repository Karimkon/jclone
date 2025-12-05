<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Promotion;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Display homepage
     */
    public function index()
    {
        // Get active categories for navigation
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('order')
            ->take(12)
            ->get()
            ->pluck('name');
        
        // Get flash sales (active promotions)
        $flashSales = Promotion::with(['listing.images', 'listing.vendor'])
            ->where('type', 'flash_sale')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();
        
        // Get new arrivals (recent imported products)
        $newArrivals = Listing::with(['images', 'vendor'])
            ->where('is_active', true)
            ->where('origin', 'imported')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();
        
        // Get featured local products
        $localProducts = Listing::with(['images', 'vendor'])
            ->where('is_active', true)
            ->where('origin', 'local')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();
        
        // Get top categories with product count
        $topCategories = Category::withCount('listings')
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('listings_count', 'desc')
            ->take(6)
            ->get();
        
        return view('welcome', compact(
            'categories',
            'flashSales',
            'newArrivals',
            'localProducts',
            'topCategories'
        ));
    }

    /**
     * Display about page
     */
    public function about()
    {
        return view('site.about');
    }

    /**
     * Display contact page
     */
    public function contact()
    {
        return view('site.contact');
    }

    /**
     * Display FAQ page
     */
    public function faq()
    {
        $faqs = [
            [
                'question' => 'How do I buy products?',
                'answer' => 'Browse products, add to cart, checkout securely with multiple payment options.'
            ],
            [
                'question' => 'How does vendor verification work?',
                'answer' => 'All vendors undergo ID verification, business documentation check, and guarantor validation.'
            ],
            [
                'question' => 'What is escrow protection?',
                'answer' => 'Your payment is held securely until you confirm receipt of goods.'
            ],
            [
                'question' => 'How do imports work?',
                'answer' => 'We handle shipping, customs clearance, and delivery for imported goods.'
            ],
            [
                'question' => 'What are the shipping costs?',
                'answer' => 'Shipping costs vary based on weight, distance, and delivery speed.'
            ],
            [
                'question' => 'How long does delivery take?',
                'answer' => 'Local: 1-3 days, Imported: 7-14 days depending on customs.'
            ],
        ];
        
        return view('site.faq', compact('faqs'));
    }

    /**
     * Display terms and conditions
     */
    public function terms()
    {
        return view('site.terms');
    }

    /**
     * Display privacy policy
     */
    public function privacy()
    {
        return view('site.privacy');
    }

    /**
     * Handle contact form submission
     */
    public function submitContact(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'subject' => 'required|string|max:255',
        'message' => 'required|string|max:2000',
        'contact_type' => 'required|in:support,vendor,buyer,other',
    ]);

    // Store contact message
    \App\Models\ContactMessage::create($validated);

    // Queue email notification
    \App\Models\NotificationQueue::create([
        'user_id' => null, // Admin notification
        'type' => 'contact_form',
        'title' => 'New Contact Form: ' . $validated['subject'],
        'message' => "From: {$validated['name']} ({$validated['email']})\nType: {$validated['contact_type']}\n\nMessage:\n{$validated['message']}",
        'meta' => $validated,
        'status' => 'pending',
    ]);

    return back()->with('success', 'Thank you for contacting us! We will respond within 24 hours.');
}

    /**
     * Display vendor benefits page
     */
    public function vendorBenefits()
    {
        $benefits = [
            [
                'icon' => 'fas fa-users',
                'title' => 'Large Customer Base',
                'description' => 'Access thousands of active buyers on our platform.'
            ],
            [
                'icon' => 'fas fa-shield-alt',
                'title' => 'Escrow Protection',
                'description' => 'Secure payments with our escrow system.'
            ],
            [
                'icon' => 'fas fa-plane',
                'title' => 'Import Assistance',
                'description' => 'We handle shipping and customs for imports.'
            ],
            [
                'icon' => 'fas fa-chart-line',
                'title' => 'Sales Analytics',
                'description' => 'Detailed insights into your sales performance.'
            ],
            [
                'icon' => 'fas fa-truck',
                'title' => 'Logistics Support',
                'description' => 'Warehousing and delivery services available.'
            ],
            [
                'icon' => 'fas fa-headset',
                'title' => '24/7 Support',
                'description' => 'Dedicated support team for vendors.'
            ],
        ];

        $stats = [
            ['value' => '10,000+', 'label' => 'Active Buyers'],
            ['value' => '95%', 'label' => 'Secure Transactions'],
            ['value' => '24h', 'label' => 'Average Support Response'],
            ['value' => '15%', 'label' => 'Average Commission'],
        ];

        return view('site.vendor-benefits', compact('benefits', 'stats'));
    }

    /**
     * Display how it works page
     */
    public function howItWorks()
    {
        $buyerSteps = [
            ['step' => 1, 'title' => 'Browse Products', 'description' => 'Search or browse categories to find products.'],
            ['step' => 2, 'title' => 'Add to Cart', 'description' => 'Select products and add them to your cart.'],
            ['step' => 3, 'title' => 'Checkout Securely', 'description' => 'Pay through escrow for buyer protection.'],
            ['step' => 4, 'title' => 'Track Delivery', 'description' => 'Monitor your order status in real-time.'],
            ['step' => 5, 'title' => 'Confirm Receipt', 'description' => 'Confirm delivery to release payment to vendor.'],
        ];

        $vendorSteps = [
            ['step' => 1, 'title' => 'Register', 'description' => 'Complete vendor onboarding with document verification.'],
            ['step' => 2, 'title' => 'List Products', 'description' => 'Add products with images and descriptions.'],
            ['step' => 3, 'title' => 'Receive Orders', 'description' => 'Get notified when buyers purchase your products.'],
            ['step' => 4, 'title' => 'Process Orders', 'description' => 'Pack and ship orders through our logistics.'],
            ['step' => 5, 'title' => 'Get Paid', 'description' => 'Receive payment after buyer confirms delivery.'],
        ];

        return view('site.how-it-works', compact('buyerSteps', 'vendorSteps'));
    }
}