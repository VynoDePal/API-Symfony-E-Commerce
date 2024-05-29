<?php

namespace App\Controller;

use App\Service\UserService;
use App\Entity\Product;
use App\Service\SessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use OpenApi\Attributes as OA;
use Doctrine\Persistence\ManagerRegistry;

class SessionController extends AbstractController
{
    private UserService $userService;

    /**
     * Constructeur avec propriétés privées en lecture seule pour SessionService et ManagerRegistry
     */
    public function __construct(
        private readonly SessionService $sessionService,
        private readonly ManagerRegistry $doctrine,
        UserService $userService
    ) {
        $this->userService = $userService;
    }

    /**
     * Route pour l'obtention de paniers d'achat avec le paramètre SessionInterface et le type de retour Response
     */
    #[Route('api/carts', name: 'api_get_shopping_carts', methods: ['GET'])]
    #[OA\Tag(name: 'Carts')]
    #[OA\Response(response: 200, description: 'Returns a list of carts')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'Cart not found')]
    public function getShoppingCart(SessionInterface $session): Response
    {
        /**
         * Récupére les paniers d'achat en utilisant SessionInterface
         */
        $carts = $this->sessionService->getShoppingCart($session);
        
        return $this->json($carts);
    }


    /**
     * Route pour l'ajout d'un article à un panier d'achat avec des paramètres SessionInterface et int et un type de retour Response
     */
    #[Route('/api/carts/{id}', name: 'api_add_item_to_shopping_cart', methods: ['GET','POST'])]
    #[OA\Tag(name: 'Carts')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Product id')]
    #[OA\Response(response: 201, description: 'Item added to cart')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'Product or cart not found')]
    // #[Security(name: "Bearer")]
    public function addItemToShoppingCart(Request $request, int $id): Response
    {
        // Utilisation du service pour obtenir l'utilisateur
        $result = $this->userService->getUserFromRequest($request);
        if ($result instanceof JsonResponse) {
            return $result;
        }

        $user = $result;
        /**
         * Recherche d'un produit par son numéro d'identification à l'aide de ManagerRegistry
         */
        $product = $this->doctrine->getRepository(Product::class)->find($id);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        /**
         * Ajoute un article au panier en utilisant SessionService
         */
        $this->sessionService->addItemToShoppingCart($product);

        return $this->json(['message' => 'Item added to cart'], Response::HTTP_CREATED);
    }

    /**
     * Route pour retirer un article d'un panier d'achat avec les paramètres SessionInterface et int et le type de retour Response
     */
    #[Route('/api/cartss/{id}', name: 'api_remove_item_from_shopping_cart', methods: ['GET', 'DELETE'])]
    #[OA\Tag(name: 'Carts')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Product id')]
    #[OA\Response(response: 200, description: 'Item removed from cart')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'Product or cart not found')]
    public function removeItemFromShoppingCart(Request $request, int $id): Response
    {
        // Utilisation du service pour obtenir l'utilisateur
        $result = $this->userService->getUserFromRequest($request);
        if ($result instanceof JsonResponse) {
            return $result;
        }

        $user = $result;

        $product = $this->doctrine->getRepository(Product::class)->find($id);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        /**
         * Supprime un article du panier d'achat en utilisant SessionService
         */
        $this->sessionService->removeItemFromShoppingCart($product);

        return $this->json(['message' => 'Item removed from cart'], Response::HTTP_OK);
    }

    /**
     * Route pour la validation d'un panier d'achat avec le paramètre SessionInterface et le type de retour Response
     */
    #[Route('/api/validate', name: 'api_validate_shopping_cart', methods: ['GET','POST'])]
    #[OA\Tag(name: 'Carts')]
    #[OA\Response(response: 201, description: 'Cart validated')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'Cart not found')]
    public function validateShoppingCart(RequestStack $requestStack, Request $request): Response
    {
        $session = $requestStack->getSession();
        /**
         * Vérifie si le panier est vide ou si l'utilisateur n'existe pas
         * puis le transforme en commande si tout est OK
         */
        $order = $this->sessionService->validateShoppingCartAsOrder($session, $request);

        if ($order) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($order);
            $entityManager->flush();

            return $this->json(['message' => 'Cart validated as order'], Response::HTTP_CREATED);
        }

        return $this->json(['message' => 'Cart not found'], Response::HTTP_NOT_FOUND);
    }

}
