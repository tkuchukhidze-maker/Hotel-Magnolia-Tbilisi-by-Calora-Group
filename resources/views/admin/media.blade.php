@extends('layouts.admin')
@section('title', 'Photos')

@section('content')
<div class="a-head">
<div>
<h1>Photos</h1>
<p>Change a room's photo, the big photos on each page, and the gallery on the Our Hotel page. Large photos are made smaller automatically before they're uploaded. Always add a short description: it's read aloud to blind guests and helps Google.</p>
</div>
<a class="a-btn a-btn--light" href="{{ route('our-hotel') }}#gallery" target="_blank" rel="noopener">See the gallery ↗</a>
</div>

@if ($disk === 'public' && app()->environment('production'))
<p class="a-flash a-flash--err" style="font-weight:500">Photos are saved on the server's own disk. On Laravel Cloud that disk is cleared on every deploy. Attach an Object Storage bucket to this environment and set <code>HOTEL_MEDIA_DISK</code> to its disk name, so uploaded photos stay.</p>
@endif

<section class="a-card" aria-labelledby="rooms-h">
<h2 id="rooms-h">Room photos</h2>
<p class="a-help">Shown on the Rooms page, the home page and the offer page.</p>
<div class="ph-grid">
@foreach ($roomSlots as $s)
@include('admin.partials.photo-slot', ['s' => $s])
@endforeach
</div>
</section>

<section class="a-card" aria-labelledby="pages-h">
<h2 id="pages-h">Page photos</h2>
<p class="a-help">The large photos at the top of pages and in key sections.</p>
<div class="ph-grid">
@foreach ($pageSlots as $s)
@include('admin.partials.photo-slot', ['s' => $s])
@endforeach
</div>
</section>

<section class="a-card" id="gallery" aria-labelledby="gallery-h">
<div class="a-head" style="margin-bottom:10px">
<div><h2 id="gallery-h">Gallery · Our Hotel page</h2><p class="a-help" style="margin:4px 0 0">{{ count($gallery) }} photos. Use the arrows to change the order; hidden photos stay here but don't show on the site.</p></div>
</div>
<form method="post" action="{{ route('admin.media.gallery.add') }}" enctype="multipart/form-data" data-upload>
@csrf
<label class="ph-drop" data-drop>
<strong>Drop photos here, or click to choose</strong>
<span>JPG, PNG or WebP · several at once is fine</span>
<input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple data-autosubmit>
</label>
<div class="a-row" style="margin-top:10px">
<label class="a-field" style="max-width:260px">New photos go under<select name="category">@foreach (\App\Support\Media::CATEGORIES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></label>
<p class="a-help" data-upload-status style="margin:0 0 10px"></p>
</div>
</form>

<div class="ph-grid" style="margin-top:16px">
@foreach ($gallery as $i => $p)
<article class="ph-card {{ $p->is_visible ? '' : 'is-hidden' }}">
<div class="ph-img"><img src="{{ \App\Support\Media::src($p->path) }}" alt="" loading="lazy">@if (! $p->is_visible)<span class="a-badge a-badge--off">Hidden</span>@endif</div>
<div class="ph-body">
<form method="post" action="{{ route('admin.media.gallery.update', $p->id) }}" enctype="multipart/form-data" class="pc-fieldset" style="gap:8px" data-upload>
@csrf
<label class="a-field">Description (what's in the photo)<input name="alt" maxlength="255" value="{{ $p->alt }}" placeholder="e.g. Deluxe Room with a balcony"></label>
<label class="a-field">Caption under the photo<input name="caption" maxlength="160" value="{{ $p->caption }}" placeholder="optional"></label>
<div class="pc-checks">
@foreach (\App\Support\Media::CATEGORIES as $k => $l)<label class="a-check"><input type="checkbox" name="categories[]" value="{{ $k }}" @checked(in_array($k, explode(' ', (string) $p->category), true))> {{ $l }}</label>@endforeach
</div>
<div class="pc-checks">
<label class="a-check"><input type="hidden" name="wide" value="0"><input type="checkbox" name="wide" value="1" @checked($p->wide)> Wide</label>
<label class="a-check"><input type="hidden" name="is_visible" value="0"><input type="checkbox" name="is_visible" value="1" @checked($p->is_visible)> Show on site</label>
</div>
<label class="a-field ph-file">Replace photo<input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
<button class="a-btn a-btn--sm">Save</button>
</form>
<div class="ph-inline">
<form method="post" action="{{ route('admin.media.gallery.move', $p->id) }}">@csrf<input type="hidden" name="dir" value="up"><button class="a-btn a-btn--light a-btn--sm" @disabled($i === 0) aria-label="Move earlier">←</button></form>
<form method="post" action="{{ route('admin.media.gallery.move', $p->id) }}">@csrf<input type="hidden" name="dir" value="down"><button class="a-btn a-btn--light a-btn--sm" @disabled($i === count($gallery) - 1) aria-label="Move later">→</button></form>
<form method="post" action="{{ route('admin.media.gallery.move', $p->id) }}">@csrf<input type="hidden" name="dir" value="first"><button class="a-btn a-btn--light a-btn--sm" @disabled($i === 0)>First</button></form>
<form method="post" action="{{ route('admin.media.gallery.delete', $p->id) }}" data-confirm="Remove this photo from the gallery?">@csrf @method('DELETE')<button class="a-btn a-btn--danger a-btn--sm">Delete</button></form>
</div>
</div>
</article>
@endforeach
</div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin-media.js') }}?v={{ filemtime(public_path('assets/js/admin-media.js')) }}" defer></script>
@endpush
