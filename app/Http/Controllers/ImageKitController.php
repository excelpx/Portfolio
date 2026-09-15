<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ImageKitController extends Controller
{
    public function auth(): JsonResponse
    {
        $privateKey = trim(
            (string) config('services.imagekit.private_key')
        );

        if ($privateKey === '') {
            return response()->json([
                'message' => 'ImageKit private key belum dikonfigurasi.'
            ], 500);
        }

        $token = Str::uuid()->toString();
        $expire = time() + 3600;

        $signature = hash_hmac(
            'sha1',
            $token . $expire,
            $privateKey
        );

        return response()->json([
            'token' => $token,
            'expire' => $expire,
            'signature' => $signature,
        ]);
    }
}