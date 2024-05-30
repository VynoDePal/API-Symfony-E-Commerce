<?php

namespace App\Service;

class Service
{
    private string $secretKey;

    public function __construct(string $secretKey)
    {
        if (empty($secretKey)) {
            throw new \InvalidArgumentException('La clé secrète doit être renseignée.');
        }
        $this->secretKey = $secretKey;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }
}