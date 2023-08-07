<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

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
}
