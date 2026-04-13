<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Client;
use App\Entity\Livraison;
use App\Entity\Tournee;
use App\Entity\User;
use App\Enum\LivraisonStatutEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
class LivraisonController extends AbstractController
{

    /**
     * Modifier statut d’une livraison
     */
    #[Route('/livraisons/{id}/statut', name: 'update_statut', methods: ['PATCH'])]
    #[IsGranted('LIVRAISON_EDIT', subject: 'livraison')]
    public function updateStatut(
        Request $request,
        Livraison $livraison,
        EntityManagerInterface $em,
    ): Response {

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        if (!isset($data['statut'])) {
            return $this->json([
                'message' => 'Statut requis'
            ], 400);
        }

        try {
            $statut = LivraisonStatutEnum::from($data['statut']);
        } catch (\ValueError $e) {
            return $this->json([
                'message' => 'Statut invalide'
            ], 400);
        }

        $livraison->setStatut($statut);

        $em->flush();

        return $this->json([
            'message' => 'Statut mis à jour',
            'statut' => $statut->value
        ]);
    }

    #[Route('/livraisons', name: 'create_livraison', methods: ['POST'])]
    #[IsGranted('ROLE_CHAUFFEUR')]
    public function create(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $tourneeId = $data['tournee_id'] ?? null;
        $clientId = $data['client_id'] ?? null;
        $adresseId = $data['adresse_id'] ?? null;
        $heure = $data['heure_prevue'] ?? null;

        if (!$tourneeId || !$clientId || !$adresseId || !$heure) {
            return $this->json([
                'message' => 'Champs requis manquants'
            ], 400);
        }

        $tournee = $em->getRepository(Tournee::class)->find(Uuid::fromString($tourneeId));
        $client = $em->getRepository(Client::class)->find(Uuid::fromString($clientId));
        $adresse = $em->getRepository(Adresse::class)->find(Uuid::fromString($adresseId));


        if (!$tournee || !$client || !$adresse) {
            return $this->json([
                'message' => 'Tournee, client ou adresse invalide'
            ], 404);
        }

        $livraison = new Livraison();

        try {
            $livraison->setHeurePrevue(new \DateTimeImmutable($heure));
        } catch (\Exception $e) {
            return $this->json(['message' => 'Date invalide'], 400);
        }

        $livraison->setTournee($tournee);
        $livraison->setClient($client);
        $livraison->setAdresse($adresse);

        $em->persist($livraison);
        $em->flush();

        return $this->json([
            'message' => 'Livraison créée',
            'id' => $livraison->getId()
        ], 201);
    }
}
