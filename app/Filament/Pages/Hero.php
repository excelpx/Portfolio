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

class Hero extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Hero / Home';

    protected static ?string $title = 'Hero / Home Website';

    protected static ?string $navigationGroup = 'Website';

    protected static string $view = 'filament.pages.hero';

    public ?array $data = [];

    public function mount(): void
    {
        $hero = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('hero')
            ->getValue();

        $hero = is_array($hero) ? $hero : [];

        $this->form->fill([
            'background_image' => (string) ($hero['background_image'] ?? ''),
            'background_upload' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('background_image'),
                Forms\Components\FileUpload::make('background_upload')
                    ->label('Foto Background Hero')
                    ->image()
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'image/gif',
                    ])
                    ->maxSize(10240)
                    ->storeFiles(false)
                    ->helperText('Unggah gambar maksimal 10 MB. Foto akan disimpan di Cloudinary.')
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $image = trim((string) ($data['background_image'] ?? ''));
        $uploadedFile = $this->getUploadedFile($data['background_upload'] ?? null);

        if ($uploadedFile instanceof UploadedFile) {
            try {
                $image = app(CloudinaryService::class)->uploadImage(
                    $uploadedFile,
                    'portfolio/hero',
                    'foto background Hero'
                );
            } catch (Throwable $exception) {
                Notification::make()
                    ->title('Upload background Hero gagal')
                    ->body($exception->getMessage())
                    ->danger()
                    ->send();

                return;
            }
        }

        if ($image === '') {
            Notification::make()
                ->title('Foto background Hero belum tersedia')
                ->body('Unggah foto baru untuk mengganti background Hero.')
                ->danger()
                ->send();

            return;
        }

        app(FirebaseService::class)
            ->getDatabase()
            ->getReference('hero')
            ->set([
                'background_image' => $image,
            ]);

        $this->mount();

        Notification::make()
            ->title('Background Hero berhasil disimpan')
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
