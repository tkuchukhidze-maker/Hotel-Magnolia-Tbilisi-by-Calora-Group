@if ($promo['active'] && ! request()->routeIs('offer'))
<div class="promo-bar" data-countdown data-ends="{{ $promo['ends_ms'] }}" data-now="{{ $promo['now_ms'] }}" data-compact>
<a class="container promo-bar__inner" href="{{ route('offer') }}">
<svg class="promo-bar__mark" width="20" height="17" stroke-width="2" aria-hidden="true"><use href="#mg-mark"/></svg>
<span><strong>−{{ $promo['discount'] }}%</strong> <span class="promo-bar__wide">{{ $promo['label'] }}: every room, </span><span>book by {{ $promo['ends_label'] }}</span></span>
<span class="promo-bar__time" aria-hidden="true" data-cd-compact></span>
<span class="promo-bar__cta">Get the offer →</span>
</a>
</div>
@endif
