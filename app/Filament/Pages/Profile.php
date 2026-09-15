<?php

namespace App\Filament\Pages;

use App\Services\CloudinaryService;
use App\Services\FirebaseService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Http\UploadedFile;
use Throwable;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationLabel = 'Profile';

    protected static ?string $title = 'Profile Website';

    protected static ?string $navigationGroup = 'Website';

    protected static string $view = 'filament.pages.profile';

    public ?array $data = [];

    public bool $isEditing = false;

    public function mount(): void
    {
        $database = app(FirebaseService::class)->getDatabase();

        $profile = $database
            ->getReference('profile')
            ->getValue();

        $profile = is_array($profile) ? $profile : [];

        $this->form->fill([
            'name' => $profile['name'] ?? '',
            'profession' => $profile['profession'] ?? '',
            'email' => $profile['email'] ?? '',
            'phone' => $profile['phone'] ?? '',
            'description' => $profile['description'] ?? '',
            'image' => $profile['image'] ?? '',
            'image_upload' => null,
        ]);
    }

    public function editWebsite(): void
    {
        $this->isEditing = true;
    }

    public function cancelEdit(): void
    {
        $this->isEditing = false;

        $this->mount();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Profile')
                    ->schema([

                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(150),

                        Forms\Components\TextInput::make('profession')
                            ->label('Profesi')
                            ->required()
                            ->maxLength(150),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(150),

                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->maxLength(50),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(6)
                            ->columnSpanFull(),

                        /*
                        |--------------------------------------------------------------------------
                        | URL gambar lama
                        |--------------------------------------------------------------------------
                        */
                        Forms\Components\Hidden::make('image'),

                        /*
                        |--------------------------------------------------------------------------
                        | Upload gambar baru
                        |--------------------------------------------------------------------------
                        */
                        Forms\Components\FileUpload::make('image_upload')
                            ->label('Foto Profile')
                            ->image()
                            ->imageEditor()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'image/gif',
                            ])
                            ->maxSize(20480)
                            ->storeFiles(false)
                            ->helperText(
                                'Upload foto maksimal 20 MB. Foto akan disimpan ke Cloudinary.'
                            )
                            ->columnSpanFull(),

                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        /*
        |--------------------------------------------------------------------------
        | Ambil URL gambar lama
        |--------------------------------------------------------------------------
        */
        $image = trim((string) ($data['image'] ?? ''));

        /*
        |--------------------------------------------------------------------------
        | Cek apakah ada gambar baru
        |--------------------------------------------------------------------------
        */
        $uploadedFile = $this->getUploadedFile(
            $data['image_upload'] ?? null
        );

        if ($uploadedFile instanceof UploadedFile) {

            try {

                /*
                |--------------------------------------------------------------------------
                | Upload ke Cloudinary
                |--------------------------------------------------------------------------
                */
                $image = app(CloudinaryService::class)
                    ->uploadProfileImage($uploadedFile);

            } catch (Throwable $exception) {

                Notification::make()
                    ->title('Upload foto profile gagal')
                    ->body($exception->getMessage())
                    ->danger()
                    ->persistent()
                    ->send();

                return;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Data profile yang disimpan ke Firebase
        |--------------------------------------------------------------------------
        */
        $payload = [
            'name' => trim((string) ($data['name'] ?? '')),
            'profession' => trim((string) ($data['profession'] ?? '')),
            'email' => trim((string) ($data['email'] ?? '')),
            'phone' => trim((string) ($data['phone'] ?? '')),
            'description' => trim((string) ($data['description'] ?? '')),
            'image' => $image,
        ];

        /*
        |--------------------------------------------------------------------------
        | Simpan ke Firebase
        |--------------------------------------------------------------------------
        */
        $database = app(FirebaseService::class)->getDatabase();

        $database
            ->getReference('profile')
            ->set($payload);

        /*
        |--------------------------------------------------------------------------
        | Refresh form
        |--------------------------------------------------------------------------
        */
        $this->form->fill([
            ...$payload,
            'image_upload' => null,
        ]);

        $this->isEditing = false;

        Notification::make()
            ->title('Profile berhasil disimpan')
            ->body('Data profile dan foto berhasil diperbarui.')
            ->success()
            ->send();
    }

    /**
     * Mengambil UploadedFile dari state FileUpload.
     */
    private function getUploadedFile(mixed $value): ?UploadedFile
    {
        if ($value instanceof UploadedFile) {
            return $value;
        }

        if (is_array($value)) {
            $firstValue = reset($value);

            return $firstValue instanceof UploadedFile
                ? $firstValue
                : null;
        }

        return null;
    }
}