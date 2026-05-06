<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Registration Approved' }}</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                    {{-- Banner --}}
                    @if($approvalType->email_banner)
                    <tr>
                        <td style="background-color:#16a34a;padding:40px 30px;text-align:center;">
                            <img src="{{ $approvalType->email_banner }}" alt="Banner" style="max-width:100%;height:auto;">
                        </td>
                    </tr>
                    @else
                    <tr>
                        <td style="background-color:#16a34a;padding:40px 30px;text-align:center;">
                            <h1 style="color:#ffffff;margin:0;font-size:24px;">Registration Approved ✓</h1>
                        </td>
                    </tr>
                    @endif

                    {{-- Body --}}
                    <tr>
                        <td style="padding:40px 30px;">
                            <p style="margin-top:0;font-size:16px;color:#374151;white-space:pre-wrap;line-height:1.6;">{!! nl2br(e($bodyHtml)) !!}</p>

                            <hr style="border:none;border-top:1px solid #e5e7eb;margin:30px 0;">

                            {{-- Event Details --}}
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:8px 0;font-size:14px;color:#6b7280;">
                                        <strong style="color:#374151;">Event:</strong> {{ $registration->event->title ?? 'N/A' }}
                                    </td>
                                </tr>
                                @if($registration->event->start_date)
                                <tr>
                                    <td style="padding:8px 0;font-size:14px;color:#6b7280;">
                                        <strong style="color:#374151;">Date:</strong> {{ $registration->event->start_date->format('d M Y, H:i') }}
                                    </td>
                                </tr>
                                @endif
                                @if($registration->event->location)
                                <tr>
                                    <td style="padding:8px 0;font-size:14px;color:#6b7280;">
                                        <strong style="color:#374151;">Location:</strong> {{ $registration->event->location }}
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>
                </table>

                {{-- Footer --}}
                <table width="600" cellpadding="0" cellspacing="0" style="margin-top:20px;">
                    <tr>
                        <td style="padding:10px;text-align:center;font-size:12px;color:#9ca3af;">
                            &copy; {{ date('Y') }} {{ config('app.name', 'Event Team') }}. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
