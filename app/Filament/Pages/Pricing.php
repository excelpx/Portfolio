<?php

namespace App\Filament\Pages;

use App\Services\FirebaseService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Pricing extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Pricing';

    protected static ?string $title = 'Pricing Website';

    protected static ?string $navigationGroup = 'Website';

    protected static string $view = 'filament.pages.pricing';

    public ?array $data = [];

    public function mount(): void
    {
        $pricing = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('pricing')
            ->getValue();

        $pricing = is_array($pricing) ? $pricing : [];
        $section = is_array($pricing['section'] ?? null) ? $pricing['section'] : [];
        $items = [];

        if (is_array($pricing['items'] ?? null)) {
            foreach ($pricing['items'] as $key => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $features = [];
                if (is_array($item['features'] ?? null)) {
                    foreach ($item['features'] as $feature) {
                        $text = is_array($feature)
                            ? trim((string) ($feature['feature'] ?? ''))
                            : trim((string) $feature);

                        if ($text !== '') {
                            $features[] = ['feature' => $text];
                        }
                    }
                }

                $items[] = [
                    'key' => (string) $key,
                    'name' => (string) ($item['name'] ?? ''),
                    'price' => (string) ($item['price'] ?? ''),
                    'period' => (string) ($item['period'] ?? ''),
                    'description' => (string) ($item['description'] ?? ''),
                    'features' => $features,
                    'button_text' => (string) ($item['button_text'] ?? ''),
                    'button_link' => (string) ($item['button_link'] ?? ''),
                    'popular' => (bool) ($item['popular'] ?? false),
                    'order' => (int) ($item['order'] ?? 0),
                ];
            }
        }

        usort($items, static fn (array $left, array $right): int => $left['order'] <=> $right['order']);

        $this->form->fill([
            'section' => [
                'title' => (string) ($section['title'] ?? 'Pricing'),
                'subtitle' => (string) ($section['subtitle'] ?? ''),
            ],
            'items' => $items,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Section Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('section.title')
                            ->label('Section Title')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('section.subtitle')
                            ->label('Section Subtitle')
                            ->maxLength(200),
                    ])
                    ->columns(2),

                Forms\Components\Repeater::make('items')
                    ->label('Daftar Pricing')
                    ->schema([
                        Forms\Components\Hidden::make('key'),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Paket')
                            ->required()
                            ->maxLength(150),
                        Forms\Components\TextInput::make('price')
                            ->label('Harga')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('period')
                            ->label('Periode Harga')
                            ->placeholder('per bulan / sekali bayar')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('order')
                            ->label('Urutan Tampilan')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Paket')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('features')
                            ->label('Daftar Fitur')
                            ->schema([
                                Forms\Components\TextInput::make('feature')
                                    ->label('Fitur')
                                    ->required()
                                    ->columnSpanFull(),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Fitur')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('button_text')
                            ->label('Teks Tombol')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('button_link')
                            ->label('Link Tombol')
                            ->url()
                            ->maxLength(500),
                        Forms\Components\Toggle::make('popular')
                            ->label('Paket Unggulan / Popular'),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Pricing')
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
            ->getReference('pricing');
        $section = is_array($data['section'] ?? null) ? $data['section'] : [];

        $reference->getChild('section')->set([
            'title' => trim((string) ($section['title'] ?? 'Pricing')),
            'subtitle' => trim((string) ($section['subtitle'] ?? '')),
        ]);

        $itemsReference = $reference->getChild('items');
        $existing = $itemsReference->getValue();
        $existing = is_array($existing) ? $existing : [];
        $submittedKeys = [];
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];

        foreach ($items as $item) {
            $key = trim((string) ($item['key'] ?? ''));
            $features = [];

            if (is_array($item['features'] ?? null)) {
                foreach ($item['features'] as $feature) {
                    $text = is_array($feature)
                        ? trim((string) ($feature['feature'] ?? ''))
                        : trim((string) $feature);

                    if ($text !== '') {
                        $features[] = $text;
                    }
                }
            }

            $payload = [
                'name' => trim((string) ($item['name'] ?? '')),
                'price' => trim((string) ($item['price'] ?? '')),
                'period' => trim((string) ($item['period'] ?? '')),
                'description' => trim((string) ($item['description'] ?? '')),
                'features' => $features,
                'button_text' => trim((string) ($item['button_text'] ?? '')),
                'button_link' => trim((string) ($item['button_link'] ?? '')),
                'popular' => (bool) ($item['popular'] ?? false),
                'order' => max(0, (int) ($item['order'] ?? 0)),
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
            ->title('Pricing berhasil disimpan')
            ->success()
            ->send();
    }
}
