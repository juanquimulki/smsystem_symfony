<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Firebase\JWT\JWT;

#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{
    #[Route('/auth/login', name: 'auth_login', methods: ['post'])]
    public function userAuthLogin(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        
        $user = $entityManager->getRepository(User::class)->findOneByEmail($content["email"]);

        if (!$user) {
            return $this->json([
                'message' => 'User not found',
                'user.email' => $content["email"],
            ], Response::HTTP_NOT_FOUND);
        } else {
            if ($user->getPassword() != md5($content["password"])) {
                return $this->json([
                    'message' => 'Password incorrect',
                    'user.email' => $user->getEmail(),
                ], Response::HTTP_FORBIDDEN);
            }            
        }

        /* creating access token */
        $issuedAt = time();
        // valid for 30 days
        $expirationTime = $issuedAt + 30 * (60 * 60 * 24);

        $key = 'example_key';
        $payload = [
            "user_id" => $user->getId(),
            "user_name" => $user->getName(),
            "user_email" => $user->getEmail(),
            "exp" => $expirationTime
        ];
        $token = JWT::encode($payload, $key, 'HS256');

        return $this->json([
            'message' => 'User logged in',
            'user.email' => $user->getEmail(),
            'user.token' => $token
        ], Response::HTTP_OK);
    }

    #[Route('/auth/register', name: 'auth_register', methods: ['post'])]
    public function userAuthRegister(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        $user = $entityManager->getRepository(User::class)->findOneByEmail($content["email"]);

        if ($user) {
            return $this->json([
                'message' => 'User already exists',
                'user.email' => $content["email"],
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setName($content["name"]);
        $user->setEmail($content["email"]);
        $user->setPassword(md5($content["password"]));

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'New user created',
            'user.id' => $user->getId(),
        ], Response::HTTP_CREATED);
    }
}
