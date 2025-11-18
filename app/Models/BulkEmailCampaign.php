<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkEmailCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'body',
        'from_email',
        'from_name',
        'status',
        'total_recipients',
        'sent_count',
        'invalid_count',
    ];

    public function recipients()
    {
        return $this->hasMany(BulkEmailRecipient::class);
    }
}