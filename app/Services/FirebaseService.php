<?php

namespace App\Services;

use Kreait\Firebase\Factory;

class FirebaseService
{
    protected $database;

    public function __construct()
    {
        $factory = new Factory;

        /*
         * Local:
         * Menggunakan storage/app/firebase-credentials.json
         *
         * Production/Vercel:
         * Menggunakan FIREBASE_CREDENTIALS_JSON dari Environment Variable
         */

        $credentialsJson = env('FIREBASE_CREDENTIALS_JSON');

        if ($credentialsJson) {
            $factory = $factory->withServiceAccount(
                json_decode($credentialsJson, true)
            );
        } else {
            $factory = $factory->withServiceAccount(
                storage_path('app/firebase-credentials.json')
            );
        }

        $factory = $factory->withDatabaseUri(
            env('FIREBASE_DATABASE_URL')
        );

        $this->database = $factory->createDatabase();
    }

    public function getDatabase()
    {
        return $this->database;
    }
}