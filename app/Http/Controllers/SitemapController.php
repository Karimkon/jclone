<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Category;
use App\Models\VendorProfile;
use App\Models\JobListing;
use App\Models\VendorService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate main sitemap index
     */
    public function index()
    {
        $sitemaps = [
            ['loc' => route('sitemap.pages'), 'lastmod' => now()->toDateString()],
            ['loc' => route('sitemap.products'), 'lastmod' => Listing::max('updated_at')?->toDateString() ?? now()->toDateString()],
            ['loc' => route('sitemap.categories'), 'lastmod' => Category::max('updated_at')?->toDateString() ?? now()->toDateString()],
            ['loc' => route('sitemap.vendors'), 'lastmod' => VendorProfile::max('updated_at')?->toDateString() ?? now()->toDateString()],
            ['loc' => route('sitemap.jobs'), 'lastmod' => JobListing::max('updated_at')?->toDateString() ?? now()->toDateString()],
            ['loc' => route('sitemap.services'), 'lastmod' => VendorService::max('updated_at')?->toDateString() ?? now()->toDateString()],
        ];

        $content = view('sitemaps.index', compact('sitemaps'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate static pages sitemap
     */
    public function pages()
    {
        $pages = [
            ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('marketplace.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('categories.index'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => route('jobs.index'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => route('services.index'), 'priority' => '0.8', 'changefreq' => 'daily'],
        ];

        $content = view('sitemaps.urls', ['urls' => $pages])->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate products sitemap
     */
    public function products()
    {
        $listings = Listing::where('is_active', true)
            ->select('slug', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->limit(50000)
            ->get()
            ->map(function ($listing) {
                return [
                    'loc' => route('marketplace.show', $listing->slug),
                    'lastmod' => $listing->updated_at->toDateString(),
                    'priority' => '0.7',
                    'changefreq' => 'weekly',
                ];
            });

        $content = view('sitemaps.urls', ['urls' => $listings])->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate categories sitemap
     */
    public function categories()
    {
        $categories = Category::where('is_active', true)
            ->select('slug', 'updated_at')
            ->get()
            ->map(function ($category) {
                return [
                    'loc' => route('categories.show', $category->slug),
                    'lastmod' => $category->updated_at?->toDateString() ?? now()->toDateString(),
                    'priority' => '0.6',
                    'changefreq' => 'weekly',
                ];
            });

        $content = view('sitemaps.urls', ['urls' => $categories])->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate vendors sitemap
     */
    public function vendors()
    {
        $vendors = VendorProfile::where('vetting_status', 'approved')
            ->select('id', 'updated_at')
            ->get()
            ->map(function ($vendor) {
                return [
                    'loc' => route('vendor.store.show', $vendor->id),
                    'lastmod' => $vendor->updated_at?->toDateString() ?? now()->toDateString(),
                    'priority' => '0.5',
                    'changefreq' => 'weekly',
                ];
            });

        $content = view('sitemaps.urls', ['urls' => $vendors])->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate jobs sitemap
     */
    public function jobs()
    {
        $jobs = JobListing::where('status', 'active')
            ->where('expires_at', '>', now())
            ->select('slug', 'updated_at')
            ->get()
            ->map(function ($job) {
                return [
                    'loc' => route('jobs.show', $job->slug),
                    'lastmod' => $job->updated_at?->toDateString() ?? now()->toDateString(),
                    'priority' => '0.6',
                    'changefreq' => 'daily',
                ];
            });

        $content = view('sitemaps.urls', ['urls' => $jobs])->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate services sitemap
     */
    public function services()
    {
        $services = VendorService::where('is_active', true)
            ->select('slug', 'updated_at')
            ->get()
            ->map(function ($service) {
                return [
                    'loc' => route('services.show', $service->slug),
                    'lastmod' => $service->updated_at?->toDateString() ?? now()->toDateString(),
                    'priority' => '0.6',
                    'changefreq' => 'weekly',
                ];
            });

        $content = view('sitemaps.urls', ['urls' => $services])->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
