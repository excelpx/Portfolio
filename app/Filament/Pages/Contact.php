<?php

namespace App\Filament\Pages;

use App\Services\FirebaseService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Contact extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Contact';

    protected static ?string $title = 'Contact Website';

    protected static ?string $navigationGroup = 'Website';

    protected static string $view = 'filament.pages.contact';

    public ?array $data = [];

    public function mount(): void
    {
        $contact = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('contact')
            ->getValue();
        $contact = is_array($contact) ? $contact : [];

        $this->form->fill([
            'address' => (string) ($contact['address'] ?? ''),
            'address_link' => (string) ($contact['address_link'] ?? ''),
            'phone' => (string) ($contact['phone'] ?? ''),
            'phone_link' => (string) ($contact['phone_link'] ?? ''),
            'email' => (string) ($contact['email'] ?? ''),
            'email_link' => (string) ($contact['email_link'] ?? ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('address')
                    ->label('Address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address_link')
                    ->label('Address Link')
                    ->url()
                    ->maxLength(500),
                Forms\Components\TextInput::make('phone')
                    ->label('Call Us / Phone')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('phone_link')
                    ->label('Call Us Link')
                    ->maxLength(500)
                    ->helperText('Gunakan https://wa.me/... atau https://wa.link/...'),
                Forms\Components\TextInput::make('email')
                    ->label('Email Us')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email_link')
                    ->label('Email Us Link')
                    ->maxLength(500)
                    ->helperText('Gunakan format mailto:alamat@email.com'),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        app(FirebaseService::class)
            ->getDatabase()
            ->getReference('contact')
            ->set([
                'address' => trim((string) ($data['address'] ?? '')),
                'address_link' => $this->safeLink($data['address_link'] ?? '', ['http', 'https']),
                'phone' => trim((string) ($data['phone'] ?? '')),
                'phone_link' => $this->safeLink($data['phone_link'] ?? '', ['http', 'https']),
                'email' => trim((string) ($data['email'] ?? '')),
                'email_link' => $this->safeLink($data['email_link'] ?? '', ['mailto']),
            ]);

        $this->mount();

        Notification::make()
            ->title('Contact berhasil disimpan')
            ->success()
            ->send();
    }

    private function safeLink(mixed $value, array $allowedSchemes): string
    {
        $link = trim((string) $value);

        if ($link === '') {
            return '';
        }

        $scheme = strtolower((string) parse_url($link, PHP_URL_SCHEME));

        return in_array($scheme, $allowedSchemes, true) ? $link : '';
    }
}
