<?php

namespace App\Service;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserService
{
    private TokenService $tokenService;
    private UserRepository $userRepository;

    public function __construct(TokenService $tokenService, UserRepository $userRepository)
    {
        $this->tokenService = $tokenService;
        $this->userRepository = $userRepository;
    }

    public function getUserFromRequest(Request $request)
    {
        $authHeader = $request->headers->get('authorization');

        if ($authHeader && preg_match('/^Bearer\s(.+)$/', $authHeader, $matches)) {
            $token = $matches[1];
        } else {
            return new JsonResponse(['error' => 'Invalid token 1'], Response::HTTP_UNAUTHORIZED);
        }

        $userId = $this->tokenService->decodeToken($token);
        if (!$userId) {
            return new JsonResponse(['error' => 'Invalid token 2'], Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return $user;
    }
}
