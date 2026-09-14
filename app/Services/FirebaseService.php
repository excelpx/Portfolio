<?php

namespace App\Services;

use Kreait\Firebase\Factory;

class FirebaseService
{
    protected $database;

    public function __construct()
    {
        $credentials = json_decode(
            env('FIREBASE_CREDENTIALS_JSON'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $factory = (new Factory)
            ->withServiceAccount($credentials)
            ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));

        $this->database = $factory->createDatabase();
    }

    public function getDatabase()
    {
        return $this->database;
    }
}