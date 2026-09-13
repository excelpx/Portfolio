<?php

namespace App\Filament\Pages;

use App\Services\FirebaseService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Resume extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Resume';

    protected static ?string $title = 'Resume Website';

    protected static ?string $navigationGroup = 'Website';

    protected static string $view = 'filament.pages.resume';

    public ?array $data = [];

    public function mount(): void
    {
        $resume = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('resume')
            ->getValue();

        $resume = is_array($resume) ? $resume : [];
        $summary = is_array($resume['summary'] ?? null) ? $resume['summary'] : [];
        $items = [];

        if (is_array($resume['items'] ?? null)) {
            foreach ($resume['items'] as $key => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $details = [];
                if (is_array($item['details'] ?? null)) {
                    foreach ($item['details'] as $detail) {
                        $detailText = is_array($detail)
                            ? trim((string) ($detail['detail'] ?? ''))
                            : trim((string) $detail);

                        if ($detailText !== '') {
                            $details[] = ['detail' => $detailText];
                        }
                    }
                }

                $items[] = [
                    'key' => (string) $key,
                    'type' => (string) ($item['type'] ?? 'education'),
                    'title' => (string) ($item['title'] ?? ''),
                    'period' => (string) ($item['period'] ?? ''),
                    'organization' => (string) ($item['organization'] ?? ''),
                    'description' => (string) ($item['description'] ?? ''),
                    'details' => $details,
                ];
            }
        }

        $this->form->fill([
            'title' => (string) ($resume['title'] ?? 'Resume'),
            'subtitle' => (string) ($resume['subtitle'] ?? ''),
            'summary' => [
                'title' => (string) ($summary['title'] ?? ''),
                'description' => (string) ($summary['description'] ?? ''),
                'address' => (string) ($summary['address'] ?? ''),
                'phone' => (string) ($summary['phone'] ?? ''),
                'email' => (string) ($summary['email'] ?? ''),
            ],
            'items' => $items,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Judul Section Resume')
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

                Forms\Components\Section::make('Summary')
                    ->schema([
                        Forms\Components\TextInput::make('summary.title')
                            ->label('Nama')
                            ->required()
                            ->maxLength(150),
                        Forms\Components\Textarea::make('summary.description')
                            ->label('Deskripsi')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('summary.address')
                            ->label('Alamat'),
                        Forms\Components\TextInput::make('summary.phone')
                            ->label('Nomor Telepon'),
                        Forms\Components\TextInput::make('summary.email')
                            ->label('Email')
                            ->email(),
                    ])
                    ->columns(2),

                Forms\Components\Repeater::make('items')
                    ->label('Education dan Professional Experience')
                    ->schema([
                        Forms\Components\Hidden::make('key'),
                        Forms\Components\Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'education' => 'Education',
                                'experience' => 'Professional Experience',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label('Judul / Posisi')
                            ->required()
                            ->maxLength(200),
                        Forms\Components\TextInput::make('period')
                            ->label('Tahun / Periode')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('organization')
                            ->label('Institusi / Perusahaan')
                            ->required()
                            ->maxLength(200),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('details')
                            ->label('Detail / Bullet Experience')
                            ->schema([
                                Forms\Components\TextInput::make('detail')
                                    ->label('Detail')
                                    ->required()
                                    ->columnSpanFull(),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Detail')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Resume Item')
                    ->reorderable(false)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $resumeReference = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('resume');

        $resumeReference->getChild('title')->set(trim((string) ($data['title'] ?? 'Resume')));
        $resumeReference->getChild('subtitle')->set(trim((string) ($data['subtitle'] ?? '')));

        $summary = is_array($data['summary'] ?? null) ? $data['summary'] : [];
        $resumeReference->getChild('summary')->set([
            'title' => trim((string) ($summary['title'] ?? '')),
            'description' => trim((string) ($summary['description'] ?? '')),
            'address' => trim((string) ($summary['address'] ?? '')),
            'phone' => trim((string) ($summary['phone'] ?? '')),
            'email' => trim((string) ($summary['email'] ?? '')),
        ]);

        $itemsReference = $resumeReference->getChild('items');
        $existing = $itemsReference->getValue();
        $existing = is_array($existing) ? $existing : [];
        $submittedKeys = [];
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];

        foreach ($items as $item) {
            $key = trim((string) ($item['key'] ?? ''));
            $details = [];
            foreach (($item['details'] ?? []) as $detail) {
                if (is_array($detail) && trim((string) ($detail['detail'] ?? '')) !== '') {
                    $details[] = trim((string) $detail['detail']);
                } elseif (is_string($detail) && trim($detail) !== '') {
                    $details[] = trim($detail);
                }
            }

            $payload = [
                'type' => in_array($item['type'] ?? '', ['education', 'experience'], true)
                    ? $item['type']
                    : 'education',
                'title' => trim((string) ($item['title'] ?? '')),
                'period' => trim((string) ($item['period'] ?? '')),
                'organization' => trim((string) ($item['organization'] ?? '')),
                'description' => trim((string) ($item['description'] ?? '')),
                'details' => $details,
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
            ->title('Resume berhasil disimpan')
            ->success()
            ->send();
    }
}
