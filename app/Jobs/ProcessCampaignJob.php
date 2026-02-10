<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\NewsletterSubscriber;
use App\Services\EgoSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 600;

    public function __construct(
        protected Campaign $campaign
    ) {}

    public function handle(EgoSmsService $smsService): void
    {
        $campaign = $this->campaign;

        if ($campaign->status === 'cancelled') {
            return;
        }

        $campaign->update([
            'status' => 'sending',
            'started_at' => now(),
        ]);

        $sentCount = 0;
        $failedCount = 0;

        $campaign->recipients()
            ->where('status', 'pending')
            ->chunkById(50, function ($recipients) use ($campaign, $smsService, &$sentCount, &$failedCount) {
                // Check if cancelled mid-process
                $campaign->refresh();
                if ($campaign->status === 'cancelled') {
                    return false;
                }

                foreach ($recipients as $recipient) {
                    try {
                        if ($recipient->channel === 'email') {
                            $this->sendEmail($campaign, $recipient);
                        } else {
                            $this->sendSms($campaign, $recipient, $smsService);
                        }

                        $recipient->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);
                        $sentCount++;
                    } catch (\Exception $e) {
                        $recipient->update([
                            'status' => 'failed',
                            'error_message' => substr($e->getMessage(), 0, 500),
                        ]);
                        $failedCount++;
                        Log::error('Campaign send failed', [
                            'campaign_id' => $campaign->id,
                            'recipient_id' => $recipient->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // Update counts periodically
                $campaign->update([
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount,
                ]);
            });

        // Final status update
        $finalStatus = $failedCount === $campaign->total_recipients ? 'failed' : 'sent';

        $campaign->update([
            'status' => $finalStatus,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'completed_at' => now(),
        ]);
    }

    protected function sendEmail(Campaign $campaign, CampaignRecipient $recipient): void
    {
        $unsubscribeUrl = null;

        // If targeting newsletter subscribers, generate unsubscribe link
        if ($campaign->audience === 'newsletter' && $recipient->email) {
            $subscriber = NewsletterSubscriber::where('email', $recipient->email)->first();
            if ($subscriber) {
                $unsubscribeUrl = $subscriber->getUnsubscribeUrl();
            }
        }

        $htmlContent = view('emails.campaign', [
            'content' => $campaign->message,
            'unsubscribeUrl' => $unsubscribeUrl,
        ])->render();

        Mail::html($htmlContent, function ($message) use ($campaign, $recipient) {
            $message->to($recipient->email)
                ->subject($campaign->subject ?? $campaign->title);
        });
    }

    protected function sendSms(Campaign $campaign, CampaignRecipient $recipient, EgoSmsService $smsService): void
    {
        if (empty($recipient->phone)) {
            throw new \Exception('No phone number available');
        }

        $result = $smsService->sendSms($recipient->phone, $campaign->message);

        if (!$smsService->isSuccess($result)) {
            throw new \Exception($result['Message'] ?? 'SMS send failed');
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->campaign->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);

        Log::error('ProcessCampaignJob failed entirely', [
            'campaign_id' => $this->campaign->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
