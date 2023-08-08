<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use App\DTO\UserDTO;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{
    #[Route('/auth/login/{id}', name: 'auth_login')]
    public function userAuthLogin(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
            'user' => $user->getName(),
        ]);
    }

    #[Route('/auth/register', name: 'auth_register', methods: ['post'])]
    public function userAuthRegister(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        $user = new User();
        $user->setName($content["name"]);
        $user->setEmail($content["email"]);
        $user->setPassword($content["password"]);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'New user created',
            'user' => $user->getId(),
        ]);
    }
}
