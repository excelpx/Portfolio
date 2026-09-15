<?php

namespace App\Filament\Pages;

use App\Services\FirebaseService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SocialMedia extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationLabel = 'Social Media';

    protected static ?string $title = 'Social Media Website';

    protected static ?string $navigationGroup = 'Website';

    protected static string $view = 'filament.pages.social-media';

    public ?array $data = [];

    /**
     * Load data dari Firebase
     */
    public function mount(): void
    {
        $database = app(FirebaseService::class)->getDatabase();

        $socialMedia = $database
            ->getReference('social_media')
            ->getValue();

        $socialMedia = is_array($socialMedia)
            ? $socialMedia
            : [];

        $items = [];

        foreach ($socialMedia as $key => $social) {
            if (! is_array($social)) {
                continue;
            }

            $items[] = [
                'key' => (string) $key,

                'name' => (string) (
                    $social['name'] ?? ''
                ),

                'icon_url' => (string) (
                    $social['icon_url'] ?? ''
                ),

                'url' => (string) (
                    $social['url'] ?? ''
                ),

                'order' => (int) (
                    $social['order'] ?? 0
                ),

                'active' => (bool) (
                    $social['active'] ?? true
                ),
            ];
        }

        // Urutkan berdasarkan order
        usort(
            $items,
            static function (array $a, array $b): int {
                return $a['order'] <=> $b['order'];
            }
        );

        $this->form->fill([
            'items' => $items,
        ]);
    }

    /**
     * Form Social Media
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('items')
                    ->label('Daftar Social Media')
                    ->schema([

                        /*
                        |--------------------------------------------------------------------------
                        | Firebase Key
                        |--------------------------------------------------------------------------
                        */
                        Forms\Components\Hidden::make('key'),

                        /*
                        |--------------------------------------------------------------------------
                        | Nama Platform
                        |--------------------------------------------------------------------------
                        */
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Platform')
                            ->placeholder('Instagram')
                            ->required()
                            ->maxLength(100),

                        /*
                        |--------------------------------------------------------------------------
                        | URL Icon
                        |--------------------------------------------------------------------------
                        */
                        Forms\Components\TextInput::make('icon_url')
                            ->label('URL Icon')
                            ->placeholder(
                                'https://example.com/instagram.png'
                            )
                            ->url()
                            ->required()
                            ->maxLength(1000)
                            ->helperText(
                                'Masukkan URL gambar icon social media.'
                            )
                            ->columnSpanFull(),

                        /*
                        |--------------------------------------------------------------------------
                        | URL Social Media
                        |--------------------------------------------------------------------------
                        */
                        Forms\Components\TextInput::make('url')
                            ->label('URL Social Media')
                            ->placeholder(
                                'https://instagram.com/username'
                            )
                            ->url()
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),

                        /*
                        |--------------------------------------------------------------------------
                        | Urutan
                        |--------------------------------------------------------------------------
                        */
                        Forms\Components\TextInput::make('order')
                            ->label('Urutan')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        /*
                        |--------------------------------------------------------------------------
                        | Status
                        |--------------------------------------------------------------------------
                        */
                        Forms\Components\Toggle::make('active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Social Media')
                    ->reorderable(false)
                    ->collapsible()
                    ->itemLabel(
                        fn (array $state): ?string =>
                            ! empty($state['name'])
                                ? $state['name']
                                : 'Social Media Baru'
                    )
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    /**
     * Simpan data ke Firebase
     */
    public function save(): void
    {
        $data = $this->form->getState();

        $reference = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('social_media');

        $existing = $reference->getValue();

        $existing = is_array($existing)
            ? $existing
            : [];

        $submittedKeys = [];

        $items = is_array($data['items'] ?? null)
            ? $data['items']
            : [];

        /*
        |--------------------------------------------------------------------------
        | Simpan setiap Social Media
        |--------------------------------------------------------------------------
        */
        foreach ($items as $item) {

            $key = trim(
                (string) ($item['key'] ?? '')
            );

            $payload = [
                'name' => trim(
                    (string) ($item['name'] ?? '')
                ),

                'icon_url' => trim(
                    (string) ($item['icon_url'] ?? '')
                ),

                'url' => trim(
                    (string) ($item['url'] ?? '')
                ),

                'order' => max(
                    0,
                    (int) ($item['order'] ?? 0)
                ),

                'active' => (bool) (
                    $item['active'] ?? false
                ),
            ];

            /*
            |--------------------------------------------------------------------------
            | Update data lama
            |--------------------------------------------------------------------------
            */
            if (
                $key !== ''
                && array_key_exists($key, $existing)
            ) {
                $reference
                    ->getChild($key)
                    ->set($payload);

                $submittedKeys[] = $key;
            }

            /*
            |--------------------------------------------------------------------------
            | Tambah data baru
            |--------------------------------------------------------------------------
            */
            else {
                $newItem = $reference->push($payload);

                $submittedKeys[] = $newItem->getKey();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Hapus data yang sudah dihapus dari Repeater
        |--------------------------------------------------------------------------
        */
        foreach (array_keys($existing) as $key) {

            if (! in_array(
                (string) $key,
                $submittedKeys,
                true
            )) {
                $reference
                    ->getChild($key)
                    ->remove();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Refresh Form
        |--------------------------------------------------------------------------
        */
        $this->mount();

        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */
        Notification::make()
            ->title('Social Media berhasil disimpan')
            ->success()
            ->send();
    }
}