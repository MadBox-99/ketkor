@props(['headText' => '', 'routeLink' => ''])

<h2 class="text-xl font-bold leading-tight text-primary">
    <div class="flex justify-between">
        <div class="basis-1/4">
            <h1 class="mb-0 text-primary">{{ __($headText) }}</h1>

        </div>
        <div class="border-p basis-auto">
            <a wire:navigate href="{{ route($routeLink) }}"
                class="text-primary transition duration-150 ease-in-out hover:text-yellow">
                <i class="fas fa-plus"></i> {{ __($slot->__toString()) }}
            </a>
        </div>
    </div>
</h2>
