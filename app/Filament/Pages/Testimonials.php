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

class Testimonials extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Testimonials';

    protected static ?string $title = 'Testimonials Website';

    protected static ?string $navigationGroup = 'Website';

    protected static string $view = 'filament.pages.testimonials';

    public ?array $data = [];

    public function mount(): void
    {
        $testimonials = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('testimonials')
            ->getValue();

        $testimonials = is_array($testimonials) ? $testimonials : [];
        $items = [];

        foreach ($testimonials as $key => $testimonial) {
            if (! is_array($testimonial)) {
                continue;
            }

            $items[] = [
                'key' => (string) $key,
                'image' => (string) ($testimonial['image'] ?? ''),
                'image_upload' => null,
                'name' => (string) ($testimonial['name'] ?? ''),
                'position' => (string) ($testimonial['position'] ?? ''),
                'rating' => min(5, max(0, (int) ($testimonial['rating'] ?? 5))),
                'content' => (string) ($testimonial['content'] ?? ''),
                'order' => (int) ($testimonial['order'] ?? 0),
                'active' => (bool) ($testimonial['active'] ?? true),
            ];
        }

        usort($items, static fn (array $left, array $right): int => $left['order'] <=> $right['order']);

        $this->form->fill(['items' => $items]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('items')
                    ->label('Daftar Testimonials')
                    ->schema([
                        Forms\Components\Hidden::make('key'),
                        Forms\Components\Hidden::make('image'),
                        Forms\Components\FileUpload::make('image_upload')
                            ->label('Foto')
                            ->image()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'image/gif',
                            ])
                            ->maxSize(5120)
                            ->storeFiles(false)
                            ->helperText('Unggah gambar maksimal 5 MB. Foto akan disimpan di Cloudinary.')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(150),
                        Forms\Components\TextInput::make('position')
                            ->label('Jabatan / Profesi')
                            ->required()
                            ->maxLength(150),
                        Forms\Components\TextInput::make('rating')
                            ->label('Rating')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(5)
                            ->required(),
                        Forms\Components\TextInput::make('order')
                            ->label('Urutan Tampilan')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\Textarea::make('content')
                            ->label('Isi Testimoni')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('active')
                            ->label('Status Aktif')
                            ->default(true),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Testimonial')
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
            ->getReference('testimonials');
        $existing = $reference->getValue();
        $existing = is_array($existing) ? $existing : [];
        $submittedKeys = [];
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];
        $payloads = [];

        foreach ($items as $item) {
            $key = trim((string) ($item['key'] ?? ''));
            $image = trim((string) ($item['image'] ?? ''));
            $uploadedFile = $this->getUploadedFile($item['image_upload'] ?? null);

            if ($uploadedFile instanceof UploadedFile) {
                try {
                    $image = app(CloudinaryService::class)
                        ->uploadTestimonialImage($uploadedFile);
                } catch (Throwable $exception) {
                    Notification::make()
                        ->title('Upload foto testimonial gagal')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return;
                }
            }

            if ($image === '') {
                Notification::make()
                    ->title('Foto testimonial wajib diisi')
                    ->body('Unggah foto baru atau pertahankan URL foto yang sudah ada.')
                    ->danger()
                    ->send();

                return;
            }

            $payload = [
                'image' => $image,
                'name' => trim((string) ($item['name'] ?? '')),
                'position' => trim((string) ($item['position'] ?? '')),
                'rating' => min(5, max(0, (int) ($item['rating'] ?? 0))),
                'content' => trim((string) ($item['content'] ?? '')),
                'order' => max(0, (int) ($item['order'] ?? 0)),
                'active' => (bool) ($item['active'] ?? false),
            ];

            $payloads[] = [
                'key' => $key,
                'payload' => $payload,
            ];
        }

        foreach ($payloads as $item) {
            $key = $item['key'];
            $payload = $item['payload'];

            if ($key !== '' && array_key_exists($key, $existing)) {
                $reference->getChild($key)->set($payload);
                $submittedKeys[] = $key;
            } else {
                $newItem = $reference->push($payload);
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
            ->title('Testimonials berhasil disimpan')
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
