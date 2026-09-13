<?php

namespace App\Filament\Pages;

use App\Services\FirebaseService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Skills extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Skills';

    protected static ?string $title = 'Skills Website';

    protected static ?string $navigationGroup = 'Website';

    protected static string $view = 'filament.pages.skills';

    public ?array $data = [];

    public function mount(): void
    {
        $skills = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('skills')
            ->getValue();

        $items = [];

        if (is_array($skills)) {
            foreach ($skills as $key => $skill) {
                if (! is_array($skill)) {
                    continue;
                }

                $items[] = [
                    'key' => (string) $key,
                    'name' => (string) ($skill['name'] ?? ''),
                    'percentage' => (int) ($skill['percentage'] ?? 0),
                ];
            }
        }

        $this->form->fill(['skills' => $items]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('skills')
                    ->label('Daftar Skills')
                    ->schema([
                        Forms\Components\Hidden::make('key'),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Skill')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('percentage')
                            ->label('Persentase')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Skill')
                    ->reorderable(false)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $skills = is_array($data['skills'] ?? null) ? $data['skills'] : [];
        $reference = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('skills');
        $existing = $reference->getValue();
        $existing = is_array($existing) ? $existing : [];
        $submittedKeys = [];

        foreach ($skills as $skill) {
            $key = trim((string) ($skill['key'] ?? ''));
            $payload = [
                'name' => trim((string) ($skill['name'] ?? '')),
                'percentage' => (int) ($skill['percentage'] ?? 0),
            ];

            if ($key !== '' && array_key_exists($key, $existing)) {
                $reference->getChild($key)->set($payload);
                $submittedKeys[] = $key;
                continue;
            }

            $newSkill = $reference->push($payload);
            $submittedKeys[] = $newSkill->getKey();
        }

        foreach (array_keys($existing) as $key) {
            if (! in_array((string) $key, $submittedKeys, true)) {
                $reference->getChild($key)->remove();
            }
        }

        $this->mount();

        Notification::make()
            ->title('Skills berhasil disimpan')
            ->success()
            ->send();
    }
}
