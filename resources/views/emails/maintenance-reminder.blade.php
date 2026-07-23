<x-mail::message>
<div>{!! nl2br(e($body)) !!}</div>

@if ($bookingUrl)
<x-mail::button :url="$bookingUrl">
Időpont foglalása
</x-mail::button>
@endif

@if ($contactPhone || $contactEmail)
---
@if ($contactPhone)
**Telefon:** {{ $contactPhone }}<br>
@endif
@if ($contactEmail)
**E-mail:** {{ $contactEmail }}
@endif
@endif

Üdvözlettel,<br>
{{ config('app.name') }}
</x-mail::message>
