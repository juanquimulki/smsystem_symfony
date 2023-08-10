<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Suscription;
use App\Entity\UserSuscription;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api', name: 'api_')]
class SuscriptionController extends BaseController
{
    #[Route('/suscriptions', name: 'suscriptions', methods: ['get'])]
    public function getSuscriptions(EntityManagerInterface $entityManager): JsonResponse
    {
        $suscriptions = $entityManager->getRepository(Suscription::class)->findAllAsArray();

        return $this->json($suscriptions, Response::HTTP_OK);
    }

    #[Route('/suscriptions/me', name: 'suscriptions_me', methods: ['get', 'options'])]
    public function getUserSuscriptions(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        if ($request->isMethod('OPTIONS')) die();

        $value = $request->headers->get('Authorization');
        if (!$this->allowAccess($request->headers->get('Authorization'))) 
            return $this->json($this->invalidTokenMessage(), Response::HTTP_FORBIDDEN);

        $content = json_decode($request->getContent(), true);
        if (!$content) $content = $request->query->all();

        $userSuscriptions = $entityManager->getRepository(UserSuscription::class)->findAllByUserAsArray($content["user_id"]);

        return $this->json($userSuscriptions, Response::HTTP_OK);
    }

    #[Route('/suscriptions/{suscription_id}', name: 'suscription', methods: ['get'])]
    public function getSuscription(EntityManagerInterface $entityManager, int $suscription_id): JsonResponse
    {
        if (!$this->allowAccess($request->headers->get('Authorization'))) 
            return $this->json($this->invalidTokenMessage(), Response::HTTP_FORBIDDEN);

        $suscription = $entityManager->getRepository(Suscription::class)->find($suscription_id);

        $data =  [
            'id' => $suscription->getId(),
            'name' => $suscription->getName(),
            'description' => $suscription->getDescription(),
            'price' => $suscription->getPrice(),
            'duration' => $suscription->getDuration()
        ];

        return $this->json($data, Response::HTTP_OK);
    }

    #[Route('/suscriptions/{suscription_id}/subscribe', name: 'suscriptions_subscribe', methods: ['post', 'options'])]
    public function userSubscribe(EntityManagerInterface $entityManager, int $suscription_id, Request $request): JsonResponse
    {
        if ($request->isMethod('OPTIONS')) die();

        if (!$this->allowAccess($request->headers->get('Authorization'))) 
            return $this->json($this->invalidTokenMessage(), Response::HTTP_FORBIDDEN);

        $content = json_decode($request->getContent(), true);

        $user_suscription = $entityManager->getRepository(UserSuscription::class)->findOneByUserSuscription($content["user_id"], $suscription_id);

        if ($user_suscription) {
            return $this->json([
                'message' => 'User suscription already exists',
                'userid' => $content["user_id"],
                'suscriptionid' => $suscription_id
            ], Response::HTTP_BAD_REQUEST);
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
            'userid' => $content["user_id"],
            'suscriptionid' => $suscription_id,
        ], Response::HTTP_CREATED);
    }

    #[Route('/suscriptions/{suscription_id}/unsubscribe', name: 'suscriptions_unsubscribe', methods: ['post', 'options'])]
    public function userUnsubscribe(EntityManagerInterface $entityManager, int $suscription_id, Request $request): JsonResponse
    {
        if ($request->isMethod('OPTIONS')) die();

        if (!$this->allowAccess($request->headers->get('Authorization'))) 
            return $this->json($this->invalidTokenMessage(), Response::HTTP_FORBIDDEN);

        $content = json_decode($request->getContent(), true);

        $user_suscription = $entityManager->getRepository(UserSuscription::class)->findOneByUserSuscription($content["user_id"], $suscription_id);

        if (!$user_suscription) {
            return $this->json([
                'message' => 'User suscription not found',
                'userid' => $content["user_id"],
                'suscriptionid' => $suscription_id
            ], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->remove($user_suscription);
        $entityManager->flush();

        return $this->json([
            'message' => 'User unsuscribed',
            'userid' => $content["user_id"],
            'suscriptionid' => $suscription_id,
        ], Response::HTTP_CREATED);
    }

    #[Route('/suscriptions/{suscription_id}/status/{status}', name: 'suscriptions_status', methods: ['post', 'options'])]
    public function changeStatus(EntityManagerInterface $entityManager, int $suscription_id, string $status, Request $request): JsonResponse
    {
        if ($request->isMethod('OPTIONS')) die();
        
        if (!$this->allowAccess($request->headers->get('Authorization'))) 
            return $this->json($this->invalidTokenMessage(), Response::HTTP_FORBIDDEN);

        $content = json_decode($request->getContent(), true);

        $user_suscription = $entityManager->getRepository(UserSuscription::class)->findOneByUserSuscription($content["user_id"], $suscription_id);

        if (!$user_suscription) {
            return $this->json([
                'message' => 'User suscription not found',
                'userid' => $content["user_id"],
                'suscriptionid' => $suscription_id
            ], Response::HTTP_BAD_REQUEST);
        }

        $user_suscription->setStatus($status);
        $entityManager->flush();

        return $this->json([
            'message' => 'Status changed',
            'userid' => $content["user_id"],
            'suscriptionid' => $suscription_id,
        ], Response::HTTP_CREATED);
    }
}
