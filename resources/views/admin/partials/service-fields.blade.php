<input type="hidden" name="kind" value="{{ $kind }}">
<div class="a-row">
<label class="a-field" style="flex:2 1 220px">Name<input name="name" maxlength="90" required value="{{ $s->name ?? '' }}" placeholder="{{ $kind === 'extra' ? 'Airport transfer' : 'Free parking' }}"></label>
<label class="a-field">Icon<select name="icon">@foreach ($icons as $k => $l)<option value="{{ $k }}" @selected(($s->icon ?? ($kind === 'extra' ? 'car' : 'leaf')) === $k)>{{ $l }}</option>@endforeach</select></label>
</div>
<div class="a-row" style="margin-top:8px">
<label class="a-field" style="flex:1 1 100%">Short description<input name="description" maxlength="255" value="{{ $s->description ?? '' }}" placeholder="{{ $kind === 'extra' ? 'We meet you at arrivals with your name on a sign.' : 'Rare in the old town. Free for every guest.' }}"></label>
</div>
@if ($kind === 'extra')
<div class="a-row" style="margin-top:8px">
<label class="a-field">Price · GEL<input type="number" name="price" min="0" max="100000" required value="{{ $s->price ?? '' }}"></label>
<label class="a-field" style="flex:1 1 200px">Charged<select name="price_unit">@foreach ($units as $k => $l)<option value="{{ $k }}" @selected(($s->price_unit ?? 'stay') === $k)>{{ $l }}</option>@endforeach</select></label>
</div>
@endif
<div style="margin-top:8px"><label class="a-check"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($s->is_active ?? true)> Show on the site</label></div>
