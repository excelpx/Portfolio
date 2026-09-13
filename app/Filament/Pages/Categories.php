<?php

namespace App\Filament\Pages;

use App\Services\FirebaseService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Categories extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Categories';

    protected static ?string $title = 'Portfolio Categories';

    protected static ?string $navigationGroup = 'Website';

    protected static string $view = 'filament.pages.categories';

    public ?array $data = [];

    public function mount(): void
    {
        $categories = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('categories')
            ->getValue();
        $items = [];

        if (is_array($categories)) {
            foreach ($categories as $key => $category) {
                if (! is_array($category)) {
                    continue;
                }

                $items[] = [
                    'key' => (string) $key,
                    'name' => (string) ($category['name'] ?? ''),
                    'slug' => (string) ($category['slug'] ?? ''),
                ];
            }
        }

        $this->form->fill(['categories' => $items]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('categories')
                    ->label('Daftar Kategori')
                    ->schema([
                        Forms\Components\Hidden::make('key'),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(80),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(80)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/'),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Category')
                    ->reorderable(false)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $categories = is_array($data['categories'] ?? null) ? $data['categories'] : [];
        $database = app(FirebaseService::class)->getDatabase();
        $reference = $database->getReference('categories');
        $existing = $reference->getValue();
        $existing = is_array($existing) ? $existing : [];
        $submittedKeys = [];
        $slugChanges = [];

        foreach ($categories as $category) {
            $key = trim((string) ($category['key'] ?? ''));
            $name = trim((string) ($category['name'] ?? ''));
            $slug = strtolower(trim((string) ($category['slug'] ?? '')));
            $payload = ['name' => $name, 'slug' => $slug];

            if ($key !== '' && array_key_exists($key, $existing)) {
                $oldSlug = (string) ($existing[$key]['slug'] ?? '');
                if ($oldSlug !== '' && $oldSlug !== $slug) {
                    $slugChanges[$oldSlug] = $slug;
                }
                $reference->getChild($key)->set($payload);
                $submittedKeys[] = $key;
                continue;
            }

            $newCategory = $reference->push($payload);
            $submittedKeys[] = $newCategory->getKey();
        }

        $projectReference = $database->getReference('projects');
        $projects = $projectReference->getValue();

        if (is_array($projects)) {
            foreach ($slugChanges as $oldSlug => $newSlug) {
                foreach ($projects as $key => $project) {
                    if (is_array($project) && ($project['category'] ?? '') === $oldSlug) {
                        $projectReference->getChild((string) $key)->getChild('category')->set($newSlug);
                    }
                }
            }
        }

        foreach (array_keys($existing) as $key) {
            if (in_array((string) $key, $submittedKeys, true)) {
                continue;
            }

            $slug = (string) ($existing[$key]['slug'] ?? '');
            $used = is_array($projects) && collect($projects)->contains(
                fn ($project): bool => is_array($project) && ($project['category'] ?? '') === $slug
            );

            if ($used) {
                Notification::make()
                    ->title('Kategori tidak dapat dihapus')
                    ->body('Pindahkan project dari kategori ini terlebih dahulu.')
                    ->danger()
                    ->send();
                $this->mount();
                return;
            }

            $reference->getChild((string) $key)->remove();
        }

        $this->mount();

        Notification::make()
            ->title('Categories berhasil disimpan')
            ->success()
            ->send();
    }
}
