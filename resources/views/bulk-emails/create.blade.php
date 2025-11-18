<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Create Bulk Email Campaign</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, sans-serif; margin: 20px; }
        .container { max-width: 960px; margin: 0 auto; }
        .form-group { margin-bottom: 1rem; }
        label { font-weight: 600; display: block; margin-bottom: .25rem; }
        input[type="text"], input[type="email"], textarea {
            width: 100%; padding: .5rem; border: 1px solid #ccc; border-radius: 4px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #eee; padding: .5rem; text-align: left; }
        th { background: #f5f5f5; }
        .btn {
            display: inline-block;
            padding: .5rem 1rem;
            border-radius: 4px;
            border: none;
            background: #2563eb;
            color: white;
            cursor: pointer;
        }
        .btn:disabled { background: #9ca3af; cursor: not-allowed; }
        .error { color: #b91c1c; margin-bottom: .5rem; }
    </style>
</head>
<body>
<div class="container">
    <h1>Bulk Email – Compose</h1>

    @if ($errors->any())
        <div class="error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('bulk-emails.store') }}" id="bulkEmailForm">
        @csrf

        <div class="form-group">
            <label for="from_email">From Email (optional)</label>
            <input type="email" name="from_email" id="from_email" value="{{ old('from_email') }}">
        </div>

        <div class="form-group">
            <label for="from_name">From Name (optional)</label>
            <input type="text" name="from_name" id="from_name" value="{{ old('from_name') }}">
        </div>

        <div class="form-group">
            <label for="subject">Subject *</label>
            <input type="text" name="subject" id="subject" required
                   value="{{ old('subject') }}">
        </div>

        <div class="form-group">
            <label for="body">Body *</label>
            <textarea name="body" id="body" rows="6" required>{{ old('body') }}</textarea>
        </div>

        <div class="form-group">
            <label>Recipients</label>
            <p style="margin: 0 0 .5rem;">
                If you select none, the campaign will be sent to <strong>all users</strong>.
            </p>

            <label style="display:inline-flex; align-items:center; gap: .25rem;">
                <input type="checkbox" id="selectAll">
                <span>Select all shown ({{ $users->count() }})</span>
            </label>

            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            <input type="checkbox"
                                   name="user_ids[]"
                                   value="{{ $user->id }}"
                                   class="recipient-checkbox">
                        </td>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <p id="selectedCount" style="margin-top:.5rem; font-size: .9rem; color: #4b5563;">
                Selected: 0 users
            </p>
        </div>

        <button type="submit" class="btn">Create & Queue Campaign</button>
    </form>
</div>

<script>
// Simple JS: select all + selected count
const selectAllCheckbox = document.getElementById('selectAll');
const checkboxes = Array.from(document.querySelectorAll('.recipient-checkbox'));
const selectedCountLabel = document.getElementById('selectedCount');

function updateSelectedCount() {
    const selected = checkboxes.filter(cb => cb.checked).length;
    selectedCountLabel.textContent = 'Selected: ' + selected + ' users';
}

if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function () {
        checkboxes.forEach(cb => { cb.checked = selectAllCheckbox.checked; });
        updateSelectedCount();
    });
}

checkboxes.forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
});

updateSelectedCount();
</script>
</body>
</html>