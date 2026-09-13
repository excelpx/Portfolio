<x-filament-panels::page>
    <div>
        <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
            Q&amp;A
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Kelola judul, deskripsi, pertanyaan, jawaban, dan urutan Q&amp;A website.
        </p>
    </div>

    <form wire:submit="save" class="mt-6">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Simpan Q&amp;A
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
