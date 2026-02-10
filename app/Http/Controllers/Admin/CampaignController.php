<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessCampaignJob;
use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    public function __construct(
        protected CampaignService $campaignService
    ) {}

    public function index(Request $request)
    {
        $query = Campaign::with('creator')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $campaigns = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Campaign::count(),
            'sent' => Campaign::where('status', 'sent')->count(),
            'scheduled' => Campaign::where('status', 'scheduled')->count(),
            'draft' => Campaign::where('status', 'draft')->count(),
        ];

        return view('admin.campaigns.index', compact('campaigns', 'stats'));
    }

    public function create()
    {
        return view('admin.campaigns.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:email,sms',
            'audience' => 'required|in:all,buyers,vendors,newsletter,custom',
            'filters' => 'nullable|array',
            'filters.roles' => 'nullable|array',
            'filters.roles.*' => 'string|in:buyer,vendor,admin,support,logistics',
            'scheduled_at' => 'nullable|date|after:now',
            'action' => 'required|in:draft,send,schedule',
        ]);

        $campaign = Campaign::create([
            'title' => $validated['title'],
            'subject' => $validated['subject'] ?? $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'audience' => $validated['audience'],
            'filters' => $validated['filters'] ?? null,
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        if ($validated['action'] === 'send') {
            $this->campaignService->buildRecipientsForCampaign($campaign);
            ProcessCampaignJob::dispatch($campaign);
            $campaign->update(['status' => 'sending']);

            return redirect()->route('admin.campaigns.show', $campaign)
                ->with('success', 'Campaign is being sent! Check back for progress.');
        }

        if ($validated['action'] === 'schedule') {
            if (empty($validated['scheduled_at'])) {
                return back()->withInput()->withErrors(['scheduled_at' => 'Please select a schedule date and time.']);
            }

            $this->campaignService->buildRecipientsForCampaign($campaign);
            $campaign->update([
                'status' => 'scheduled',
                'scheduled_at' => $validated['scheduled_at'],
            ]);

            return redirect()->route('admin.campaigns.show', $campaign)
                ->with('success', 'Campaign scheduled successfully.');
        }

        // Draft
        return redirect()->route('admin.campaigns.show', $campaign)
            ->with('success', 'Campaign saved as draft.');
    }

    public function show(Campaign $campaign)
    {
        $campaign->load(['creator', 'recipients' => function ($q) {
            $q->latest()->limit(100);
        }]);

        $recipientStats = [
            'pending' => $campaign->recipients()->where('status', 'pending')->count(),
            'sent' => $campaign->recipients()->where('status', 'sent')->count(),
            'failed' => $campaign->recipients()->where('status', 'failed')->count(),
            'skipped' => $campaign->recipients()->where('status', 'skipped')->count(),
        ];

        return view('admin.campaigns.show', compact('campaign', 'recipientStats'));
    }

    public function edit(Campaign $campaign)
    {
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return redirect()->route('admin.campaigns.show', $campaign)
                ->with('error', 'Only draft or scheduled campaigns can be edited.');
        }

        return view('admin.campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return redirect()->route('admin.campaigns.show', $campaign)
                ->with('error', 'Only draft or scheduled campaigns can be edited.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:email,sms',
            'audience' => 'required|in:all,buyers,vendors,newsletter,custom',
            'filters' => 'nullable|array',
            'filters.roles' => 'nullable|array',
            'filters.roles.*' => 'string|in:buyer,vendor,admin,support,logistics',
            'scheduled_at' => 'nullable|date|after:now',
            'action' => 'required|in:draft,send,schedule',
        ]);

        $campaign->update([
            'title' => $validated['title'],
            'subject' => $validated['subject'] ?? $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'audience' => $validated['audience'],
            'filters' => $validated['filters'] ?? null,
        ]);

        if ($validated['action'] === 'send') {
            $this->campaignService->buildRecipientsForCampaign($campaign);
            ProcessCampaignJob::dispatch($campaign);
            $campaign->update(['status' => 'sending']);

            return redirect()->route('admin.campaigns.show', $campaign)
                ->with('success', 'Campaign is being sent!');
        }

        if ($validated['action'] === 'schedule') {
            if (empty($validated['scheduled_at'])) {
                return back()->withInput()->withErrors(['scheduled_at' => 'Please select a schedule date and time.']);
            }

            $this->campaignService->buildRecipientsForCampaign($campaign);
            $campaign->update([
                'status' => 'scheduled',
                'scheduled_at' => $validated['scheduled_at'],
            ]);

            return redirect()->route('admin.campaigns.show', $campaign)
                ->with('success', 'Campaign scheduled successfully.');
        }

        // Draft
        $campaign->update(['status' => 'draft']);

        return redirect()->route('admin.campaigns.show', $campaign)
            ->with('success', 'Campaign updated.');
    }

    public function cancel(Campaign $campaign)
    {
        if (!in_array($campaign->status, ['scheduled', 'sending'])) {
            return redirect()->route('admin.campaigns.show', $campaign)
                ->with('error', 'This campaign cannot be cancelled.');
        }

        $campaign->update(['status' => 'cancelled']);

        return redirect()->route('admin.campaigns.show', $campaign)
            ->with('success', 'Campaign has been cancelled.');
    }

    public function duplicate(Campaign $campaign)
    {
        $newCampaign = Campaign::create([
            'title' => $campaign->title . ' (Copy)',
            'subject' => $campaign->subject,
            'message' => $campaign->message,
            'type' => $campaign->type,
            'audience' => $campaign->audience,
            'filters' => $campaign->filters,
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.campaigns.edit', $newCampaign)
            ->with('success', 'Campaign duplicated as draft.');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'subject' => 'nullable|string',
        ]);

        $html = view('emails.campaign', [
            'content' => $request->message,
            'unsubscribeUrl' => '#',
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function getAudienceCount(Request $request)
    {
        $request->validate([
            'audience' => 'required|in:all,buyers,vendors,newsletter,custom',
            'type' => 'required|in:email,sms',
            'filters' => 'nullable|array',
        ]);

        $count = $this->campaignService->getAudienceCount(
            $request->audience,
            $request->type,
            $request->filters ?? []
        );

        return response()->json(['count' => $count]);
    }
}
