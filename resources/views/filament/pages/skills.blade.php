<x-filament-panels::page>
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                Skills
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Kelola nama dan persentase skills yang ditampilkan pada website.
            </p>
        </div>
    </div>

    <form wire:submit="save" class="mt-6">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Simpan Skills
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
