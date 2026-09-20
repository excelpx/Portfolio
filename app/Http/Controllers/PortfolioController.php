<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Services\FirebaseService;

class PortfolioController extends Controller
{
    protected $database;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->database = $firebaseService->getDatabase();
    }

    // READ
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Ambil data portfolio dari cache
        |--------------------------------------------------------------------------
        | Cache berlaku selama 60 detik.
        | Jadi Firebase tidak perlu dipanggil setiap kali halaman dibuka.
        */

        $data = Cache::remember(
            'portfolio_data',
            now()->addSeconds(60),
            function () {
                return $this->database
                    ->getReference('/')
                    ->getValue();
            }
        );

        $data = is_array($data) ? $data : [];

        /*
        |--------------------------------------------------------------------------
        | Pecah data Firebase menjadi masing-masing section
        |--------------------------------------------------------------------------
        */

        $profile = $data['profile'] ?? [];
        $hero = $data['hero'] ?? [];
        $projects = $data['projects'] ?? [];
        $skills = $data['skills'] ?? [];
        $categories = $data['categories'] ?? [];
        $resume = $data['resume'] ?? [];
        $services = $data['services'] ?? [];
        $statistics = $data['statistics'] ?? [];
        $toolsTechnologies = $data['tools_technologies'] ?? [];
        $pricing = $data['pricing'] ?? [];
        $faq = $data['faq'] ?? [];
        $testimonials = $data['testimonials'] ?? [];
        $contact = $data['contact'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Tampilkan website
        |--------------------------------------------------------------------------
        */

        return view('index', [
            'profile' => is_array($profile) ? $profile : [],
            'hero' => is_array($hero) ? $hero : [],
            'projects' => is_array($projects) ? $projects : [],
            'skills' => is_array($skills) ? $skills : [],
            'categories' => is_array($categories) ? $categories : [],
            'resume' => is_array($resume) ? $resume : [],
            'services' => is_array($services) ? $services : [],
            'statistics' => is_array($statistics) ? $statistics : [],
            'toolsTechnologies' => is_array($toolsTechnologies) ? $toolsTechnologies : [],
            'pricing' => is_array($pricing) ? $pricing : [],
            'faq' => is_array($faq) ? $faq : [],
            'testimonials' => is_array($testimonials) ? $testimonials : [],
            'contact' => is_array($contact) ? $contact : [],
        ]);
    }

    // CONTACT
    public function sendContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return $this->contactError(
                $request,
                $validator->errors()->first(),
                422
            );
        }

        $validated = $validator->validated();

        $apiUrl = trim(
            (string) config('services.contact_api_url')
        );

        if (
            $apiUrl === '' ||
            filter_var($apiUrl, FILTER_VALIDATE_URL) === false
        ) {
            Log::error('Contact API URL is not configured.');

            return $this->contactError(
                $request,
                'Pesan tidak dapat dikirim saat ini. Silakan coba lagi nanti.',
                503
            );
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(15)
                ->connectTimeout(5)
                ->post($apiUrl, $validated);

            $responseData = $response->json();

            if (
                ! $response->successful() ||
                ! is_array($responseData) ||
                ($responseData['success'] ?? false) !== true
            ) {
                Log::warning(
                    'Contact API rejected the message.',
                    [
                        'status' => $response->status(),
                        'response_is_json' => is_array($responseData),
                    ]
                );

                return $this->contactError(
                    $request,
                    'Pesan tidak dapat dikirim saat ini. Silakan coba lagi nanti.',
                    502
                );
            }
        } catch (\Throwable $exception) {
            Log::error(
                'Contact API request failed.',
                [
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ]
            );

            return $this->contactError(
                $request,
                'Pesan tidak dapat dikirim saat ini. Silakan coba lagi nanti.',
                500
            );
        }

        if (
            $request->expectsJson() ||
            $request->ajax()
        ) {
            return response('OK');
        }

        return back()->with(
            'contact_success',
            'Your message has been sent. Thank you!'
        );
    }

    // CONTACT ERROR
    private function contactError(
        Request $request,
        string $message,
        int $status
    ) {
        if (
            $request->expectsJson() ||
            $request->ajax()
        ) {
            return response($message, $status);
        }

        return back()
            ->withInput()
            ->with('contact_error', $message);
    }

    // CREATE
    public function store(Request $request)
    {
        $reference = $this->database
            ->getReference('projects');

        $newProject = $reference->push([
            'title' => 'Aplikasi Manajemen Tugas',
            'tech_stack' => [
                'Laravel',
                'Firebase',
                'Vue'
            ],
            'status' => 'Selesai'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Hapus cache setelah data projects berubah
        |--------------------------------------------------------------------------
        */

        Cache::forget('portfolio_data');

        return response()->json([
            'message' => 'Proyek berhasil ditambahkan',
            'id' => $newProject->getKey()
        ]);
    }
}