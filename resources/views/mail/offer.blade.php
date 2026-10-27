{{-- Offer email to past guests. $v = App\Support\GuestOffers::vars(). --}}
<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="light only">
<meta name="supported-color-schemes" content="light">
<title>{!! $v['HEADLINE'] !!}</title>
</head>
<body style="margin:0;padding:0;background:#F5EBDD;-webkit-text-size-adjust:100%">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent">{!! $v['HEADLINE'] !!}@if ($v['UNTIL']) · {!! $v['UNTIL'] !!}@endif</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#F5EBDD">
<tr><td align="center" style="padding:28px 12px">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;background:#FFFDF9;border-radius:20px;overflow:hidden">

<tr><td align="center" style="background:#42271F;padding:34px 24px 30px">
<img src="{!! $v['MARK_URL'] !!}" width="48" height="41" alt="" style="display:block;border:0;margin:0 auto 12px">
<div style="font-family:Georgia,'Times New Roman',serif;font-size:30px;letter-spacing:6px;color:#F5EBDD">MAGNOLIA</div>
<div style="font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:4px;color:#D6BD8C;margin-top:6px">HOTEL · TBILISI</div>
<div style="font-family:Georgia,'Times New Roman',serif;font-style:italic;font-size:13px;color:#E8DCCB;margin-top:4px">by Calora Group</div>
</td></tr>

@if ($v['DISCOUNT'])
<tr><td align="center" style="background:#A94729;padding:16px 24px;font-family:Georgia,'Times New Roman',serif;font-size:26px;letter-spacing:1px;color:#FFFFFF">{!! $v['DISCOUNT'] !!}@if ($v['UNTIL'])<div style="font-family:Arial,Helvetica,sans-serif;font-size:12px;letter-spacing:2px;color:#F5EBDD;margin-top:4px">{!! strtoupper($v['UNTIL']) !!}</div>@endif</td></tr>
@endif

<tr><td style="padding:34px 32px 8px;font-family:Arial,Helvetica,sans-serif;color:#42271F">
<p style="margin:0 0 14px;font-size:15px;color:#6B4A3E">{!! $v['GREETING'] !!}</p>
<h1 style="margin:0 0 16px;font-family:Georgia,'Times New Roman',serif;font-weight:normal;font-size:32px;line-height:1.2;color:#42271F">{!! $v['HEADLINE'] !!}</h1>
<p style="margin:0;font-size:16px;line-height:1.65;color:#5A3A2F">{!! $v['BODY_HTML'] !!}</p>
</td></tr>

@if ($v['CODE'])
<tr><td align="center" style="padding:22px 32px 0">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="background:#F5EBDD;border:1px dashed #B99559;border-radius:14px"><tr><td align="center" style="padding:14px 26px;font-family:Arial,Helvetica,sans-serif">
<div style="font-size:11px;letter-spacing:2px;font-weight:bold;color:#A94729">YOUR CODE</div>
<div style="font-family:Georgia,'Times New Roman',serif;font-size:26px;letter-spacing:3px;color:#42271F;margin-top:4px">{!! $v['CODE'] !!}</div>
</td></tr></table>
</td></tr>
@endif

<tr><td align="center" style="padding:28px 32px 8px">
<a href="{!! $v['BUTTON_URL'] !!}" style="display:inline-block;background:#A94729;color:#FFFFFF;font-family:Arial,Helvetica,sans-serif;font-size:16px;font-weight:bold;text-decoration:none;padding:15px 30px;border-radius:999px">{!! $v['BUTTON_LABEL'] !!}</a>
@if ($v['UNTIL'])<p style="margin:12px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#6B4A3E">{!! $v['UNTIL'] !!}</p>@endif
</td></tr>

<tr><td style="padding:26px 32px 30px;font-family:Arial,Helvetica,sans-serif;color:#5A3A2F">
<p style="margin:0 0 16px;font-size:14px;line-height:1.6">Questions? Reply to this email, call <a href="tel:{!! $v['PHONE_TEL'] !!}" style="color:#A94729;font-weight:bold;text-decoration:none">{!! $v['PHONE'] !!}</a> or <a href="{!! $v['WHATSAPP_URL'] !!}" style="color:#A94729;font-weight:bold">message us on WhatsApp</a>.</p>
<p style="margin:0;font-family:Georgia,'Times New Roman',serif;font-size:18px;color:#42271F">See you in Tbilisi,<br><em>The Magnolia team</em></p>
</td></tr>

<tr><td align="center" style="background:#EFE2CF;padding:18px 24px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#6B4A3E">
{!! $v['HOTEL'] !!} · Since 1899<br>{!! $v['ADDRESS'] !!} · <a href="{!! $v['SITE_URL'] !!}" style="color:#A94729">magnoliatbilisi.com</a><br>
You get this email because you booked with us. <a href="{!! $v['UNSUB_URL'] !!}" style="color:#6B4A3E">Unsubscribe</a>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
