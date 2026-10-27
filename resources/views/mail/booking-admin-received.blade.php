{{-- Hotel (manager) email for a new booking. $v = BookingMail::vars() or ::tags().
     Keep in sync with the Mailchimp Transactional template "magnolia-booking-admin". --}}
<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="light only">
<title>New booking</title>
</head>
<body style="margin:0;padding:0;background:#EFE2CF;-webkit-text-size-adjust:100%">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent">{!! $v['GUEST_NAME'] !!} · {!! $v['CHECKIN_DAY'] !!} → {!! $v['CHECKOUT_DAY'] !!} · {!! $v['ROOMS'] !!} · {!! $v['TOTAL'] !!}</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#EFE2CF">
<tr><td align="center" style="padding:24px 12px">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;background:#FFFDF9;border-radius:18px;overflow:hidden">

<tr><td style="background:#42271F;padding:20px 26px">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"><tr>
<td style="font-family:Georgia,'Times New Roman',serif;font-size:20px;letter-spacing:4px;color:#F5EBDD">MAGNOLIA <span style="font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:2px;color:#D6BD8C">· MANAGEMENT</span></td>
<td align="right"><span style="display:inline-block;background:#A94729;color:#FFFFFF;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:bold;letter-spacing:1px;padding:6px 12px;border-radius:999px;white-space:nowrap">NEW BOOKING</span></td>
</tr></table>
</td></tr>

<tr><td style="padding:28px 26px 6px;font-family:Arial,Helvetica,sans-serif;color:#42271F">
<p style="margin:0 0 8px;font-size:13px;color:#6B4A3E">{!! $v['REF'] !!} · booked {!! $v['BOOKED_AT'] !!}</p>
<h1 style="margin:0;font-family:Georgia,'Times New Roman',serif;font-weight:normal;font-size:30px;line-height:1.2">{!! $v['GUEST_NAME'] !!} is coming<br><em style="color:#A94729">{!! $v['CHECKIN_DAY'] !!} → {!! $v['CHECKOUT_DAY'] !!}</em></h1>
</td></tr>

<tr><td style="padding:18px 26px 4px">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"><tr>
<td width="32%" valign="top" style="background:#F5EBDD;border-radius:12px;padding:14px;font-family:Arial,Helvetica,sans-serif">
<div style="font-size:10px;letter-spacing:2px;font-weight:bold;color:#A94729">ARRIVES</div>
<div style="font-size:17px;font-weight:bold;color:#42271F;margin-top:4px">{!! $v['CHECKIN_DAY'] !!}</div>
<div style="font-size:12px;color:#6B4A3E;margin-top:2px">{!! $v['ARRIVAL'] !!}</div>
</td>
<td width="2%">&nbsp;</td>
<td width="32%" valign="top" style="background:#F5EBDD;border-radius:12px;padding:14px;font-family:Arial,Helvetica,sans-serif">
<div style="font-size:10px;letter-spacing:2px;font-weight:bold;color:#A94729">LEAVES</div>
<div style="font-size:17px;font-weight:bold;color:#42271F;margin-top:4px">{!! $v['CHECKOUT_DAY'] !!}</div>
<div style="font-size:12px;color:#6B4A3E;margin-top:2px">{!! $v['NIGHTS'] !!}</div>
</td>
<td width="2%">&nbsp;</td>
<td width="32%" valign="top" style="background:#F5EBDD;border-radius:12px;padding:14px;font-family:Arial,Helvetica,sans-serif">
<div style="font-size:10px;letter-spacing:2px;font-weight:bold;color:#A94729">GUESTS</div>
<div style="font-size:17px;font-weight:bold;color:#42271F;margin-top:4px">{!! $v['GUESTS'] !!}</div>
<div style="font-size:12px;color:#6B4A3E;margin-top:2px">{!! $v['CHILD_AGES'] !!}&nbsp;</div>
</td>
</tr></table>
</td></tr>

<tr><td style="padding:20px 26px 4px;font-family:Arial,Helvetica,sans-serif">
<div style="font-size:11px;letter-spacing:2px;font-weight:bold;color:#A94729;margin-bottom:6px">ROOMS &amp; PRICE</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
{!! $v['LINES_HTML'] !!}
<tr><td style="padding:10px 0;border-top:1px solid #E6D8C3;font-size:13px;color:#6B4A3E">Rate</td><td align="right" style="padding:10px 0;border-top:1px solid #E6D8C3;font-size:13px;color:#6B4A3E">{!! $v['RATE'] !!}</td></tr>
<tr><td style="padding:12px 0;border-top:2px solid #42271F;font-size:15px;font-weight:bold;color:#42271F">Total · pay at hotel</td><td align="right" style="padding:12px 0;border-top:2px solid #42271F;font-family:Georgia,'Times New Roman',serif;font-size:24px;color:#42271F;white-space:nowrap">{!! $v['TOTAL'] !!}</td></tr>
</table>
</td></tr>

<tr><td style="padding:20px 26px 4px;font-family:Arial,Helvetica,sans-serif">
<div style="font-size:11px;letter-spacing:2px;font-weight:bold;color:#A94729;margin-bottom:6px">GUEST</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size:14px;color:#42271F">
<tr><td style="padding:7px 0;color:#6B4A3E;width:32%">Name</td><td style="padding:7px 0;font-weight:bold">{!! $v['GUEST_NAME'] !!}</td></tr>
<tr><td style="padding:7px 0;color:#6B4A3E">Phone</td><td style="padding:7px 0"><a href="tel:{!! $v['GUEST_PHONE_TEL'] !!}" style="color:#A94729;font-weight:bold">{!! $v['GUEST_PHONE'] !!}</a></td></tr>
<tr><td style="padding:7px 0;color:#6B4A3E">Email</td><td style="padding:7px 0"><a href="mailto:{!! $v['GUEST_EMAIL'] !!}" style="color:#A94729">{!! $v['GUEST_EMAIL'] !!}</a></td></tr>
<tr><td style="padding:7px 0;color:#6B4A3E">Country</td><td style="padding:7px 0">{!! $v['GUEST_COUNTRY'] !!}</td></tr>
<tr><td style="padding:7px 0;color:#6B4A3E">Address</td><td style="padding:7px 0">{!! $v['GUEST_ADDRESS'] !!}</td></tr>
<tr><td style="padding:7px 0;color:#6B4A3E;vertical-align:top">Notes</td><td style="padding:7px 0">{!! $v['NOTES'] !!}</td></tr>
</table>
</td></tr>

<tr><td style="padding:22px 26px 28px;font-family:Arial,Helvetica,sans-serif">
<a href="{!! $v['ADMIN_URL'] !!}" style="display:inline-block;background:#42271F;color:#F5EBDD;font-size:14px;font-weight:bold;text-decoration:none;padding:12px 20px;border-radius:999px">Open in Magnolia Management</a>
<p style="margin:14px 0 0;font-size:12px;color:#6B4A3E">Reply to this email to write to the guest directly.</p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
