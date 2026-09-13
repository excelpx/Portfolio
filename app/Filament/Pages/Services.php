<?php

namespace App\Filament\Pages;

use App\Services\FirebaseService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Services extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Services';

    protected static ?string $title = 'Services Website';

    protected static ?string $navigationGroup = 'Website';

    protected static string $view = 'filament.pages.services';

    public ?array $data = [];

    public function mount(): void
    {
        $services = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('services')
            ->getValue();

        $services = is_array($services) ? $services : [];
        $itemsSource = is_array($services['items'] ?? null) ? $services['items'] : $services;
        $items = [];

        foreach ($itemsSource as $key => $service) {
            if (! is_array($service) || isset($service['items'])) {
                continue;
            }

            $items[] = [
                'key' => (string) $key,
                'icon' => (string) ($service['icon'] ?? 'bi bi-activity'),
                'name' => (string) ($service['name'] ?? ''),
                'description' => (string) ($service['description'] ?? ''),
            ];
        }

        $this->form->fill([
            'title' => (string) ($services['title'] ?? 'Services'),
            'subtitle' => (string) ($services['subtitle'] ?? 'Layanan yang saya tawarkan'),
            'items' => $items,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Judul Section')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('subtitle')
                            ->label('Subtitle')
                            ->maxLength(200),
                    ])
                    ->columns(2),

                Forms\Components\Repeater::make('items')
                    ->label('Daftar Services')
                    ->schema([
                        Forms\Components\Hidden::make('key'),
                        Forms\Components\TextInput::make('icon')
                            ->label('Icon Bootstrap')
                            ->placeholder('bi bi-activity')
                            ->helperText('Gunakan class Bootstrap Icons, contoh: bi bi-code-slash.')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Layanan')
                            ->required()
                            ->maxLength(150),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Service')
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
            ->getReference('services');

        $reference->getChild('title')->set(trim((string) ($data['title'] ?? 'Services')));
        $reference->getChild('subtitle')->set(trim((string) ($data['subtitle'] ?? '')));

        $itemsReference = $reference->getChild('items');
        $existing = $itemsReference->getValue();
        $existing = is_array($existing) ? $existing : [];
        $submittedKeys = [];
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];

        foreach ($items as $item) {
            $key = trim((string) ($item['key'] ?? ''));
            $payload = [
                'icon' => trim((string) ($item['icon'] ?? '')),
                'name' => trim((string) ($item['name'] ?? '')),
                'description' => trim((string) ($item['description'] ?? '')),
            ];

            if ($key !== '' && array_key_exists($key, $existing)) {
                $itemsReference->getChild($key)->set($payload);
                $submittedKeys[] = $key;
            } else {
                $newItem = $itemsReference->push($payload);
                $submittedKeys[] = $newItem->getKey();
            }
        }

        foreach (array_keys($existing) as $key) {
            if (! in_array((string) $key, $submittedKeys, true)) {
                $itemsReference->getChild($key)->remove();
            }
        }

        $this->mount();

        Notification::make()
            ->title('Services berhasil disimpan')
            ->success()
            ->send();
    }
}
