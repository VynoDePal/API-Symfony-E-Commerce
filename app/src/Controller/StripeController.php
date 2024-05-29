<?php

namespace App\Controller;

use App\Service\SessionService;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    public function __construct(private readonly StripeService $stripeService) {}

    /**
     * Créer une session de paiement Stripe.
     *
     * @param SessionService $sessionService Le service de session.
     * @return Response La réponse JSON contenant l'URL de la session de paiement.
     */
    #[Route('/stripe/checkout-sessions', name: 'create_checkout_session', methods: ['GET','POST'])]
    public function createCheckoutSession(SessionService $sessionService): Response
    {
        /**
         * Créer la session de paiement Stripe avec le panier de la session.
         */
        $checkoutSession = $this->stripeService->createCheckoutSession($sessionService->getShoppingCart());
        
        /**
         * Retourner l'URL de la session de paiement.
         */
        return $this->json(['url' => $checkoutSession->url]);
    }

    /**
     * Traiter la réussite d'un paiement Stripe.
     */
    #[Route('/success', name: 'stripe_success')]
    public function success(Request $request): Response
    {
        /**
         * Récupérer l'ID de la session de paiement Stripe.
         */
        $sessionId = $request->query->get('session_id');
        
        /**
         * Récupérer la session de paiement Stripe.
         */
        $session = $this->stripeService->getCheckoutSession($sessionId);
        
        /**
         * Retourner le message de succès.
         */
        return $this->json(['message' => 'Achat effectué avec succès']);
    }

    #[Route('/stripe/cancel', name: 'stripe_cancel')]
    public function cancel(): Response
    {
        return $this->json(['message' => 'Achat annulé']);
    }
}
