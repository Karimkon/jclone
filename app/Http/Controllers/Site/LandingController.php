<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Promotion;
use App\Models\ContactMessage; 
use Illuminate\Http\Request;


class LandingController extends Controller
{
    /**
     * Display homepage
     */
 public function index()
    {
        // Get categories with children and listing counts
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->where('is_active', true)
                    ->with(['children' => function($q) {
                        $q->where('is_active', true);
                    }]);
            }])
            ->orderBy('name')
            ->get();

        // Add total listings count to each category (includes descendants)
        $categories->each(function($category) {
            $category->listings_count = $category->total_listings_count;
        });

        // Featured products (you can add a 'is_featured' column later)
        $featuredProducts = Listing::where('is_active', true)
            ->with(['images', 'category', 'vendor'])
            ->inRandomOrder()
            ->take(10)
            ->get();

        // Trending/New Arrivals
        $newArrivals = Listing::where('is_active', true)
            ->with(['images', 'category', 'vendor'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Recently added products
        $recentProducts = Listing::where('is_active', true)
            ->with(['images', 'category'])
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();

        // Flash deals (products with potential discounts)
        $flashDeals = Listing::where('is_active', true)
            ->with(['images', 'category'])
            ->where('stock', '>', 0)
            ->inRandomOrder()
            ->take(10)
            ->get();

        // Top selling
        $topSelling = Listing::where('is_active', true)
            ->with(['images', 'category'])
            ->where('stock', '>', 0)
            ->inRandomOrder()
            ->take(5)
            ->get();

        // Imported products
        $importedProducts = Listing::where('is_active', true)
            ->where('origin', 'imported')
            ->with(['images', 'category'])
            ->take(8)
            ->get();

        // Local products
        $localProducts = Listing::where('is_active', true)
            ->where('origin', 'local')
            ->with(['images', 'category'])
            ->take(8)
            ->get();

        return view('welcome', compact(
            'categories',
            'featuredProducts',
            'newArrivals',
            'recentProducts',
            'flashDeals',
            'topSelling',
            'importedProducts',
            'localProducts'
        ));
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
 * Display FAQ page
 */
public function faq()
{
    $faqs = [
        [
            'question' => 'What is ' . config('app.name') . '?',
            'answer' => config('app.name') . ' is a secure online marketplace with escrow protection. We connect buyers and sellers while ensuring safe transactions through our escrow system.'
        ],
        [
            'question' => 'How does escrow work?',
            'answer' => 'When you buy a product, your payment is held securely in escrow. The seller ships your order, and you have time to inspect it. Once you confirm receipt, the payment is released to the seller.'
        ],
        [
            'question' => 'How do I become a vendor?',
            'answer' => 'Click "Become a Vendor" in the navigation menu or visit the vendor registration page. You\'ll need to provide business information, ID verification, and agree to our terms. Once approved, you can start listing products.'
        ],
        [
            'question' => 'How long does shipping take?',
            'answer' => 'Shipping times vary: Local products: 1-3 days, Imported products: 7-14 days. You\'ll receive tracking information once your order is shipped.'
        ],
        [
            'question' => 'What payment methods do you accept?',
            'answer' => 'We accept mobile money, credit/debit cards, and bank transfers. All payments are processed securely through our escrow system.'
        ],
        [
            'question' => 'Can I return a product?',
            'answer' => 'Yes, we have a 30-day return policy for most items. Products must be in original condition with packaging. Some items (perishable, custom-made) may not be returnable.'
        ],
        [
            'question' => 'How do I track my order?',
            'answer' => 'After your order ships, you\'ll receive a tracking number via email/SMS. You can also check order status in your account dashboard.'
        ],
        [
            'question' => 'What if I don\'t receive my order?',
            'answer' => 'If your order doesn\'t arrive within the estimated time, contact our support team. Since payments are held in escrow, you won\'t lose your money.'
        ],
        [
            'question' => 'Are there any seller fees?',
            'answer' => 'We charge a small commission on successful sales. There are no listing fees or monthly subscriptions. Detailed commission rates are available in the vendor dashboard.'
        ],
        [
            'question' => 'How do I contact customer support?',
            'answer' => 'You can reach us via: Email: support@' . parse_url(config('app.url'), PHP_URL_HOST) . ', Live Chat: Available on our website, Phone: +256 XXX XXX XXX'
        ]
    ];
    
    return view('site.faq', compact('faqs'));
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

     /**
     * Display contact page
     */
   public function contact()
    {
        return view('site.contact');
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
            'message' => 'required|string|min:10',
            'contact_type' => 'required|in:buyer,vendor,support,partner,other',
        ]);

        // Create contact message
        ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'contact_type' => $validated['contact_type'],
            'status' => 'new',
        ]);

        return back()->with('success', 'Thank you for your message. We will get back to you soon!');
    }
}