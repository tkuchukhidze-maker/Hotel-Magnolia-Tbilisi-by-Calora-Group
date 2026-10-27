@extends('layouts.admin')
@section('title', 'Offers')

@push('head')
<style>
.of-layout{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:16px;align-items:start}
.of-layout .a-field{margin-top:12px}
.of-aud{display:grid;gap:8px;margin-top:8px}
.of-aud label{display:flex;gap:10px;align-items:center;border:1px solid var(--line);border-radius:12px;padding:10px 12px;cursor:pointer;font-size:14px;font-weight:600}
.of-aud label:has(input:checked){border-color:var(--terracotta);background:#FBF1EC}
.of-aud input{accent-color:var(--terracotta);width:18px;height:18px}
.of-aud small{margin-left:auto;color:var(--muted);font-weight:700}
.of-steps{display:grid;gap:14px}
.of-step{border-top:1px solid var(--line);padding-top:14px}
.of-step:first-child{border-top:0;padding-top:0}
.of-step h3{margin:0 0 4px;font-size:15px}
@media(max-width:900px){.of-layout{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="a-head">
<div>
<h1>Offer emails</h1>
<p>Send an offer to people who have booked with you, in the Magnolia email design. Everyone gets an unsubscribe link, and people who unsubscribed are skipped automatically.</p>
</div>
</div>

<form method="post" action="{{ route('admin.offers.send') }}" class="of-layout" id="offer-form">
@csrf
<section class="a-card">
<h2>1. Write the offer</h2>
<label class="a-field">Email subject<input name="subject" maxlength="150" required value="{{ $form['subject'] }}"></label>
<label class="a-field">Headline<input name="headline" maxlength="150" required value="{{ $form['headline'] }}"></label>
<label class="a-field">Text<textarea name="body" rows="6" maxlength="3000" required>{{ $form['body'] }}</textarea></label>
<div class="a-row">
<label class="a-field">Discount % <span class="a-help" style="margin:0">optional</span><input type="number" name="discount" min="1" max="90" value="{{ $form['discount'] }}"></label>
<label class="a-field">Promo code <span class="a-help" style="margin:0">optional</span><input name="code" maxlength="40" value="{{ $form['code'] }}" placeholder="MAGNOLIA25"></label>
<label class="a-field">Book by <span class="a-help" style="margin:0">optional</span><input type="date" name="valid_until" value="{{ $form['valid_until'] }}"></label>
</div>
<div class="a-row">
<label class="a-field">Button text<input name="button_label" maxlength="60" required value="{{ $form['button_label'] }}"></label>
<label class="a-field" style="flex:2 1 260px">Button link<input type="url" name="button_url" maxlength="500" required value="{{ $form['button_url'] }}"></label>
</div>
<p class="a-help" style="margin-top:12px">A discount or code here is only shown in the email. Make sure the price on the site matches (Prices → calendar, or the offer page).</p>
<div class="a-row" style="margin-top:6px">
<button class="a-btn a-btn--light" type="submit" formaction="{{ route('admin.offers.preview') }}" formtarget="_blank" formnovalidate>Preview the email ↗</button>
</div>
</section>

<section class="a-card of-steps">
<div class="of-step">
<h2>2. Who gets it</h2>
<div class="of-aud">
@foreach (\App\Support\GuestOffers::AUDIENCES as $key => $label)
<label><input type="radio" name="audience" value="{{ $key }}" @checked($form['audience'] === $key)> {{ $label }} @if ($key !== 'none')<small>{{ $counts[$key] }}</small>@endif</label>
@endforeach
</div>
<label class="a-field">Also send to <span class="a-help" style="margin:0">one email per line · only people who agreed to hear from you</span><textarea name="extra" rows="3" placeholder="name@example.com">{{ $form['extra'] }}</textarea></label>
<p class="a-help">{{ $unsubscribed }} {{ $unsubscribed === 1 ? 'person has' : 'people have' }} unsubscribed and will be skipped.</p>
</div>

<div class="of-step">
<h3>3. Send yourself a test</h3>
<div class="a-row">
<label class="a-field" style="flex:2 1 200px">Your email<input type="email" name="test_to" value="{{ old('test_to', config('hotel.bookings_email')) }}"></label>
<button class="a-btn a-btn--light" type="submit" formaction="{{ route('admin.offers.test') }}">Send test</button>
</div>
</div>

<div class="of-step">
<h3>4. Send to guests</h3>
<label class="a-check"><input type="checkbox" name="confirm" value="1"> I checked the test email. Send it now.</label>
<div class="a-row" style="margin-top:10px"><button class="a-btn" type="submit">Send the offer</button></div>
<p class="a-help">Each guest gets their own email, addressed by first name. Sending can take a minute for long lists, so keep this page open.</p>
</div>
</section>
</form>

<section class="a-card" style="margin-top:16px">
<h2>Sent offers</h2>
<div class="a-scroll"><table class="a-table"><thead><tr><th>Sent</th><th>Subject</th><th>To</th><th>Delivered</th><th>Failed</th><th></th></tr></thead><tbody>
@forelse ($campaigns as $c)
<tr><td>{{ $c->sent_at ? \Illuminate\Support\Carbon::parse($c->sent_at)->setTimezone('Asia/Tbilisi')->format('j M Y, H:i') : 'sending…' }}</td><td>{{ $c->subject }}</td><td>{{ \App\Support\GuestOffers::AUDIENCES[$c->audience] ?? $c->audience }}</td><td>{{ $c->sent_count }}</td><td>{{ $c->failed_count }}</td><td><a href="{{ route('admin.offers.show', $c->id) }}">Recipients</a></td></tr>
@empty
<tr><td colspan="6">No offers sent yet.</td></tr>
@endforelse
</tbody></table></div>
<p class="a-help">Opens and clicks are in Mailchimp Transactional → Outbound → Activity (tag “guest-offer”).</p>
</section>
@endsection
