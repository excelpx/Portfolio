<x-filament-panels::page>
    <div>
        <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
            Statistics
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Kelola counter, icon, urutan, dan background section Statistics website.
        </p>
    </div>

    <form wire:submit="save" class="mt-6">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Simpan Statistics
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
