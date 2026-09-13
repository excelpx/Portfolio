<?php

namespace App\Filament\Pages;

use App\Services\FirebaseService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Statistics extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Statistics';

    protected static ?string $title = 'Statistics Website';

    protected static ?string $navigationGroup = 'Website';

    protected static string $view = 'filament.pages.statistics';

    public ?array $data = [];

    public function mount(): void
    {
        $statistics = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('statistics')
            ->getValue();

        $statistics = is_array($statistics) ? $statistics : [];
        $section = is_array($statistics['section'] ?? null) ? $statistics['section'] : [];
        $items = [];

        if (is_array($statistics['items'] ?? null)) {
            foreach ($statistics['items'] as $key => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $items[] = [
                    'key' => (string) $key,
                    'label' => (string) ($item['label'] ?? ''),
                    'value' => (int) ($item['value'] ?? 0),
                    'icon' => (string) ($item['icon'] ?? 'bi bi-bar-chart'),
                    'order' => (int) ($item['order'] ?? 0),
                ];
            }
        }

        usort($items, static fn (array $left, array $right): int => $left['order'] <=> $right['order']);

        $this->form->fill([
            'section' => [
                'title' => (string) ($section['title'] ?? 'Statistics'),
                'subtitle' => (string) ($section['subtitle'] ?? ''),
                'background_image' => (string) ($section['background_image'] ?? 'img/stats-bg.jpg'),
            ],
            'items' => $items,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Section Statistics')
                    ->schema([
                        Forms\Components\TextInput::make('section.title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('section.subtitle')
                            ->label('Subtitle')
                            ->maxLength(200),
                        Forms\Components\TextInput::make('section.background_image')
                            ->label('Background Image')
                            ->placeholder('img/stats-bg.jpg atau https://...')
                            ->helperText('Gunakan path asset public atau URL gambar.')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Repeater::make('items')
                    ->label('Daftar Statistics')
                    ->schema([
                        Forms\Components\Hidden::make('key'),
                        Forms\Components\TextInput::make('label')
                            ->label('Statistic Name')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('value')
                            ->label('Value')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\TextInput::make('icon')
                            ->label('Icon Bootstrap')
                            ->placeholder('bi bi-people')
                            ->helperText('Contoh: bi bi-people atau <i class="bi bi-people"></i>.')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('order')
                            ->label('Urutan Tampilan')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Statistic')
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
            ->getReference('statistics');
        $section = is_array($data['section'] ?? null) ? $data['section'] : [];

        $reference->getChild('section')->set([
            'title' => trim((string) ($section['title'] ?? 'Statistics')),
            'subtitle' => trim((string) ($section['subtitle'] ?? '')),
            'background_image' => trim((string) ($section['background_image'] ?? 'img/stats-bg.jpg')),
        ]);

        $itemsReference = $reference->getChild('items');
        $existing = $itemsReference->getValue();
        $existing = is_array($existing) ? $existing : [];
        $submittedKeys = [];
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];

        foreach ($items as $item) {
            $key = trim((string) ($item['key'] ?? ''));
            $payload = [
                'label' => trim((string) ($item['label'] ?? '')),
                'value' => max(0, (int) ($item['value'] ?? 0)),
                'icon' => trim((string) ($item['icon'] ?? '')),
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
            ->title('Statistics berhasil disimpan')
            ->success()
            ->send();
    }
}
