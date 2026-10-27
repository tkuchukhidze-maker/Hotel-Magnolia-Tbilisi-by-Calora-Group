Hotel Magnolia Tbilisi by Calora Group

{!! html_entity_decode($v['GREETING']) !!}

{!! html_entity_decode($v['HEADLINE']) !!}
@if ($v['DISCOUNT'])
{!! html_entity_decode($v['DISCOUNT']) !!}
@endif

{!! html_entity_decode(strip_tags($v['BODY_HTML'])) !!}

@if ($v['CODE'])
Your code: {!! html_entity_decode($v['CODE']) !!}
@endif
{!! html_entity_decode($v['BUTTON_LABEL']) !!}: {!! html_entity_decode($v['BUTTON_URL']) !!}
@if ($v['UNTIL'])
{!! html_entity_decode($v['UNTIL']) !!}
@endif

Questions? Reply to this email or call {!! html_entity_decode($v['PHONE']) !!}.

See you in Tbilisi,
The Magnolia team

{!! html_entity_decode($v['ADDRESS']) !!}
Unsubscribe: {!! html_entity_decode($v['UNSUB_URL']) !!}
