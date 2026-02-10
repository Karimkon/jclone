<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminNewsletterController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsletterSubscriber::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }

        $subscribers = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => NewsletterSubscriber::count(),
            'subscribed' => NewsletterSubscriber::where('status', 'subscribed')->count(),
            'unsubscribed' => NewsletterSubscriber::where('status', 'unsubscribed')->count(),
        ];

        return view('admin.newsletters.index', compact('subscribers', 'stats'));
    }

    public function export()
    {
        $filename = 'newsletter_subscribers_' . date('Y-m-d') . '.csv';

        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Email', 'Status', 'Subscribed At', 'Unsubscribed At', 'IP Address']);

            NewsletterSubscriber::orderBy('created_at', 'desc')
                ->chunk(500, function ($subscribers) use ($handle) {
                    foreach ($subscribers as $sub) {
                        fputcsv($handle, [
                            $sub->email,
                            $sub->status,
                            $sub->subscribed_at?->format('Y-m-d H:i:s'),
                            $sub->unsubscribed_at?->format('Y-m-d H:i:s'),
                            $sub->ip_address,
                        ]);
                    }
                });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }

    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();

        return redirect()->route('admin.newsletters.index')
            ->with('success', 'Subscriber deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,unsubscribe',
            'ids' => 'required|array',
            'ids.*' => 'exists:newsletter_subscribers,id',
        ]);

        $query = NewsletterSubscriber::whereIn('id', $request->ids);

        if ($request->action === 'delete') {
            $count = $query->count();
            $query->delete();
            return redirect()->route('admin.newsletters.index')
                ->with('success', "{$count} subscriber(s) deleted.");
        }

        if ($request->action === 'unsubscribe') {
            $count = $query->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);
            return redirect()->route('admin.newsletters.index')
                ->with('success', "{$count} subscriber(s) unsubscribed.");
        }

        return redirect()->route('admin.newsletters.index');
    }
}
