<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $campaign->subject }}</title>
</head>
<body>
    <p>{!! nl2br(e($campaign->body)) !!}</p>
</body>
</html>