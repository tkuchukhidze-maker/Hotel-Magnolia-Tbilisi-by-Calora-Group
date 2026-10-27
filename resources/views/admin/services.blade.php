@extends('layouts.admin')
@section('title', 'Services')

@php
$form = function ($s, string $kind) use ($units, $icons) {
    return view('admin.partials.service-fields', ['s' => $s, 'kind' => $kind, 'units' => $units, 'icons' => $icons])->render();
};
@endphp

@section('content')
<div class="a-head">
<div>
<h1>Services</h1>
<p><strong>Included</strong> services are shown on the Our Hotel page. <strong>Paid extras</strong> can be ticked by guests when they book; the price is added to their booking and to the email you receive.</p>
</div>
<a class="a-btn a-btn--light" href="{{ route('our-hotel') }}" target="_blank" rel="noopener">See on the site ↗</a>
</div>

@foreach (['amenity' => ['Included in every stay', $amenities, 'amenities'], 'extra' => ['Paid extras', $extras, 'extras']] as $kind => [$title, $list, $anchor])
<section class="a-card" id="{{ $anchor }}" style="margin-bottom:16px">
<h2>{{ $title }}</h2>
<p class="a-help">{{ $kind === 'extra' ? 'E.g. airport transfer, late check-out, a baby cot, a city tour. Hidden ones are not offered.' : 'E.g. free parking, Wi‑Fi, a 24-hour front desk.' }}</p>
<div class="sv-list">
@forelse ($list as $i => $s)
<div class="sv-item {{ $s->is_active ? '' : 'is-off' }}">
<span class="sv-icon">{!! \App\Support\Services::icon($s->icon) !!}</span>
<div>
<strong>{{ $s->name }}</strong>
<div class="sv-meta">
@if ($kind === 'extra')<span class="a-badge">GEL {{ $s->price }} {{ $units[$s->price_unit] ?? '' }}</span>@endif
<span class="a-badge {{ $s->is_active ? 'a-badge--ok' : 'a-badge--off' }}">{{ $s->is_active ? 'Shown' : 'Hidden' }}</span>
</div>
@if ($s->description)<p class="a-help" style="margin:6px 0 0">{{ $s->description }}</p>@endif
<details>
<summary>Edit</summary>
<form method="post" action="{{ route('admin.services.update', $s->id) }}">@csrf @method('PUT')
{!! $form($s, $kind) !!}
<div class="a-row" style="margin-top:10px"><button class="a-btn a-btn--sm">Save</button></div>
</form>
</details>
</div>
<div class="sv-order">
<form method="post" action="{{ route('admin.services.move', $s->id) }}">@csrf @method('PATCH')<input type="hidden" name="dir" value="up"><button class="a-btn a-btn--light a-btn--sm" @disabled($i === 0) aria-label="Move up">↑</button></form>
<form method="post" action="{{ route('admin.services.move', $s->id) }}">@csrf @method('PATCH')<input type="hidden" name="dir" value="down"><button class="a-btn a-btn--light a-btn--sm" @disabled($i === count($list) - 1) aria-label="Move down">↓</button></form>
<form method="post" action="{{ route('admin.services.toggle', $s->id) }}">@csrf @method('PATCH')<button class="a-btn a-btn--light a-btn--sm">{{ $s->is_active ? 'Hide' : 'Show' }}</button></form>
<form method="post" action="{{ route('admin.services.destroy', $s->id) }}" onsubmit="return confirm('Delete “{{ addslashes($s->name) }}”?')">@csrf @method('DELETE')<button class="a-btn a-btn--danger a-btn--sm">Delete</button></form>
</div>
</div>
@empty
<p class="a-help">Nothing here yet.</p>
@endforelse
</div>

<details style="margin-top:14px" @if ($list->isEmpty() || old('kind') === $kind) open @endif>
<summary class="a-btn a-btn--dark" style="display:inline-flex;list-style:none">+ Add {{ $kind === 'extra' ? 'a paid extra' : 'an included service' }}</summary>
<form method="post" action="{{ route('admin.services.store') }}" class="a-card" style="margin-top:10px;background:#FBF6EF">@csrf
{!! $form(null, $kind) !!}
<div class="a-row" style="margin-top:10px"><button class="a-btn">Add</button></div>
</form>
</details>
</section>
@endforeach
@endsection
