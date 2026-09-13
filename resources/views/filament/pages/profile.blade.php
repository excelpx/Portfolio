<x-filament-panels::page>
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                Kelola Konten Website
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Ubah informasi Profile yang digunakan pada Hero dan About website.
            </p>
        </div>

        @if (! $isEditing)
            <x-filament::button
                type="button"
                wire:click="editWebsite"
                icon="heroicon-m-pencil-square"
            >
                Edit Website
            </x-filament::button>
        @endif
    </div>

    @if ($isEditing)
        <form wire:submit="save" class="mt-6">
            {{ $this->form }}

            <div class="mt-6 flex items-center gap-3">
                <x-filament::button type="submit">
                    Simpan Perubahan
                </x-filament::button>

                <x-filament::button
                    type="button"
                    color="gray"
                    wire:click="cancelEdit"
                >
                    Batal
                </x-filament::button>
            </div>
        </form>
    @endif
</x-filament-panels::page>
