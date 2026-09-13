<?php

namespace App\Filament\Pages;

use App\Services\FirebaseService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Faq extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'Q&A';

    protected static ?string $title = 'Q&A Website';

    protected static ?string $navigationGroup = 'Website';

    protected static string $view = 'filament.pages.faq';

    public ?array $data = [];

    public function mount(): void
    {
        $faq = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('faq')
            ->getValue();

        $faq = is_array($faq) ? $faq : [];
        $section = is_array($faq['section'] ?? null) ? $faq['section'] : [];
        $items = [];

        if (is_array($faq['items'] ?? null)) {
            foreach ($faq['items'] as $key => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $items[] = [
                    'key' => (string) $key,
                    'question' => (string) ($item['question'] ?? ''),
                    'answer' => (string) ($item['answer'] ?? ''),
                    'order' => (int) ($item['order'] ?? 0),
                ];
            }
        }

        usort($items, static fn (array $left, array $right): int => $left['order'] <=> $right['order']);

        $this->form->fill([
            'section' => [
                'title' => (string) ($section['title'] ?? 'Frequently Asked Questions'),
                'description' => (string) ($section['description'] ?? ''),
            ],
            'items' => $items,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Section Q&A')
                    ->schema([
                        Forms\Components\TextInput::make('section.title')
                            ->label('Judul Section')
                            ->required()
                            ->maxLength(150),
                        Forms\Components\Textarea::make('section.description')
                            ->label('Deskripsi Section')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Repeater::make('items')
                    ->label('Daftar Pertanyaan')
                    ->schema([
                        Forms\Components\Hidden::make('key'),
                        Forms\Components\TextInput::make('question')
                            ->label('Pertanyaan')
                            ->required()
                            ->maxLength(500),
                        Forms\Components\TextInput::make('order')
                            ->label('Nomor / Urutan')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\Textarea::make('answer')
                            ->label('Jawaban')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Pertanyaan')
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
            ->getReference('faq');
        $section = is_array($data['section'] ?? null) ? $data['section'] : [];

        $reference->getChild('section')->set([
            'title' => trim((string) ($section['title'] ?? 'Frequently Asked Questions')),
            'description' => trim((string) ($section['description'] ?? '')),
        ]);

        $itemsReference = $reference->getChild('items');
        $existing = $itemsReference->getValue();
        $existing = is_array($existing) ? $existing : [];
        $submittedKeys = [];
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];

        foreach ($items as $item) {
            $key = trim((string) ($item['key'] ?? ''));
            $payload = [
                'question' => trim((string) ($item['question'] ?? '')),
                'answer' => trim((string) ($item['answer'] ?? '')),
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
            ->title('Q&A berhasil disimpan')
            ->success()
            ->send();
    }
}
