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
use Psr\Log\LoggerInterface;
use OpenApi\Attributes as OA;

class OrderApiController extends AbstractController
{
    private UserService $userService;
    private LoggerInterface $logger;

    public function __construct(UserService $userService, LoggerInterface $logger)
    {
        $this->userService = $userService;
        $this->logger = $logger;
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
        $this->logger->info('Récupération des commandes de l\'utilisateur actuel.');

        try {
            // Utilisation du service pour obtenir l'utilisateur
            $result = $this->userService->getUserFromRequest($request);
            if ($result instanceof JsonResponse) {
                return $result;
            }

            $user = $result;

            // Recherche toutes les commandes de l'utilisateur actuel
            $orders = $orderRepository->findBy(['user' => $user]);

            if (empty($orders)) {
                $this->logger->warning('L\'utilisateur n\'a pas de commandes');
            }

            $ordersJson = $normalizer->normalize($orders, 'json', ['groups' => ['orders', 'product:read']]);
            $this->logger->info('Les commandes ont été récupérées avec succès.');

            return $this->json($ordersJson);
        } catch (\Exception $e) {
            $this->logger->error('Une erreur s\'est produite lors de la recherche des commandes : ' . $e->getMessage());
            return $this->json(['error' => 'Une erreur s\'est produite lors de la recherche des commandes.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
        $this->logger->info('Récupération de la commande n°' . $id . ' de l\'utilisateur actuel.');

        try {
            // Utilisation du service pour obtenir l'utilisateur
            $result = $this->userService->getUserFromRequest($request);
            if ($result instanceof JsonResponse) {
                return $result;
            }

            $user = $result;

            // Recherche la commande correspondant à l'id envoyé en paramètre
            $order = $orderRepository->findOneBy(['id' => $id, 'user' => $user]);

            if (!$order) {
                $this->logger->warning('La commande n\'a pas été trouvé');
                return $this->json(['message' => 'Order not found'], 404);
            }

            $orderJson = $normalizer->normalize($order, 'json', ['groups' => ['orders', 'product:read']]);
            $this->logger->info('La commande a été trouvé avec succès.');

            return $this->json($orderJson);
        } catch (\Exception $e) {
            $this->logger->error('Une erreur s\'est produite lors de la recherche de la commande n°' . $id . ' : ' . $e->getMessage());
            return $this->json(['error' => 'Une erreur s\'est produite lors de la recherche de la commande.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
