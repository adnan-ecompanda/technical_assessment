<?php

namespace App\Jobs;

use App\Mail\BulkEmailMailable;
use App\Models\BulkEmailCampaign;
use App\Models\BulkEmailRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBulkEmailBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $campaignId;

    public function __construct(int $campaignId)
    {
        $this->campaignId = $campaignId;
    }

    public function handle(): void
    {
        $campaign = BulkEmailCampaign::find($this->campaignId);

        if (! $campaign) {
            return;
        }

        // Mark campaign as running
        if ($campaign->status === 'pending') {
            $campaign->update(['status' => 'running']);
        }

        // Fetch up to 100 pending recipients
        $recipients = BulkEmailRecipient::query()
            ->where('bulk_email_campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->limit(100)
            ->get();

        if ($recipients->isEmpty()) {
            // No more pending recipients, mark as completed
            $campaign->update(['status' => 'completed']);
            return;
        }

        foreach ($recipients as $recipient) {
            // Basic email format validation – invalids will not be retried
            if (! filter_var($recipient->email, FILTER_VALIDATE_EMAIL)) {
                $recipient->status = 'invalid';
                $recipient->attempts++;
                $recipient->last_error = 'Invalid email format.';
                $recipient->save();

                $campaign->increment('invalid_count');
                continue;
            }

            try {
                Mail::to($recipient->email)
                    ->send(new BulkEmailMailable($campaign));

                $recipient->status = 'sent';
                $recipient->attempts++;
                $recipient->sent_at = now();
                $recipient->last_error = null;
                $recipient->save();

                $campaign->increment('sent_count');
            } catch (\Throwable $e) {
                $recipient->attempts++;
                $recipient->last_error = $e->getMessage();
                $recipient->save();
            }
        }

        // If there are still pending recipients, dispatch next batch after 1 minute
        $hasMorePending = BulkEmailRecipient::query()
            ->where('bulk_email_campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasMorePending) {
            self::dispatch($campaign->id)->delay(now()->addMinute());
        } else {
            $campaign->update(['status' => 'completed']);
        }
    }
}