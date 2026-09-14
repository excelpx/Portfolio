<?php

namespace App\Services;

use Kreait\Firebase\Factory;

class FirebaseService
{
    protected $database;

    public function __construct()
    {
        $credentialFile = storage_path('app/firebase-credentials.json');
        $credentialsJson = env('FIREBASE_CREDENTIALS_JSON');

        /*
         * LOCAL
         * Jika file credential tersedia, gunakan file.
         */
        if (file_exists($credentialFile)) {
            $factory = (new Factory)
                ->withServiceAccount($credentialFile)
                ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));
        }

        /*
         * PRODUCTION / VERCEL
         * Jika file tidak tersedia, gunakan Environment Variable.
         */
        elseif (!empty($credentialsJson)) {
            $credentials = json_decode(
                $credentialsJson,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            $factory = (new Factory)
                ->withServiceAccount($credentials)
                ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));
        }

        /*
         * Tidak ada credential.
         */
        else {
            throw new \RuntimeException(
                'Firebase credentials tidak ditemukan.'
            );
        }

        $this->database = $factory->createDatabase();
    }

    public function getDatabase()
    {
        return $this->database;
    }
}