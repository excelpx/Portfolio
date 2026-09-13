<x-filament-panels::page>
    <div>
        <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
            Tools &amp; Technologies
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Kelola logo, URL, urutan, dan status tools yang tampil di website.
        </p>
    </div>

    <form wire:submit="save" class="mt-6">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Simpan Tools &amp; Technologies
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
