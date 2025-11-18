<?php

namespace App\Mail;

use App\Models\BulkEmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulkEmailMailable extends Mailable
{
    use Queueable, SerializesModels;

    public BulkEmailCampaign $campaign;

    public function __construct(BulkEmailCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function build(): self
    {
        $mail = $this->subject($this->campaign->subject)
            ->view('emails.bulk-campaign')
            ->with([
                'campaign' => $this->campaign,
            ]);

        if ($this->campaign->from_email) {
            $mail->from(
                $this->campaign->from_email,
                $this->campaign->from_name ?: config('mail.from.name')
            );
        }

        return $mail;
    }
}