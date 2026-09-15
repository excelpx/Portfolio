<?php

namespace App\Services;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class CloudinaryService
{
    public function uploadTestimonialImage(UploadedFile $file): string
    {
        return $this->uploadImage($file, 'portfolio/testimonials', 'foto testimonial');
    }

    public function uploadProfileImage(UploadedFile $file): string
    {
        return $this->uploadImage($file, 'portfolio/profile', 'foto profile');
    }

    public function uploadImage(
        UploadedFile $file,
        string $folder,
        string $fileLabel = 'gambar'
    ): string {
        $cloudinaryUrl = trim((string) config('services.cloudinary.url'));
        $cloudName = (string) config('services.cloudinary.cloud_name');
        $apiKey = (string) config('services.cloudinary.api_key');
        $apiSecret = (string) config('services.cloudinary.api_secret');

        if (
            $cloudinaryUrl === '' &&
            ($cloudName === '' || $apiKey === '' || $apiSecret === '')
        ) {
            throw new RuntimeException(
                'Konfigurasi Cloudinary belum lengkap.'
            );
        }

        if ($cloudinaryUrl !== '') {
            Configuration::instance($cloudinaryUrl);
        } else {
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => $cloudName,
                    'api_key' => $apiKey,
                    'api_secret' => $apiSecret,
                ],
                'url' => [
                    'secure' => true,
                ],
            ]);
        }

        $path = $file->getRealPath();

        if ($path === false) {
            throw new RuntimeException(
                "File {$fileLabel} tidak dapat dibaca."
            );
        }

        $result = (new UploadApi())->upload($path, [
            'folder' => $folder,
            'resource_type' => 'image',
        ]);

        $url = $result['secure_url'] ?? null;

        if (! is_string($url) || $url === '') {
            throw new RuntimeException(
                "Cloudinary tidak mengembalikan URL {$fileLabel}."
            );
        }

        return $url;
    }
}