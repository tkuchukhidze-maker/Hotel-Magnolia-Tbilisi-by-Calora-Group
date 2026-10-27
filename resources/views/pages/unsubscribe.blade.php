<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Email preferences · Hotel Magnolia Tbilisi</title>
<link rel="icon" href="{{ asset('assets/img/favicon.svg') }}" type="image/svg+xml">
<style>
body{margin:0;min-height:100vh;display:grid;place-items:center;background:#F5EBDD;color:#42271F;font:16px/1.6 Arial,Helvetica,sans-serif;padding:16px;box-sizing:border-box}
.card{background:#FFFDF9;border-radius:20px;max-width:460px;width:100%;padding:36px 28px;text-align:center;box-shadow:0 10px 30px rgba(66,39,31,.08)}
.brand{font-family:Georgia,'Times New Roman',serif;letter-spacing:6px;font-size:22px}
.sub{font-size:11px;letter-spacing:4px;color:#B99559;margin-top:2px}
h1{font-family:Georgia,'Times New Roman',serif;font-weight:normal;font-size:28px;margin:26px 0 10px}
p{color:#5A3A2F;margin:0 0 18px}
button,.btn{display:inline-block;border:0;cursor:pointer;background:#A94729;color:#fff;font:bold 15px Arial,Helvetica,sans-serif;padding:13px 24px;border-radius:999px;text-decoration:none}
.link{color:#A94729}
</style>
</head>
<body>
<main class="card">
<div class="brand">MAGNOLIA</div>
<div class="sub">HOTEL · TBILISI</div>
@if ($done)
<h1>You're unsubscribed</h1>
<p>We won't send offers to <strong>{{ $email }}</strong> any more. You'll still get emails about your own bookings.</p>
<a class="btn" href="{{ route('home') }}">Visit our website</a>
@else
<h1>Stop offer emails?</h1>
<p>We'll stop sending offers to <strong>{{ $email }}</strong>. Emails about your own bookings will still arrive.</p>
<form method="post" action="{{ route('unsubscribe.store', $token) }}">@csrf<button type="submit">Unsubscribe</button></form>
<p style="margin-top:18px;font-size:14px"><a class="link" href="{{ route('home') }}">Keep getting offers</a></p>
@endif
</main>
</body>
</html>
