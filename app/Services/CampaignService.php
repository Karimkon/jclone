<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\NewsletterSubscriber;
use App\Models\User;

class CampaignService
{
    /**
     * Build recipients for a campaign based on its audience type.
     */
    public function buildRecipientsForCampaign(Campaign $campaign): int
    {
        $campaign->recipients()->delete();

        $recipients = collect();

        switch ($campaign->audience) {
            case 'all':
                $recipients = User::where('is_active', true)->select('id', 'email', 'phone')->get();
                break;
            case 'buyers':
                $recipients = User::where('is_active', true)->where('role', 'buyer')->select('id', 'email', 'phone')->get();
                break;
            case 'vendors':
                $recipients = User::where('is_active', true)->where('role', 'vendor')->select('id', 'email', 'phone')->get();
                break;
            case 'newsletter':
                return $this->buildNewsletterRecipients($campaign);
            case 'custom':
                $query = User::where('is_active', true);
                if (!empty($campaign->filters['roles'])) {
                    $query->whereIn('role', $campaign->filters['roles']);
                }
                $recipients = $query->select('id', 'email', 'phone')->get();
                break;
        }

        $count = 0;
        foreach ($recipients->chunk(500) as $chunk) {
            $rows = [];
            foreach ($chunk as $user) {
                if ($campaign->type === 'email' && empty($user->email)) continue;
                if ($campaign->type === 'sms' && empty($user->phone)) continue;

                $rows[] = [
                    'campaign_id' => $campaign->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'channel' => $campaign->type,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($rows)) {
                CampaignRecipient::insert($rows);
                $count += count($rows);
            }
        }

        $campaign->update(['total_recipients' => $count]);
        return $count;
    }

    /**
     * Get estimated audience count for preview.
     */
    public function getAudienceCount(string $audience, string $type, array $filters = []): int
    {
        switch ($audience) {
            case 'all':
                $count = User::where('is_active', true)->count();
                break;
            case 'buyers':
                $count = User::where('is_active', true)->where('role', 'buyer')->count();
                break;
            case 'vendors':
                $count = User::where('is_active', true)->where('role', 'vendor')->count();
                break;
            case 'newsletter':
                $count = NewsletterSubscriber::active()->count();
                break;
            case 'custom':
                $query = User::where('is_active', true);
                if (!empty($filters['roles'])) {
                    $query->whereIn('role', $filters['roles']);
                }
                $count = $query->count();
                break;
            default:
                $count = 0;
        }

        // For SMS, filter out users without phone numbers
        if ($type === 'sms' && in_array($audience, ['all', 'buyers', 'vendors', 'custom'])) {
            $query = User::where('is_active', true)->whereNotNull('phone')->where('phone', '!=', '');
            if ($audience === 'buyers') $query->where('role', 'buyer');
            elseif ($audience === 'vendors') $query->where('role', 'vendor');
            elseif ($audience === 'custom' && !empty($filters['roles'])) $query->whereIn('role', $filters['roles']);
            $count = $query->count();
        }

        return $count;
    }

    private function buildNewsletterRecipients(Campaign $campaign): int
    {
        $subscribers = NewsletterSubscriber::active()->select('email')->get();
        $count = 0;

        foreach ($subscribers->chunk(500) as $chunk) {
            $rows = [];
            foreach ($chunk as $sub) {
                $rows[] = [
                    'campaign_id' => $campaign->id,
                    'user_id' => null,
                    'email' => $sub->email,
                    'phone' => null,
                    'channel' => 'email',
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($rows)) {
                CampaignRecipient::insert($rows);
                $count += count($rows);
            }
        }

        $campaign->update(['total_recipients' => $count]);
        return $count;
    }
}
