@props(['value' => '', 'selected' => false])

<option {{ $selected ? 'selected' : '' }} value='{{ $value }}'>{{ $slot }}</option>
