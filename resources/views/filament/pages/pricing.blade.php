<x-filament-panels::page>
    <div>
        <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
            Pricing
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Kelola paket, harga, fitur, dan urutan Pricing website.
        </p>
    </div>

    <form wire:submit="save" class="mt-6">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Simpan Pricing
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
