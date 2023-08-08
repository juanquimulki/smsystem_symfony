<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        return $this->json([
            'message' => 'User logged in',
            'user.email' => $user->getEmail(),
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
