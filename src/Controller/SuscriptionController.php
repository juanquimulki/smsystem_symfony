<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Suscription;
use App\Entity\UserSuscription;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;

#[Route('/api', name: 'api_')]
class SuscriptionController extends AbstractController
{
    #[Route('/suscriptions', name: 'suscriptions', methods: ['get'])]
    public function getSuscriptions(EntityManagerInterface $entityManager): JsonResponse
    {
        $suscriptions = $entityManager->getRepository(Suscription::class)->findAllAsArray();

        return $this->json($suscriptions, 200);
    }

    #[Route('/suscriptions/me', name: 'suscriptions_me', methods: ['get'])]
    public function getUserSuscriptions(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        $userSuscriptions = $entityManager->getRepository(UserSuscription::class)->findAllByUserAsArray($content["user_id"]);

        return $this->json($userSuscriptions, 200);
    }

    #[Route('/suscriptions/{suscription_id}', name: 'suscription', methods: ['get'])]
    public function getSuscription(EntityManagerInterface $entityManager, int $suscription_id): JsonResponse
    {
        $suscription = $entityManager->getRepository(Suscription::class)->find($suscription_id);

        $data =  [
            'id' => $suscription->getId(),
            'name' => $suscription->getName(),
            'description' => $suscription->getDescription(),
            'price' => $suscription->getPrice(),
            'duration' => $suscription->getDuration()
        ];

        return $this->json($data, 200);
    }

    #[Route('/suscriptions/{suscription_id}/subscribe', name: 'suscriptions_subscribe', methods: ['post'])]
    public function userSubscribe(EntityManagerInterface $entityManager, int $suscription_id, Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        $user_suscription = $entityManager->getRepository(UserSuscription::class)->findOneByUserSuscription($content["user_id"], $suscription_id);

        if ($user_suscription) {
            return $this->json([
                'message' => 'User suscription already exists',
                'user.id' => $content["user_id"],
                'suscription.id' => $suscription_id
            ], 400);
        }

        $userSuscription = new UserSuscription();
        $userSuscription->setUserId($content["user_id"]);
        $userSuscription->setSuscriptionId($suscription_id);
        $userSuscription->setStatus("ACTIVE");

        $date = new \DateTime();
        $userSuscription->setStartDate($date);
        $userSuscription->setEndDate($date->modify('+60 day'));

        $entityManager->persist($userSuscription);
        $entityManager->flush();

        return $this->json([
            'message' => 'User suscribed',
            'user.id' => $content["user_id"],
            'suscription.id' => $suscription_id,
        ], 201);
    }

    #[Route('/suscriptions/{suscription_id}/unsubscribe', name: 'suscriptions_unsubscribe', methods: ['post'])]
    public function userUnsubscribe(EntityManagerInterface $entityManager, int $suscription_id, Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        $user_suscription = $entityManager->getRepository(UserSuscription::class)->findOneByUserSuscription($content["user_id"], $suscription_id);

        if (!$user_suscription) {
            return $this->json([
                'message' => 'User suscription not found',
                'user.id' => $content["user_id"],
                'suscription.id' => $suscription_id
            ], 400);
        }

        $entityManager->remove($user_suscription);
        $entityManager->flush();

        return $this->json([
            'message' => 'User unsuscribed',
            'user.id' => $content["user_id"],
            'suscription.id' => $suscription_id,
        ], 201);
    }
}
