<x-mail::message>
# Munkalap

Tisztelt {{ $product->owner_name ?? 'Ügyfél' }}!

Az alábbiakban talál egy munkalapot a következő munkavégzésről:

**Termék:** {{ $product->tool->name ?? 'N/A' }}<br>
**Gyári szám:** {{ $product->serial_number }}<br>
**Munka típusa:** @if($productLog->what === 'commissioning')
    Beüzemelés
@elseif($productLog->what === 'maintenance')
    Karbantartás
@else
    Javítás
@endif<br>
**Dátum:** {{ $productLog->when->format('Y. m. d. H:i') }}

A munkalap részletei a mellékelt PDF dokumentumban találhatók.

Köszönjük a bizalmát!

Üdvözlettel,<br>
{{ config('app.name') }}
</x-mail::message>
