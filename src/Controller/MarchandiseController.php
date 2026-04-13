<?php

namespace App\Controller;

use App\Repository\MarchandiseRepository;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
#[IsGranted('ROLE_CHAUFFEUR')]
class MarchandiseController extends AbstractController
{
    /**
     * Récupérer toutes les marchandises
     */
    #[Route('/marchandises', name: 'marchandises_list', methods: ['GET'])]
    public function index(
        MarchandiseRepository $repository,
    ): Response {
        $marchandises = $repository->findAll();

        $data = [];

        foreach ($marchandises as $m) {
            $data[] = [
                'id' => $m->getId(),
                'nom' => $m->getNom(),
                'poids' => $m->getPoids(),
                'volume' => $m->getVolume(),
            ];
        }

        return $this->json($data);
    }
}
