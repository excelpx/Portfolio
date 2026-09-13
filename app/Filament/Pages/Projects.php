<?php

namespace App\Filament\Pages;

use App\Services\FirebaseService;
use App\Services\ImageKitService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class Projects extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Projects';

    protected static ?string $title = 'Projects Website';

    protected static ?string $navigationGroup = 'Website';

    protected static string $view = 'filament.pages.projects';

    public ?array $data = [];

    public function mount(): void
    {
        $database = app(FirebaseService::class)->getDatabase();
        $projects = $database->getReference('projects')->getValue();
        $categories = $database->getReference('categories')->getValue();
        $categorySlugs = [];

        if (is_array($categories)) {
            foreach ($categories as $category) {
                if (is_array($category) && ! empty($category['slug'])) {
                    $categorySlugs[(string) $category['name']] = (string) $category['slug'];
                }
            }
        }

        $items = [];

        if (is_array($projects)) {
            foreach ($projects as $key => $project) {
                if (! is_array($project)) {
                    continue;
                }

                $techStack = $project['tech_stack'] ?? [];
                $techStack = is_array($techStack) ? array_values($techStack) : [];

                $items[] = [
                    'key' => (string) $key,
                    'title' => (string) ($project['title'] ?? ''),
                    'category' => $categorySlugs[(string) ($project['category'] ?? '')] ?? (string) ($project['category'] ?? ''),
                    'description' => (string) ($project['description'] ?? ''),
                    'tech_stack' => $techStack,
                    'image' => (string) ($project['image'] ?? ''),
                    'image_upload' => null,
                    'project_url' => (string) ($project['project_url'] ?? ''),
                    'instagram_url' => (string) ($project['instagram_url'] ?? ''),
                    'instagram_aspect_ratio' => (string) ($project['instagram_aspect_ratio'] ?? '4:5'),
                    'status' => (string) ($project['status'] ?? ''),
                ];
            }
        }

        $this->form->fill(['projects' => $items]);
    }

    public function form(Form $form): Form
    {
        $categories = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('categories')
            ->getValue();
        $categoryOptions = [];

        if (is_array($categories)) {
            foreach ($categories as $category) {
                if (is_array($category) && ! empty($category['slug']) && ! empty($category['name'])) {
                    $categoryOptions[(string) $category['slug']] = (string) $category['name'];
                }
            }
        }

        return $form
            ->schema([
                Forms\Components\Repeater::make('projects')
                    ->label('Daftar Projects')
                    ->schema([
                        Forms\Components\Hidden::make('key'),

                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(150),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Selesai' => 'Selesai',
                                'Sedang Dikerjakan' => 'Sedang Dikerjakan',
                                'Draft' => 'Draft',
                            ])
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->options($categoryOptions)
                            ->searchable()
                            ->required()
                            ->disabled(fn (): bool => $categoryOptions === []),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TagsInput::make('tech_stack')
                            ->label('Tech Stack')
                            ->placeholder('Tambah teknologi')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('image')
                            ->label('URL atau Path Gambar')
                            ->placeholder('img/portfolio/app-1.jpg atau https://...')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('image_upload')
                            ->label('Upload Image')
                            ->image()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(10240)
                            ->storeFiles(false)
                            ->helperText('Opsional. JPG, JPEG, PNG, atau WEBP. Maksimal 10 MB.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('project_url')
                            ->label('URL Project')
                            ->url()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url()
                            ->placeholder('https://www.instagram.com/p/XXXXXXXX/')
                            ->helperText('Gunakan URL Instagram Post atau Reel.')
                            ->rules([
                                'regex:/^https:\/\/(www\.)?instagram\.com\/(p|reel)\/[A-Za-z0-9_-]+\/?(?:\?.*)?$/i',
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Select::make('instagram_aspect_ratio')
                            ->label('Instagram Aspect Ratio')
                            ->options([
                                '4:5' => '4:5 (Portrait)',
                                '1:1' => '1:1 (Square)',
                                '9:16' => '9:16 (Vertical)',
                                '16:9' => '16:9 (Landscape)',
                            ])
                            ->default('4:5')
                            ->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Project')
                    ->reorderable(false)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $projects = is_array($data['projects'] ?? null) ? $data['projects'] : [];
        $reference = app(FirebaseService::class)
            ->getDatabase()
            ->getReference('projects');
        $existing = $reference->getValue();
        $existing = is_array($existing) ? $existing : [];
        $submittedKeys = [];
        $payloads = [];

        foreach ($projects as $project) {
            $key = trim((string) ($project['key'] ?? ''));
            $image = trim((string) ($project['image'] ?? ''));
            $uploadedFile = $this->getUploadedFile($project['image_upload'] ?? null);

            if ($uploadedFile instanceof UploadedFile) {
                try {
                                $image = app(ImageKitService::class)->upload($uploadedFile, 'portfolio');
            } catch (ConnectionException $e) {
                Notification::make()
                    ->title('Upload gambar project gagal')
                    ->body('Koneksi ke ImageKit gagal: ' . $e->getMessage())
                    ->danger()
                    ->persistent()
                    ->send();

                return;
            } catch (RuntimeException $e) {
                Notification::make()
                    ->title('Upload gambar project gagal')
                    ->body($e->getMessage())
                    ->danger()
                    ->persistent()
                    ->send();

                return;
            }
    }

            $instagramAspectRatio = (string) ($project['instagram_aspect_ratio'] ?? '4:5');
            $techStack = is_array($project['tech_stack'] ?? null)
                ? array_values(array_filter(array_map('trim', $project['tech_stack'])))
                : [];
            $payload = [
                'title' => trim((string) ($project['title'] ?? '')),
                'category' => trim((string) ($project['category'] ?? '')),
                'description' => trim((string) ($project['description'] ?? '')),
                'tech_stack' => $techStack,
                'image' => $image,
                'project_url' => trim((string) ($project['project_url'] ?? '')),
                'instagram_url' => trim((string) ($project['instagram_url'] ?? '')),
                'instagram_aspect_ratio' => in_array(
                    $instagramAspectRatio,
                    ['4:5', '1:1', '9:16', '16:9'],
                    true
                ) ? $instagramAspectRatio : '4:5',
                'status' => trim((string) ($project['status'] ?? '')),
            ];

            $payloads[] = [
                'key' => $key,
                'payload' => $payload,
            ];
        }

        foreach ($payloads as $item) {
            $key = $item['key'];
            $payload = $item['payload'];

            if ($key !== '' && array_key_exists($key, $existing)) {
                $reference->getChild($key)->set($payload);
                $submittedKeys[] = $key;
                continue;
            }

            $newProject = $reference->push($payload);
            $submittedKeys[] = $newProject->getKey();
        }

        foreach (array_keys($existing) as $key) {
            if (! in_array((string) $key, $submittedKeys, true)) {
                $reference->getChild($key)->remove();
            }
        }

        $this->mount();

        Notification::make()
            ->title('Projects berhasil disimpan')
            ->success()
            ->send();
    }

    private function getUploadedFile(mixed $value): ?UploadedFile
    {
        if ($value instanceof UploadedFile) {
            return $value;
        }

        if (is_array($value)) {
            $firstValue = reset($value);

            return $firstValue instanceof UploadedFile ? $firstValue : null;
        }

        return null;
    }
}
