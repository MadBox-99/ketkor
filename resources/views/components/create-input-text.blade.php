@props(['name' => '', 'headText' => '', 'disabled' => false, 'required' => true, 'type' => 'text'])

<div class="@if ($type == 'hidden') hidden @endif basis-full">
    <div class="col-span-full">
        <label class="my-5 block text-left text-lg font-medium leading-6 text-gray-900" for="{{ $name }}">
            @if ($required)
                <span style="color:red;">*</span>
            @endif
            {{ __($headText) }}
        </label>
        <div class="mt-2">
            <div
                class="flex rounded-md shadow-sm focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                <input
                    class="@error($name) border-l-danger-600 @enderror block w-full rounded border-0 p-2 py-1.5 text-3xl text-gray-900 shadow ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
                    value="{{ !empty(old($name)) ? old($name) : $slot }}" placeholder="{{ __($headText) }}"
                    @if ($disabled) {!! 'readonly ' !!} @endif>
            </div>
        </div>
    </div>
    @error($name)
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
