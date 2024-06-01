<?php

namespace App\Controller\Authentification;

use App\Service\TokenService;
use App\Service\UserService;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

#[Route('/api')]
class AuthController extends AbstractController
{
    private TokenService $tokenService;
    private UserService $userService;
    private UserPasswordHasherInterface $passwordHasher;
    private LoggerInterface $logger;

    public function __construct(UserPasswordHasherInterface $passwordHasher, TokenService $tokenService, UserService $userService, LoggerInterface $logger)
    {
        $this->tokenService = $tokenService;
        $this->userService = $userService;
        $this->passwordHasher = $passwordHasher;
        $this->logger = $logger;
    }
    /**
     * Enregistre un nouvel utilisateur.
     */
    #[Route('/register', name: 'app_authentification_register', methods: ['GET','POST'])]
    #[OA\Tag(name: 'Users')]
    #[OA\RequestBody(content: new Model(type: UserType::class), required: true)]
    #[OA\Response(response: 200, description: 'Returns the created user')]
    #[OA\Response(response: 201, description: 'Returns the created user')]
    #[OA\Response(response: 400, description: 'Invalid data')]
    public function register(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->logger->info('Inscription de l\'utilisateur');
        try {
            $data = json_decode($request->getContent(), true);
            $this->logger->debug('Request data', ['data' => $data]);
            $user = new User();
            
            // Vérifier que tous les champs nécessaires sont présents
            if (!isset($data['login'], $data['password'], $data['email'], $data['firstname'], $data['lastname'])) {
                $this->logger->warning('Missing required fields', ['data' => $data]);
                return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
            }

            $user->setLogin($data['login']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
            $user->setEmail($data['email']);
            $user->setFirstname($data['firstname']);
            $user->setLastname($data['lastname']);

            $entityManager->persist($user);
            $entityManager->flush();

            $responseData = [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'email' => $user->getEmail(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
            ];

            $this->logger->info('Utilisateur enregistré', ['data' => $responseData]);
            // Renvoie une réponse JSON avec le nouvel utilisateur créé avec un statut 201 (CREATED)
            return new JsonResponse($responseData, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logger->error('Erreur d\'enregistrement de l\'utilisateur : ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Échec de l\'enregistrement de l\'utilisateur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Connexion d'un utilisateur.
     */
    #[Route('/login', name: 'app_authentification_login', methods: ['GET','POST','OPTIONS'])]
    #[OA\Tag(name: 'Users')]
    #[OA\Response(response: 200, description: 'Return user login')]
    #[OA\Response(response: 400, description: 'Invalid data')]
    public function login(Request $request, EntityManagerInterface $manager): JsonResponse
    {
        $this->logger->info('Connexion de l\'utilisateur');

        if ($request->getMethod() === 'OPTIONS') {
            $this->logger->info('OPTIONS request');
            return new JsonResponse(null, Response::HTTP_OK);
        }        

        try {
            $data = json_decode($request->getContent(), true);
            $this->logger->debug('Request data', ['data' => $data]);
            $user = $manager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

            if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
                $this->logger->warning('L\'email ou le mot de passe sont invalides', ['data' => $data]);
                return new JsonResponse(['error' => 'Invalid email or password'], Response::HTTP_BAD_REQUEST);
            }

            $this->logger->debug('User found', ['user' => $user]);

            $token = $this->tokenService->createToken($user->getId());
            $this->logger->info('Token créé', ['token' => $token]);

            return new JsonResponse(['token' => $token], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la connexion de l\'utilisateur : ' . $e->getMessage());
            return new JsonResponse(['error' => 'Échec de connexion de l\'utilisateur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Modifie un utilisateur.
     */
    #[Route('/user', name: 'app_authentification_modify_user', methods: ['GET','POST'])]
    #[OA\Tag(name: 'Users')]
    #[OA\Response(response: 200, description: 'Returns the modified user')]
    #[OA\Response(response: 400, description: 'Invalid data')]
    #[OA\RequestBody(content: new Model(type: UserType::class), required: true)]
    public function modifyUser(Request $request, EntityManagerInterface $manager): JsonResponse
    {
        $this->logger->info('Modification de l\'utilisateur');
        try {
            $data = json_decode($request->getContent(), true);
            $this->logger->debug('Request data', ['data' => $data]);

            // Utilisation du service pour obtenir l'utilisateur
            $result = $this->userService->getUserFromRequest($request);
            if ($result instanceof JsonResponse) {
                $this->logger->warning('L\'utilisateur n\'a pas pu être retrouver', ['data' => $data]);
                return $result;
            }

            $user = $result;

            if (isset($data['login'])) {
                $user->setLogin($data['login']);
            }
            if (isset($data['password'])) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
            }
            if (isset($data['email'])) {
                $user->setEmail($data['email']);
            }
            if (isset($data['firstname'])) {
                $user->setFirstname($data['firstname']);
            }
            if (isset($data['lastname'])) {
                $user->setLastname($data['lastname']);
            }

            $manager->flush();

            $responseData = [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'email' => $user->getEmail(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
            ];

            $this->logger->info('Utilisateur modifié', ['data' => $responseData]);
            return new JsonResponse($responseData, Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la modification de l\'utilisateur : ' . $e->getMessage());
            return new JsonResponse(['error' => 'Échec de modification de l\'utilisateur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
   
    /**
     * Affiche les informations de l'utilisateur connecté.
     */
    #[Route('/api/users', name: 'app_authentification_display_user_informations', methods: ['GET'])]
    #[OA\Tag(name: 'Users')]
    #[OA\Response(response: 200, description: 'Returns the current user informations')]
    public function displayUserInformations(Request $request, SerializerInterface $serializer): Response
    {
        $this->logger->info('Affichage des informations de l\'utilisateur');
        try {
            // Utilisation du service pour obtenir l'utilisateur
            $result = $this->userService->getUserFromRequest($request);
            if ($result instanceof JsonResponse) {
                $this->logger->warning('L\'utilisateur n\'a pas pu être retrouver');
                return $result;
            }

            $user = $result;
            
            $userWithoutPassword = new class {
                public string $id;
                public string $login;
                public string $email;
                public string $firstname;
                public string $lastname;
            };
        
            $userWithoutPassword->id = $user->getId();
            $userWithoutPassword->login = $user->getLogin();
            $userWithoutPassword->email = $user->getEmail();
            $userWithoutPassword->firstname = $user->getFirstname();
            $userWithoutPassword->lastname = $user->getLastname();
        
            $responseData = $serializer->normalize($userWithoutPassword);
        
            $this->logger->info('Informations de l\'utilisateur affichées', ['data' => $responseData]);
            return $this->json($responseData, Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'affichage des informations de l\'utilisateur : ' . $e->getMessage());
            return new JsonResponse(['error' => 'Échec de l\'affichage des informations de l\'utilisateur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Affiche la page d'accueil.
     */
    #[Route('/home', name: 'home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

}
