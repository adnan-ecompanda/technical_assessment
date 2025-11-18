@php use Illuminate\Support\Str; @endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $body ? Str::limit($body, 40) : '' }}</title>
</head>
<body>
    <p>{!! nl2br(e($body)) !!}</p>
</body>
</html>