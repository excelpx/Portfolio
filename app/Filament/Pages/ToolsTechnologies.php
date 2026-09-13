<?php

namespace App\Filament\Pages;

use App\Services\CloudinaryService;
use App\Services\FirebaseService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Http\UploadedFile;
use Throwable;

class ToolsTechnologies extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'Tools & Technologies';

    protected static ?string $title = 'Tools & Technologies Website';

    protected static ?string $navigationGroup = 'Website';

    protected static string $view = 'filament.pages.tools-technologies';

    public ?array $data = [];

    public function mount(): void
    {
        $tools = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('tools_technologies')
            ->getValue();

        $items = [];
        if (is_array($tools)) {
            foreach ($tools as $key => $tool) {
                if (! is_array($tool)) {
                    continue;
                }

                $items[] = [
                    'key' => (string) $key,
                    'name' => (string) ($tool['name'] ?? ''),
                    'icon' => (string) ($tool['icon'] ?? ''),
                    'image_url' => (string) ($tool['image_url'] ?? ''),
                    'website_url' => (string) ($tool['website_url'] ?? ''),
                    'sort_order' => (int) ($tool['sort_order'] ?? 0),
                    'status' => (bool) ($tool['status'] ?? true),
                    'image_upload' => null,
                ];
            }
        }

        usort($items, static fn (array $left, array $right): int => $left['sort_order'] <=> $right['sort_order']);

        $this->form->fill(['items' => $items]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('items')
                    ->label('Daftar Tools & Technologies')
                    ->schema([
                        Forms\Components\Hidden::make('key'),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('icon')
                            ->label('URL Logo / Icon')
                            ->placeholder('https://...')
                            ->helperText('URL ini dipakai jika tidak ada upload Cloudinary.')
                            ->maxLength(500),
                        Forms\Components\TextInput::make('image_url')
                            ->label('Image URL (Opsional)')
                            ->url()
                            ->maxLength(500),
                        Forms\Components\FileUpload::make('image_upload')
                            ->label('Upload Logo ke Cloudinary')
                            ->image()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(5120)
                            ->storeFiles(false)
                            ->helperText('Opsional. JPG, JPEG, PNG, atau WEBP. Maksimal 5 MB.')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('website_url')
                            ->label('Website URL (Opsional)')
                            ->url()
                            ->maxLength(500),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\Toggle::make('status')
                            ->label('Status Aktif')
                            ->default(true),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Tool / Technology')
                    ->reorderable(false)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $reference = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('tools_technologies');
        $existing = $reference->getValue();
        $existing = is_array($existing) ? $existing : [];
        $submittedKeys = [];
        $payloads = [];
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];

        foreach ($items as $item) {
            $key = trim((string) ($item['key'] ?? ''));
            $icon = trim((string) ($item['icon'] ?? ''));
            $uploadedFile = $this->getUploadedFile($item['image_upload'] ?? null);

            if ($uploadedFile instanceof UploadedFile) {
                try {
                    $icon = app(CloudinaryService::class)
                        ->uploadImage($uploadedFile, 'portfolio/tools-technologies', 'logo tool');
                } catch (Throwable) {
                    Notification::make()
                        ->title('Upload logo gagal')
                        ->body('Logo tidak dapat diunggah ke Cloudinary. Data tidak disimpan.')
                        ->danger()
                        ->send();

                    return;
                }
            }

            if ($icon === '' && trim((string) ($item['image_url'] ?? '')) === '') {
                Notification::make()
                    ->title('Logo belum diisi')
                    ->body('Masukkan URL logo atau upload file logo terlebih dahulu.')
                    ->danger()
                    ->send();

                return;
            }

            $payloads[] = [
                'key' => $key,
                'payload' => [
                    'name' => trim((string) ($item['name'] ?? '')),
                    'icon' => $icon,
                    'image_url' => trim((string) ($item['image_url'] ?? '')),
                    'website_url' => trim((string) ($item['website_url'] ?? '')),
                    'sort_order' => max(0, (int) ($item['sort_order'] ?? 0)),
                    'status' => (bool) ($item['status'] ?? false),
                ],
            ];
        }

        foreach ($payloads as $item) {
            if ($item['key'] !== '' && array_key_exists($item['key'], $existing)) {
                $reference->getChild($item['key'])->set($item['payload']);
                $submittedKeys[] = $item['key'];
            } else {
                $newItem = $reference->push($item['payload']);
                $submittedKeys[] = $newItem->getKey();
            }
        }

        foreach (array_keys($existing) as $key) {
            if (! in_array((string) $key, $submittedKeys, true)) {
                $reference->getChild($key)->remove();
            }
        }

        $this->mount();

        Notification::make()
            ->title('Tools & Technologies berhasil disimpan')
            ->success()
            ->send();
    }

    private function getUploadedFile(mixed $value): ?UploadedFile
    {
        if ($value instanceof UploadedFile) {
            return $value;
        }

        if (is_array($value)) {
            $firstValue = reset($value);

            return $firstValue instanceof UploadedFile ? $firstValue : null;
        }

        return null;
    }
}
