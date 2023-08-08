<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Suscription;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api', name: 'api_')]
class SuscriptionController extends AbstractController
{
    #[Route('/suscriptions', name: 'suscriptions', methods: ['get'])]
    public function getSuscriptions(EntityManagerInterface $entityManager): JsonResponse
    {
        $suscriptions = $entityManager->getRepository(Suscription::class)->findAllAsArray();

        return $this->json($suscriptions, 200);
    }

    #[Route('/suscriptions/{suscription_id}', name: 'suscriptions', methods: ['get'])]
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
}
