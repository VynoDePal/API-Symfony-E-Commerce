<?php

namespace App\Controller\Api;

use App\Service\UserService;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;

class ProductApiController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Renvoie une liste de produits
     */
    #[Route('/api/products', name: 'api_get_products', methods: ['GET'])]
    #[OA\Tag(name: 'Products')]
    #[OA\Response(response: 200, description: 'Returns a list of products')]
    #[OA\Response(response: 404, description: 'Product not found')]
    public function getProducts(ProductRepository $productRepository, NormalizerInterface $normalizer): Response
    {
        $products = $productRepository->findAll();

        /**
         * Normalise les données des produits au format JSON
         */
        $serializedProducts = $normalizer->normalize($products, 'json', ['groups' => 'product:read']);

        return $this->json($serializedProducts);
    }

    /**
     * Renvoie les détails du produit
     */
    #[Route('/api/products/{id}', name: 'api_get_product', methods: ['GET'])]
    #[OA\Tag(name: 'Products')]
    #[OA\Response(response: 201, description: 'Returns the product details')]
    #[OA\Response(response: 404, description: 'Product not found')]
    public function getProduct(ProductRepository $productRepository, NormalizerInterface $normalizer, string $id): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            return $this->json([
                'error' => 'Product not found'
            ], 404);
        }

        $serializedProduct = $normalizer->normalize($product, 'json', ['groups' => 'product:read']);

        return $this->json($serializedProduct);
    }

    /**
     * Création d'un nouveau produit
     */
    #[Route('/api/products', name: 'api_add_product', methods: ['GET','POST'])]
    #[OA\Tag(name: 'Products')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: new Model(type: ProductType::class)))]
    #[OA\Response(response: 201, description: 'Returns the created product')]
    #[OA\Response(response: 400, description: 'Invalid data')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 403, description: 'User not allowed to create this product')]
    // #[OA\Security(name: "Bearer")]
    public function addProduct(Request $request, StripeService $stripeService, NormalizerInterface $normalizer, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        // Utilisation du service pour obtenir l'utilisateur
        $result = $this->userService->getUserFromRequest($request);
        if ($result instanceof JsonResponse) {
            return $result;
        }

        $user = $result;

        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPhotoName($data['photoName']);
        $product->setPrice($data['price']);
        $product->setIsAvailable($data['isAvailable']);

        /**
         * Persistance de l'entité produit dans la base de données
         */
        $entityManager->persist($product);
        $entityManager->flush();

        /**
         * Création du produit Stripe
         */
        $stripeProduct = $stripeService->createProduct($product);
        $product->setStripeProductId($stripeProduct->id);

        /**
         * Création du prix Stripe
         */
        $stripePrice = $stripeService->createPrice($product);
        $product->setStripePriceId($stripePrice->id);

        /**
         * Mise à jour de l'entité produit dans la base de données
         */
        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json($normalizer->normalize($product, 'json', ['groups' => 'product:read']), Response::HTTP_CREATED);
    }

    /**
     * Modifie un produit
     */
    #[Route('/api/products/{id}', name: 'api_modify_product', methods: ['GET','POST'])]
    #[OA\Tag(name: 'Products')]
    #[OA\Response(response: 200, description: 'Returns the modified product')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 403, description: 'User not allowed to modify this product')]
    // #[OA\Security(name: 'Bearer')]
    public function modifyProduct(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        // Utilisation du service pour obtenir l'utilisateur
        $result = $this->userService->getUserFromRequest($request);
        if ($result instanceof JsonResponse) {
            return $result;
        }

        $user = $result;

        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            return $this->json([
                'error' => 'Product not found'
            ], Response::HTTP_NOT_FOUND);
        }

        if (isset($data['name'])) {
            $product->setName($data['name']);
        }
        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }
        if (isset($data['photo'])) {
            $product->setPhoto($data['photo']);
        }
        if (isset($data['price'])) {
            $product->setPrice($data['price']);
        }
        if (isset($data['isAvailable'])) {
            $product->setIsAvailable($data['isAvailable']);
        }

        $entityManager->flush();

        $responseData = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'photo' => $product->getPhoto(),
            'price' => $product->getPrice(),
            'isAvailable' => $product->isAvailable(),
        ];

        return $this->json($responseData, Response::HTTP_OK);
    }

    /**
     * Supprime un produit
     */
    #[Route('/api/products/{id}', name: 'api_delete_product', methods: ['GET','DELETE'])]
    #[OA\Tag(name: 'Products')]
    #[OA\Response(response: 204, description: 'Product successfully deleted')]
    // #[OA\Security(name: 'Bearer')]
    public function deleteProduct(Request $request, ProductRepository $productRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        // Utilisation du service pour obtenir l'utilisateur
        $result = $this->userService->getUserFromRequest($request);
        if ($result instanceof JsonResponse) {
            return $result;
        }

        $user = $result;

        $product = $productRepository->find($id);

        if (!$product) {
            return $this->json([
                'error' => 'Product not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
