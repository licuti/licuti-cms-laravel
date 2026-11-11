<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notificationSubject }}</title>
</head>
<body style="margin:0;background:#f5f5f5;color:#222;font-family:Arial,sans-serif;line-height:1.6;">
    <main style="max-width:600px;margin:24px auto;padding:24px;background:#fff;">
        <h1 style="font-size:22px;">{{ $notificationSubject }}</h1>
        <p>{{ __('Xin chào quý khách,', [], 'vi') }}</p>
        @foreach ($notificationLines as $line)
            <p>{{ $line }}</p>
        @endforeach
        <p style="color:#666;font-size:13px;">{{ __('Thời điểm sự kiện:', [], 'vi') }} {{ $occurredAt }}</p>
        <p>{{ __('Thông báo này ghi nhận sự kiện tại thời điểm trên, không phản ánh các thay đổi phát sinh sau đó.', [], 'vi') }}</p>
        <p>{{ __('Cảm ơn quý khách.', [], 'vi') }}</p>
    </main>
</body>
</html>
