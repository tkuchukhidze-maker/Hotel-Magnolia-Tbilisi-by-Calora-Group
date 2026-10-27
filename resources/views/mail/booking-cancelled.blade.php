{{-- Guest email when a booking is cancelled (guest asked, no-show, or by the hotel).
     $v = BookingMail::vars($booking), $c = BookingCancelled::copy(): label, title, lead, note_html, book_url. --}}
<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="light only">
<meta name="supported-color-schemes" content="light">
<title>{{ $c['title'] }}</title>
</head>
<body style="margin:0;padding:0;background:#F5EBDD;-webkit-text-size-adjust:100%">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent">Booking {!! $v['REF'] !!} · {!! $v['CHECKIN_DAY'] !!} → {!! $v['CHECKOUT_DAY'] !!}</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#F5EBDD">
<tr><td align="center" style="padding:28px 12px">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;background:#FFFDF9;border-radius:20px;overflow:hidden">

<tr><td align="center" style="background:#42271F;padding:30px 24px 26px">
<img src="{!! $v['MARK_URL'] !!}" width="48" height="41" alt="" style="display:block;border:0;margin:0 auto 12px">
<div style="font-family:Georgia,'Times New Roman',serif;font-size:28px;letter-spacing:6px;color:#F5EBDD">MAGNOLIA</div>
<div style="font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:4px;color:#D6BD8C;margin-top:6px">HOTEL · TBILISI</div>
</td></tr>

<tr><td style="padding:34px 32px 8px;font-family:Arial,Helvetica,sans-serif;color:#42271F">
<p style="margin:0 0 12px;font-size:12px;letter-spacing:3px;font-weight:bold;color:#A94729">{{ $c['label'] }}</p>
<h1 style="margin:0 0 14px;font-family:Georgia,'Times New Roman',serif;font-weight:normal;font-size:30px;line-height:1.2;color:#42271F">{{ $c['title'] }}</h1>
<p style="margin:0 0 12px;font-size:15px;color:#6B4A3E">Dear {!! $v['GUEST_FIRST'] !!},</p>
<p style="margin:0;font-size:16px;line-height:1.6;color:#5A3A2F">{{ $c['lead'] }}</p>
@if ($c['note_html'])
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:16px"><tr><td style="background:#F5EBDD;border-left:3px solid #B99559;border-radius:8px;padding:12px 16px;font-size:15px;line-height:1.6;color:#42271F">{!! $c['note_html'] !!}</td></tr></table>
@endif
</td></tr>

<tr><td style="padding:22px 32px 4px;font-family:Arial,Helvetica,sans-serif">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#FBF6EF;border:1px solid #E6D8C3;border-radius:14px">
<tr><td style="padding:16px 18px;font-size:14px;line-height:1.7;color:#42271F">
<div style="font-size:11px;letter-spacing:2px;font-weight:bold;color:#A94729;margin-bottom:4px">CANCELLED BOOKING</div>
<span style="color:#6B4A3E">Reference:</span> <strong style="letter-spacing:1px">{!! $v['REF'] !!}</strong><br>
<span style="color:#6B4A3E">Dates:</span> <s style="color:#6B4A3E">{!! $v['CHECKIN_DAY'] !!} → {!! $v['CHECKOUT_DAY'] !!}</s> · {!! $v['NIGHTS'] !!}<br>
<span style="color:#6B4A3E">Rooms:</span> {!! $v['ROOMS'] !!}
</td></tr>
</table>
<p style="margin:10px 0 0;font-size:13px;color:#2F6B45;font-weight:bold">Nothing was charged. You don't need to do anything.</p>
</td></tr>

<tr><td align="center" style="padding:28px 32px 6px">
<a href="{{ $c['book_url'] }}" style="display:inline-block;background:#A94729;color:#FFFFFF;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:bold;text-decoration:none;padding:13px 26px;border-radius:999px">Book new dates</a>
</td></tr>

<tr><td style="padding:22px 32px 30px;font-family:Arial,Helvetica,sans-serif;color:#5A3A2F">
<p style="margin:0 0 16px;font-size:14px;line-height:1.6">Questions, or was this a mistake? Reply to this email, call <a href="tel:{!! $v['PHONE_TEL'] !!}" style="color:#A94729;font-weight:bold;text-decoration:none">{!! $v['PHONE'] !!}</a> or <a href="{!! $v['WHATSAPP_URL'] !!}" style="color:#A94729;font-weight:bold">message us on WhatsApp</a>.</p>
<p style="margin:0;font-family:Georgia,'Times New Roman',serif;font-size:18px;color:#42271F">Warm regards,<br><em>The Magnolia team</em></p>
</td></tr>

<tr><td align="center" style="background:#EFE2CF;padding:18px 24px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#6B4A3E">
{!! $v['HOTEL'] !!} · Since 1899<br>{!! $v['ADDRESS'] !!} · <a href="{!! $v['SITE_URL'] !!}" style="color:#A94729">magnoliatbilisi.com</a>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
