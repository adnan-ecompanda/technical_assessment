<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkEmailRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulk_email_campaign_id',
        'user_id',
        'email',
        'status',
        'attempts',
        'last_error',
        'sent_at',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(BulkEmailCampaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}