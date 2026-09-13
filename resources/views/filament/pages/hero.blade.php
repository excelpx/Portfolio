<x-filament-panels::page>
    <div>
        <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
            Hero / Home
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Ganti foto background Hero website. Foto baru akan disimpan di Cloudinary.
        </p>
    </div>

    <form wire:submit="save" class="mt-6">
        @php
            $currentBackground = trim((string) ($data['background_image'] ?? ''));
            $currentBackgroundUrl = filter_var($currentBackground, FILTER_VALIDATE_URL)
                ? $currentBackground
                : asset($currentBackground !== '' ? $currentBackground : 'img/hero-img.jpg');
        @endphp

        <div class="mb-6">
            <p class="mb-2 text-sm font-medium text-gray-950 dark:text-white">
                Background Hero Saat Ini
            </p>
            <img
                src="{{ $currentBackgroundUrl }}"
                alt="Background Hero saat ini"
                class="h-48 w-full rounded-xl object-cover"
            >
        </div>

        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Simpan Background Hero
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
