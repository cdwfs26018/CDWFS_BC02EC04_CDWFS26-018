<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
class ClientController extends AbstractController
{
    /**
     * Consulter un client
     */
    #[Route('/clients/{id}', name: 'client_show', methods: ['GET'])]
    #[IsGranted('CLIENT_VIEW', subject: 'client')]
    public function getClient(Client $client, #[CurrentUser] ?User $user): Response
    {
        return $this->json([
            'id' => $client->getId(),
            'nom' => $client->getNom(),
            'email' => $client->getEmail(),
            'telephone' => $client->getTelephone(),
        ]);
    }
}
