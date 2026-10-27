@extends('layouts.admin')
@section('title', 'Offer recipients')

@section('content')
<div class="a-head">
<div>
<h1>{{ $campaign->subject }}</h1>
<p>Sent {{ $campaign->sent_at ? \Illuminate\Support\Carbon::parse($campaign->sent_at)->setTimezone('Asia/Tbilisi')->format('j M Y, H:i') : '—' }} · {{ $campaign->sent_count }} delivered to the mail server · {{ $campaign->failed_count }} failed</p>
</div>
<a class="a-btn a-btn--light" href="{{ route('admin.offers.index') }}">← All offers</a>
</div>
<section class="a-card">
<div class="a-scroll"><table class="a-table"><thead><tr><th>Email</th><th>Result</th></tr></thead><tbody>
@foreach ($recipients as $r)
<tr><td>{{ $r->email }}</td><td>@if ($r->sent_at)<span class="a-badge a-badge--ok">Sent</span>@else<span class="a-badge a-badge--off">Failed</span> <span class="a-help">{{ $r->error }}</span>@endif</td></tr>
@endforeach
</tbody></table></div>
</section>
@endsection
