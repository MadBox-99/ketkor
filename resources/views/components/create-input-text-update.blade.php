@props(['name' => '', 'headText' => ''])

<div class="col-sm-6 mb-sm-0 mb-3">
    <label for="{{ $name }}"> <span style="color:red;">*</span>{{ __($headText) }}</label>
    <input type="text" class="border @error($name) border-l-danger-600 @enderror rounded p-2 text-2xl shadow"
        id="{{ $name }}" placeholder="{{ __($headText) }}" name="{{ $name }}"
        value="{{ !empty(old($name)) ? old($name) : $slot }}">

    @error($name)
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
