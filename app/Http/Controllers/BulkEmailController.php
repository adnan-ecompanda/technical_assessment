<?php

namespace App\Http\Controllers;

use App\Jobs\SendBulkEmailBatch;
use App\Models\BulkEmailCampaign;
use App\Models\BulkEmailRecipient;
use App\Models\User;
use Illuminate\Http\Request;

class BulkEmailController extends Controller
{
    public function create()
    {
        // For demo: show first 200 users with checkboxes.
        // Backend is still designed to handle thousands.
        $users = User::orderBy('id')->limit(200)->get();

        return view('bulk-emails.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject'  => ['required', 'string', 'max:255'],
            'body'     => ['required', 'string'],
            'from_email' => ['nullable', 'email'],
            'from_name'  => ['nullable', 'string', 'max:255'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        // Get recipients: either selected users, or all users if none selected
        $usersQuery = User::query();

        if (! empty($validated['user_ids'])) {
            $usersQuery->whereIn('id', $validated['user_ids']);
        }

        $users = $usersQuery->get(['id', 'email']);

        if ($users->isEmpty()) {
            return back()->withErrors(['user_ids' => 'No users selected / found.']);
        }

        // Create campaign
        $campaign = BulkEmailCampaign::create([
            'subject'          => $validated['subject'],
            'body'             => $validated['body'],
            'from_email'       => $validated['from_email'] ?? null,
            'from_name'        => $validated['from_name'] ?? null,
            'status'           => 'pending',
            'total_recipients' => $users->count(),
        ]);

        // Attach recipients (batched insert for performance)
        $rows = [];
        foreach ($users as $user) {
            $rows[] = [
                'bulk_email_campaign_id' => $campaign->id,
                'user_id'                => $user->id,
                'email'                  => $user->email,
                'status'                 => 'pending',
                'attempts'               => 0,
                'created_at'             => now(),
                'updated_at'             => now(),
            ];
        }

        // Insert in chunks for large sets
        collect($rows)->chunk(1000)->each(function ($chunk) {
            BulkEmailRecipient::insert($chunk->toArray());
        });

        // Dispatch first batch job
        SendBulkEmailBatch::dispatch($campaign->id);

        return redirect()
            ->route('bulk-emails.show', $campaign)
            ->with('status', 'Bulk email campaign created and queued.');
    }

    public function show(BulkEmailCampaign $campaign)
    {
        $campaign->loadCount([
            'recipients as pending_count' => function ($q) {
                $q->where('status', 'pending');
            },
            'recipients as sent_recipient_count' => function ($q) {
                $q->where('status', 'sent');
            },
            'recipients as invalid_recipient_count' => function ($q) {
                $q->where('status', 'invalid');
            },
        ]);

        return view('bulk-emails.show', compact('campaign'));
    }

    public function status(BulkEmailCampaign $campaign)
    {
        // Always compute live counts from recipients, so numbers stay correct
        $campaign->loadCount([
            'recipients as pending_count' => function ($q) {
                $q->where('status', 'pending');
            },
            'recipients as sent_recipient_count' => function ($q) {
                $q->where('status', 'sent');
            },
            'recipients as invalid_recipient_count' => function ($q) {
                $q->where('status', 'invalid');
            },
        ]);

        return response()->json([
            'id'                => $campaign->id,
            'status'            => $campaign->status,
            'subject'           => $campaign->subject,
            'total_recipients'  => $campaign->total_recipients,
            'sent'              => $campaign->sent_recipient_count,
            'pending'           => $campaign->pending_count,
            'invalid'           => $campaign->invalid_recipient_count,
            'updated_at'        => $campaign->updated_at?->toDateTimeString(),
        ]);
    }

}