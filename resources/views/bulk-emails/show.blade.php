<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Campaign #{{ $campaign->id }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, sans-serif; margin: 20px; }
        .container { max-width: 720px; margin: 0 auto; }
        .stat { margin-bottom: .5rem; }
        .badge {
            display: inline-block;
            padding: .25rem .5rem;
            border-radius: 999px;
            font-size:.8rem;
            background:#e5e7eb;
        }
        .badge-running { background: #facc15; }
        .badge-completed { background: #22c55e; color: #fff; }
        .badge-pending { background: #9ca3af; color:#fff; }
        .muted { color:#6b7280; font-size:.85rem; }
    </style>
</head>
<body>
<div class="container" data-campaign-id="{{ $campaign->id }}">
    <h1>Campaign #{{ $campaign->id }}</h1>

    @if(session('status'))
        <p style="color:#15803d;">{{ session('status') }}</p>
    @endif

    <p class="stat">
        <strong>Subject:</strong>
        <span id="stat-subject">{{ $campaign->subject }}</span>
    </p>

    <p class="stat">
        <strong>Status:</strong>
        <span id="stat-status-badge"
              class="badge badge-{{ $campaign->status }}">
            {{ strtoupper($campaign->status) }}
        </span>
    </p>

    <p class="stat">
        <strong>Total recipients:</strong>
        <span id="stat-total">{{ $campaign->total_recipients }}</span>
    </p>

    <p class="stat">
        <strong>Sent:</strong>
        <span id="stat-sent">{{ $campaign->sent_recipient_count }}</span>
    </p>

    <p class="stat">
        <strong>Pending:</strong>
        <span id="stat-pending">{{ $campaign->pending_count }}</span>
    </p>

    <p class="stat">
        <strong>Invalid:</strong>
        <span id="stat-invalid">{{ $campaign->invalid_count }}</span>
    </p>

    <p class="muted" id="stat-last-updated">
        Last updated: just now
    </p>

    <p style="margin-top:1.5rem;">
        <a href="{{ route('bulk-emails.create') }}">← Back to compose</a>
    </p>
</div>

<script>
(function () {
    // Config
    const POLL_INTERVAL_MS = 4000; // 4 seconds
    const statusUrl = "{{ route('bulk-emails.status', $campaign) }}";

    const statusBadge = document.getElementById('stat-status-badge');
    const totalEl = document.getElementById('stat-total');
    const sentEl = document.getElementById('stat-sent');
    const pendingEl = document.getElementById('stat-pending');
    const invalidEl = document.getElementById('stat-invalid');
    const lastUpdatedEl = document.getElementById('stat-last-updated');

    let pollTimer = null;

    function applyStatusBadge(status) {
        const normalized = status.toLowerCase();

        statusBadge.className = 'badge'; // reset
        statusBadge.textContent = status.toUpperCase();

        if (normalized === 'running') {
            statusBadge.classList.add('badge-running');
        } else if (normalized === 'completed') {
            statusBadge.classList.add('badge-completed');
        } else if (normalized === 'pending') {
            statusBadge.classList.add('badge-pending');
        }
    }

    async function fetchStatus() {
        try {
            const response = await fetch(statusUrl, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                console.error('Status request failed:', response.status);
                return;
            }

            const data = await response.json();

            // Update DOM
            totalEl.textContent = data.total_recipients;
            sentEl.textContent = data.sent;
            pendingEl.textContent = data.pending;
            invalidEl.textContent = data.invalid;
            applyStatusBadge(data.status);

            const now = new Date();
            lastUpdatedEl.textContent = 'Last updated: ' + now.toLocaleTimeString();

            const isDone = data.status === 'completed' || (data.pending === 0 && data.sent >= data.total_recipients);
            if (isDone && pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
        } catch (e) {
            console.error('Error fetching status:', e);
            // We don't stop polling on transient errors.
        }
    }

    // Initial fetch immediately
    fetchStatus();

    // Start polling
    pollTimer = setInterval(fetchStatus, POLL_INTERVAL_MS);
})();
</script>
</body>
</html>