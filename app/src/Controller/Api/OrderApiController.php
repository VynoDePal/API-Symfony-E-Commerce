<?php

namespace App\Controller\Api;

use App\Service\UserService;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use OpenApi\Attributes as OA;

class OrderApiController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Renvoie les commandes de l'utilisateur actuel
     */
    #[Route('/api/orders', name: 'get_orders_user', methods: ['GET'])]
    #[OA\Tag(name: 'Orders')]
    #[OA\Response(response: 200, description: 'Returns all orders of the current user')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'Not found')]
    public function getOrdersUser(Request $request, OrderRepository $orderRepository, NormalizerInterface $normalizer): Response
    {
        /**
         * Trouve l'utilisateur actuel en fonction de sa session
         */
        // Utilisation du service pour obtenir l'utilisateur
        $result = $this->userService->getUserFromRequest($request);
        if ($result instanceof JsonResponse) {
            return $result;
        }

        $user = $result;

        /**
         * Recherche toutes les commandes de l'utilisateur actuel
         */
        $orders = $orderRepository->findBy(['user' => $user]);

        $ordersJson = $normalizer->normalize($orders, 'json', ['groups' => ['orders', 'product:read']]);

        return $this->json($ordersJson);
    }


    /**
     * Renvoie une commande de l'utilisateur actuel
     */
    #[Route('/api/orders/{id}', name: 'get_order_user', methods: ['GET'])]
    #[OA\Tag(name: 'Orders')]
    #[OA\Response(response: 200, description: 'Returns an order of the current user')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'Not found')]
    public function getOrderUser(Request $request, OrderRepository $orderRepository, NormalizerInterface $normalizer, int $id): Response
    {
        // Utilisation du service pour obtenir l'utilisateur
        $result = $this->userService->getUserFromRequest($request);
        if ($result instanceof JsonResponse) {
            return $result;
        }

        $user = $result;

        /**
         * Recherche la commande correspondant à l'id envoyé en paramètre
         */
        $order = $orderRepository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$order) {
            return $this->json(['message' => 'Order not found'], 404);
        }

        $orderJson = $normalizer->normalize($order, 'json', ['groups' => ['orders', 'product:read']]);

        return $this->json($orderJson);
    }

}
