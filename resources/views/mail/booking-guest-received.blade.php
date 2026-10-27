<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>We received your booking request</title></head>
<body style="margin:0;padding:0;background:#f7f3ed;font-family:Arial,Helvetica,sans-serif;color:#3b302a">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f7f3ed;padding:28px 12px">
<tr><td align="center">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;max-width:600px;background:#fff;border-radius:18px;overflow:hidden;border:1px solid #e7dfd6">
<tr><td style="background:#42271f;text-align:center;padding:32px 28px;color:#f5ebdd">
<div style="font-family:Georgia,serif;font-size:32px;letter-spacing:3px">MAGNOLIA</div>
<div style="font-size:12px;letter-spacing:3px;margin-top:7px;color:#d6bd8c">HOTEL · TBILISI</div>
</td></tr>
<tr><td style="padding:32px 30px">
<p style="font-size:12px;letter-spacing:2px;font-weight:700;color:#a65e42;margin:0 0 14px">BOOKING REQUEST RECEIVED</p>
<h1 style="font-family:Georgia,serif;font-weight:normal;font-size:31px;line-height:1.2;margin:0 0 18px">Thank you, {{ $booking->first_name }}.</h1>
<p style="font-size:16px;line-height:1.65;margin:0 0 18px">We've received your request to stay with us at Hotel Magnolia Tbilisi.</p>
<div style="padding:15px 18px;border-radius:10px;background:#fff2e6;border:1px solid #ecd1bd;margin-bottom:26px">
<strong style="display:block;font-size:14px;color:#7c3e21;margin-bottom:5px">This is not a confirmed reservation yet.</strong>
<span style="font-size:14px;line-height:1.55">Our team will check room availability and contact you to confirm your stay and the final terms. No payment has been taken.</span>
</div>
<h2 style="font-size:17px;margin:0 0 16px">Your request details</h2>
<table role="presentation" width="100%" cellpadding="7" cellspacing="0" style="font-size:14px;line-height:1.5;border-collapse:collapse">
<tr><td style="color:#777066;width:40%">Reference</td><td><strong>{{ $booking->reference }}</strong></td></tr>
<tr><td style="color:#777066">Check-in</td><td>{{ $booking->checkin->format('d M Y') }}</td></tr>
<tr><td style="color:#777066">Check-out</td><td>{{ $booking->checkout->format('d M Y') }}</td></tr>
<tr><td style="color:#777066">Nights</td><td>{{ $booking->nights }}</td></tr>
<tr><td style="color:#777066">Guests</td><td>{{ $booking->adults }} {{ \Illuminate\Support\Str::plural('adult', $booking->adults) }}@if($booking->children), {{ $booking->children }} {{ \Illuminate\Support\Str::plural('child', $booking->children) }}@endif</td></tr>
<tr><td style="color:#777066">Rooms</td><td>{{ $booking->roomsSummary() }}</td></tr>
<tr><td style="color:#777066">Rate plan</td><td>{{ $booking->rateName() }}</td></tr>
<tr><td style="color:#777066">Quoted stay total</td><td style="font-size:20px;font-weight:bold">GEL {{ number_format($booking->total) }}</td></tr>
</table>
<p style="font-size:13px;color:#70675e;line-height:1.6;margin:20px 0 25px">The amount above is the quote for your submitted room selection, not a payment receipt. The hotel will confirm availability, any additional charges, applicable cancellation rules and the final price.</p>
<p style="font-size:15px;line-height:1.7;margin:0 0 16px">Need to make a change or ask a question? Reply to this email or contact our team at <a href="mailto:{{ config('hotel.contact.email') }}" style="color:#9b5439">{{ config('hotel.contact.email') }}</a>.</p>
<p style="font-size:15px;line-height:1.6;margin:0">Warm regards,<br><strong>The Hotel Magnolia Tbilisi team</strong></p>
</td></tr>
<tr><td style="background:#f4ede3;text-align:center;padding:20px 25px;font-size:12px;color:#756a5d;line-height:1.6">
Hotel Magnolia Tbilisi by Calora Group<br>{{ config('hotel.contact.address') }}, Tbilisi, Georgia
</td></tr>
</table>
</td></tr></table>
</body></html>
