<?php

namespace App\Service;

class TokenService
{
    private Service $service;
    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Create token with id of user
     *
     * @param int $id
     * @return string
     */
    public function createToken(int $id): string
    {
        $secretKey = $this->service->getSecretKey();
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];
        $payload = [
            'sub' => $id,
            'iat' => time(),
            'exp' => time() + 3600,
        ];
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secretKey, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    /**
     * Verify token
     *
     * @param string $token
     * @return User|null
     */
    public function decodeToken(string $token): ?int
    {
        $secretKey = $this->service->getSecretKey();
        $tokenParts = explode(".", $token);
        if (count($tokenParts) !== 3) {
            return null;
        }

        $base64UrlHeader = $tokenParts[0];
        $base64UrlPayload = $tokenParts[1];
        $base64UrlSignature = $tokenParts[2];

        $header = json_decode(base64_decode(strtr($base64UrlHeader, '-_', '+/')), true);
        if (!isset($header['alg']) || $header['alg'] !== 'HS256') {
            return null;
        }

        $payload = json_decode(base64_decode(strtr($base64UrlPayload, '-_', '+/')), true);
        if (!isset($payload['sub'])) {
            return null;
        }

        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secretKey, true);
        $base64UrlExpectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($expectedSignature));

        if (!hash_equals($base64UrlExpectedSignature, $base64UrlSignature)) {
            return null;
        }

        return $payload['sub'];
    }

}