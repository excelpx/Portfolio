<?php

namespace App\Filament\Pages;

use App\Services\FirebaseService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

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
                            ->required(),

                        Forms\Components\TextInput::make('profession')
                            ->label('Profesi')
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email(),

                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor Telepon'),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(6)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('image')
                            ->label('URL Foto Profile')
                            ->columnSpanFull(),

                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $database = app(FirebaseService::class)->getDatabase();

        $database
            ->getReference('profile')
            ->set($data);

        $this->form->fill($data);
        $this->isEditing = false;

        Notification::make()
            ->title('Profile berhasil disimpan')
            ->success()
            ->send();
    }
}