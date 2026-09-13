<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ImageKitService
{
    /**
     * Upload an image to ImageKit using the server-side private key.
     */
    public function upload(UploadedFile $file, string $folder = 'portfolio'): string
    {
        $publicKey = trim((string) config('services.imagekit.public_key'));
        $privateKey = trim((string) config('services.imagekit.private_key'));
        $urlEndpoint = trim((string) config('services.imagekit.url_endpoint'));

        if ($publicKey === '' || $privateKey === '') {
            throw new RuntimeException(
                'Konfigurasi credential ImageKit belum lengkap.'
            );
        }

        if (
            $urlEndpoint === '' ||
            filter_var($urlEndpoint, FILTER_VALIDATE_URL) === false
        ) {
            throw new RuntimeException(
                'ImageKit URL endpoint belum dikonfigurasi dengan benar.'
            );
        }

        $realPath = $file->getRealPath();

        if ($realPath === false || ! is_readable($realPath)) {
            throw new RuntimeException(
                'File gambar sementara tidak dapat dibaca.'
            );
        }

        $contents = file_get_contents($realPath);

        if ($contents === false) {
            throw new RuntimeException(
                'Isi file gambar tidak dapat dibaca.'
            );
        }

        $response = Http::acceptJson()
            ->withBasicAuth($privateKey, '')
            ->timeout(60)
            ->connectTimeout(20)
            ->retry(2, 3000)
            ->attach(
                'file',
                $contents,
                $file->getClientOriginalName()
            )
            ->post(
                'https://upload.imagekit.io/api/v1/files/upload',
                [
                    'fileName' => Str::uuid() . '.' . $file->getClientOriginalExtension(),
                    'folder' => '/' . trim($folder, '/'),
                    'useUniqueFileName' => 'true',
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Upload ImageKit gagal dengan HTTP status ' .
                $response->status() . '.'
            );
        }

        $data = $response->json();

        if (
            ! is_array($data) ||
            ! isset($data['url']) ||
            ! is_string($data['url'])
        ) {
            throw new RuntimeException(
                'Response ImageKit tidak memiliki URL gambar yang valid.'
            );
        }

        $imageUrl = trim($data['url']);

        if (
            $imageUrl === '' ||
            filter_var($imageUrl, FILTER_VALIDATE_URL) === false
        ) {
            throw new RuntimeException(
                'ImageKit mengembalikan URL gambar yang tidak valid.'
            );
        }

        return $imageUrl;
    }
}