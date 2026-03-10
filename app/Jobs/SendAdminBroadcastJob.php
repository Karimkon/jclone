<?php

namespace App\Jobs;

use App\Models\AdminBroadcast;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAdminBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 600;

    public function __construct(
        protected AdminBroadcast $broadcast
    ) {}

    public function handle(PushNotificationService $pushService): void
    {
        $broadcast = $this->broadcast;

        if ($broadcast->status === 'failed') {
            return;
        }

        $broadcast->update([
            'status' => 'sending',
            'sent_at' => now(),
        ]);

        $query = $this->buildUserQuery($broadcast);
        $sentCount = 0;

        $query->chunkById(50, function ($users) use ($broadcast, $pushService, &$sentCount) {
            // Refresh to check if cancelled/failed externally
            $broadcast->refresh();
            if ($broadcast->status === 'failed') {
                return false;
            }

            foreach ($users as $user) {
                try {
                    $pushService->sendToUser(
                        $user->id,
                        'admin_message',
                        $broadcast->title,
                        $broadcast->body,
                        ['route' => $broadcast->route ?? '/notifications'],
                        $broadcast->image_url
                    );
                    $sentCount++;
                } catch (\Exception $e) {
                    Log::warning('AdminBroadcast send failed for user', [
                        'broadcast_id' => $broadcast->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $broadcast->update(['sent_count' => $sentCount]);
        });

        $broadcast->update([
            'status' => 'sent',
            'sent_count' => $sentCount,
        ]);
    }

    protected function buildUserQuery(AdminBroadcast $broadcast)
    {
        $query = User::query()->select('id');

        switch ($broadcast->audience) {
            case 'buyers':
                $query->where('role', 'buyer');
                break;
            case 'vendors':
                $query->whereIn('role', ['vendor_local', 'vendor_international']);
                break;
            case 'specific_user':
                $query->where('id', $broadcast->user_id);
                break;
            default: // 'all'
                $query->whereNotNull('id');
                break;
        }

        return $query;
    }

    public function failed(\Throwable $exception): void
    {
        $this->broadcast->update(['status' => 'failed']);

        Log::error('SendAdminBroadcastJob failed entirely', [
            'broadcast_id' => $this->broadcast->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
