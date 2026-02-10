<?php

namespace App\Console\Commands;

use App\Jobs\ProcessCampaignJob;
use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Console\Command;

class ProcessScheduledCampaigns extends Command
{
    protected $signature = 'campaigns:process-scheduled';
    protected $description = 'Dispatch queued jobs for campaigns that are due to be sent';

    public function handle(CampaignService $campaignService): int
    {
        $campaigns = Campaign::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No scheduled campaigns to process.');
            return 0;
        }

        foreach ($campaigns as $campaign) {
            $this->info("Processing campaign #{$campaign->id}: {$campaign->title}");

            // Build recipients if not already built
            if ($campaign->total_recipients === 0) {
                $count = $campaignService->buildRecipientsForCampaign($campaign);
                $this->info("  Built {$count} recipients.");
            }

            ProcessCampaignJob::dispatch($campaign);
            $this->info("  Dispatched to queue.");
        }

        $this->info("Processed {$campaigns->count()} campaign(s).");
        return 0;
    }
}
