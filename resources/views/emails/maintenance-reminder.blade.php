<x-mail::message>
{!! nl2br(e($body)) !!}

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
