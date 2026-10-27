{{-- Guest booking email. $v = App\Support\BookingMail::vars() (real values) or ::tags() (Mailchimp merge tags).
     Keep in sync with the Mailchimp Transactional template "magnolia-booking-guest" (built from this file). --}}
<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="light only">
<meta name="supported-color-schemes" content="light">
<title>Your room is booked</title>
</head>
<body style="margin:0;padding:0;background:#F5EBDD;-webkit-text-size-adjust:100%">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent">{!! $v['ROOMS'] !!} · {!! $v['CHECKIN_DAY'] !!} → {!! $v['CHECKOUT_DAY'] !!} · {!! $v['GUESTS'] !!}. See you in Tbilisi.</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#F5EBDD">
<tr><td align="center" style="padding:28px 12px">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;background:#FFFDF9;border-radius:20px;overflow:hidden">

<tr><td align="center" style="background:#42271F;padding:34px 24px 30px">
<img src="{!! $v['MARK_URL'] !!}" width="48" height="41" alt="" style="display:block;border:0;margin:0 auto 12px">
<div style="font-family:Georgia,'Times New Roman',serif;font-size:30px;letter-spacing:6px;color:#F5EBDD">MAGNOLIA</div>
<div style="font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:4px;color:#D6BD8C;margin-top:6px">HOTEL · TBILISI</div>
<div style="font-family:Georgia,'Times New Roman',serif;font-style:italic;font-size:13px;color:#E8DCCB;margin-top:4px">by Calora Group</div>
</td></tr>

<tr><td style="padding:36px 32px 8px;font-family:Arial,Helvetica,sans-serif;color:#42271F">
<p style="margin:0 0 12px;font-size:12px;letter-spacing:3px;font-weight:bold;color:#A94729">YOUR ROOM IS BOOKED</p>
<h1 style="margin:0 0 14px;font-family:Georgia,'Times New Roman',serif;font-weight:normal;font-size:34px;line-height:1.15;color:#42271F">Congratulations, {!! $v['GUEST_FIRST'] !!}!<br><em style="color:#A94729">We're expecting you.</em></h1>
<p style="margin:0;font-size:16px;line-height:1.6;color:#5A3A2F">You've booked {!! $v['ROOMS'] !!} at Hotel Magnolia Tbilisi for {!! $v['NIGHTS'] !!}. Here's everything you need in one place.</p>
</td></tr>

<tr><td style="padding:24px 32px 6px">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
<td width="48%" valign="top" style="background:#F5EBDD;border-radius:16px;padding:18px 18px 16px;font-family:Arial,Helvetica,sans-serif">
<div style="font-size:11px;letter-spacing:2px;font-weight:bold;color:#A94729">CHECK-IN</div>
<div style="font-family:Georgia,'Times New Roman',serif;font-size:30px;line-height:1.1;color:#42271F;margin-top:6px">{!! $v['CHECKIN_DAY'] !!}</div>
<div style="font-size:13px;color:#6B4A3E;margin-top:4px">{!! $v['CHECKIN_SUB'] !!}</div>
</td>
<td width="4%" style="font-size:0;line-height:0">&nbsp;</td>
<td width="48%" valign="top" style="background:#F5EBDD;border-radius:16px;padding:18px 18px 16px;font-family:Arial,Helvetica,sans-serif">
<div style="font-size:11px;letter-spacing:2px;font-weight:bold;color:#A94729">CHECK-OUT</div>
<div style="font-family:Georgia,'Times New Roman',serif;font-size:30px;line-height:1.1;color:#42271F;margin-top:6px">{!! $v['CHECKOUT_DAY'] !!}</div>
<div style="font-size:13px;color:#6B4A3E;margin-top:4px">{!! $v['CHECKOUT_SUB'] !!}</div>
</td>
</tr>
</table>
<p style="margin:12px 0 0;text-align:center;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#6B4A3E"><strong style="color:#42271F">{!! $v['NIGHTS'] !!}</strong> &nbsp;·&nbsp; {!! $v['GUESTS'] !!}{!! $v['CHILD_AGES'] !!}</p>
<p style="margin:14px 0 0;text-align:center"><a href="{!! $v['CALENDAR_URL'] !!}" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:bold;color:#A94729;text-decoration:underline">+ Add to my calendar</a></p>
</td></tr>

