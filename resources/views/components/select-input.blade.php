@props(['name' => '', 'headText' => ''])
<div class="mb-sm-0 mb-3 w-full">
    <label for="{{ $name }}"> <span style="color:red;">*</span>{{ __($headText) }}</label>
    <select name="{{ $name }}" id="{{ __($name) }}" class="w-full rounded-full px-4 py-3">
        {{ $slot }}
    </select>
    @error($name)
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
