<article class="ph-card">
<div class="ph-img">@if ($s['src'])<img src="{{ $s['src'] }}" alt="" loading="lazy">@else No photo yet @endif @if ($s['custom'])<span class="a-badge a-badge--ok">Changed</span>@endif</div>
<div class="ph-body">
<strong>{{ $s['label'] }}</strong>
<form method="post" action="{{ route('admin.media.slot', $s['key']) }}" enctype="multipart/form-data" class="pc-fieldset" style="gap:8px" data-upload>
@csrf
<label class="a-field ph-file">New photo<input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
<label class="a-field">Description<input name="alt" maxlength="255" value="{{ $s['alt'] }}"></label>
<button class="a-btn a-btn--sm">Save</button>
</form>
@if ($s['custom'])
<form method="post" action="{{ route('admin.media.slot.reset', $s['key']) }}" data-confirm="Go back to the original photo?">@csrf @method('DELETE')<button class="a-btn a-btn--light a-btn--sm">Use original photo</button></form>
@endif
</div>
</article>
