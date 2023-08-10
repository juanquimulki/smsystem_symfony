<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Firebase\JWT\JWT;

use OpenApi\Annotations as OA;

/**
 * @Route("/api", name="api_")
 * @OA\Tag(name="Users")
 */
#[Route('/api', name: 'api_')]
class UserController extends BaseController
{
    /**
     * @Route("/auth/login", name="auth_login")
     * @OA\Parameter(
     *     name="email",
     *     in="query",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="password",
     *     in="query",
     *     @OA\Schema(type="string")
     * )
     */
    #[Route('/auth/login', name: 'auth_login', methods: ['post', 'options'])]
    public function userAuthLogin(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        if ($request->isMethod('OPTIONS')) die();

        $content = json_decode($request->getContent(), true);
        if (!$content) $content = $request->query->all();
        
        $user = $entityManager->getRepository(User::class)->findOneByEmail($content["email"]);

        if (!$user) {
            return $this->json([
                'message' => 'User not found',
                'email' => $content["email"],
            ], Response::HTTP_NOT_FOUND);
        } else {
            if ($user->getPassword() != md5($content["password"])) {
                return $this->json([
                    'message' => 'Password incorrect',
                    'email' => $user->getEmail(),
                ], Response::HTTP_FORBIDDEN);
            }            
        }

        $token = $this->getToken($user);

        return $this->json([
            'message' => 'User logged in',
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'token' => $token
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/auth/register", name="auth_register")
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="email",
     *     in="query",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="password",
     *     in="query",
     *     @OA\Schema(type="string")
     * )
     */
    #[Route('/auth/register', name: 'auth_register', methods: ['post', 'options'])]
    public function userAuthRegister(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        if ($request->isMethod('OPTIONS')) die();

        $content = json_decode($request->getContent(), true);
        if (!$content) $content = $request->query->all();

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
            'id' => $user->getId(),
        ], Response::HTTP_CREATED);
    }
}
