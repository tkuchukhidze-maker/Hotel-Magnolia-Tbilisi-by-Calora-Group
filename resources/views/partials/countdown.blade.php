{{-- Live countdown to the end of the book-direct offer.
     Rendered with the real time left (works without JS); main.js keeps it ticking
     against the server clock, so a wrong phone clock can't change it. --}}
@php
    $left = max(0, intdiv(($promo['ends_ms'] ?? 0) - ($promo['now_ms'] ?? 0), 1000));
    $parts = ['d' => intdiv($left, 86400), 'h' => intdiv($left % 86400, 3600), 'm' => intdiv($left % 3600, 60), 's' => $left % 60];
    $variant = $variant ?? 'dark';
@endphp
<div class="countdown countdown--{{ $variant }}" data-countdown data-ends="{{ $promo['ends_ms'] }}" data-now="{{ $promo['now_ms'] }}">
<p class="countdown__label">{{ $label ?? 'Offer ends in' }}</p>
<div class="countdown__units" role="timer" aria-live="off" aria-label="Offer ends {{ $promo['ends_long'] }}">
@foreach (['d' => 'days', 'h' => 'hours', 'm' => 'min', 's' => 'sec'] as $k => $unit)
<span class="countdown__unit"><b data-cd="{{ $k }}">{{ str_pad($parts[$k], 2, '0', STR_PAD_LEFT) }}</b><small>{{ $unit }}</small></span>
@endforeach
</div>
<p class="countdown__over" hidden>This offer has just ended. Updating prices…</p>
</div>