<tr><td style="padding:26px 32px 4px;font-family:Arial,Helvetica,sans-serif">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-top:1px solid #E6D8C3">
<tr><td style="padding:12px 0;font-size:13px;color:#6B4A3E">Booking reference</td><td align="right" style="padding:12px 0;font-size:15px;font-weight:bold;color:#42271F;letter-spacing:1px">{!! $v['REF'] !!}</td></tr>
{!! $v['LINES_HTML'] !!}
<tr><td style="padding:10px 0;border-top:1px solid #E6D8C3;font-size:13px;color:#6B4A3E">Rate</td><td align="right" style="padding:10px 0;border-top:1px solid #E6D8C3;font-size:13px;color:#6B4A3E">{!! $v['RATE'] !!} · taxes included</td></tr>
<tr><td style="padding:14px 0;border-top:2px solid #42271F;font-size:16px;font-weight:bold;color:#42271F">Total</td><td align="right" style="padding:14px 0;border-top:2px solid #42271F;font-family:Georgia,'Times New Roman',serif;font-size:28px;color:#42271F;white-space:nowrap">{!! $v['TOTAL'] !!}</td></tr>
</table>
<p style="margin:4px 0 0;font-size:13px;line-height:1.5;color:#2F6B45;font-weight:bold">Nothing to pay now. You pay at the hotel.</p>
</td></tr>

<tr><td style="padding:26px 32px 4px;font-family:Arial,Helvetica,sans-serif">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#42271F;border-radius:16px">
<tr><td style="padding:22px 22px 20px">
<div style="font-size:11px;letter-spacing:2px;font-weight:bold;color:#D6BD8C">WHERE TO FIND US</div>
<div style="font-family:Georgia,'Times New Roman',serif;font-size:22px;line-height:1.3;color:#F5EBDD;margin-top:6px">{!! $v['ADDRESS'] !!}</div>
<div style="font-size:13px;line-height:1.6;color:#E8DCCB;margin-top:6px">In the Old Town, 950 m from Freedom Square. Free parking for guests.</div>
<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:16px"><tr>
<td style="padding-right:8px"><a href="{!! $v['DIRECTIONS_URL'] !!}" style="display:inline-block;background:#A94729;color:#FFFFFF;font-size:14px;font-weight:bold;text-decoration:none;padding:11px 18px;border-radius:999px">Get directions</a></td>
<td><a href="{!! $v['WHATSAPP_URL'] !!}" style="display:inline-block;background:#F5EBDD;color:#42271F;font-size:14px;font-weight:bold;text-decoration:none;padding:11px 18px;border-radius:999px">WhatsApp us</a></td>
</tr></table>
<div style="font-size:13px;color:#E8DCCB;margin-top:14px">Front desk, 24 hours: <a href="tel:{!! $v['PHONE_TEL'] !!}" style="color:#F5EBDD;font-weight:bold;text-decoration:none">{!! $v['PHONE'] !!}</a></div>
</td></tr>
</table>
</td></tr>

<tr><td style="padding:26px 32px 32px;font-family:Arial,Helvetica,sans-serif;color:#5A3A2F">
<p style="margin:0 0 12px;font-size:15px;line-height:1.65">A 127-year-old magnolia grows in our courtyard. Take your morning coffee down and sit under it for a while.</p>
<p style="margin:0 0 18px;font-size:15px;line-height:1.65">Arriving late or need to change something? Just reply to this email or message us on WhatsApp.</p>
<p style="margin:0;font-family:Georgia,'Times New Roman',serif;font-size:18px;color:#42271F">See you in Tbilisi,<br><em>The Magnolia team</em></p>
</td></tr>

<tr><td align="center" style="background:#EFE2CF;padding:18px 24px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#6B4A3E">
{!! $v['HOTEL'] !!} · Since 1899<br>{!! $v['ADDRESS'] !!} · <a href="{!! $v['SITE_URL'] !!}" style="color:#A94729">magnoliatbilisi.com</a>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
