<?php

namespace App\Controller;

use App\Entity\Chauffeur;
use App\Entity\Tournee;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
class TourneeController extends AbstractController
{
    /**
     * Chauffeur connecté = ses tournées
     */
    #[Route('/me/tournees', name: 'me_tournees', methods: ['GET'])]
    #[IsGranted('ROLE_CHAUFFEUR')]
    public function getMesTournees(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $chauffeur = $user->getChauffeur();

        $tournees = [];

        foreach ($chauffeur->getTournees() as $tournee) {
            $tournees[] = [
                'id' => $tournee->getId()->toRfc4122(),
                'date' => $tournee->getDate()->format('Y-m-d'),
            ];
        }

        return $this->json($tournees);
    }

    /**
     * Voir les livraisons d’une tournée
     */
    #[Route('/tournees/{id}/livraisons', name: 'tournee_livraisons', methods: ['GET'])]
    #[IsGranted('TOURNEE_VIEW', subject: 'tournee')]
    public function getLivraisons(Tournee $tournee): Response
    {
        $livraisonsData = [];

        foreach ($tournee->getLivraisons() as $livraison) {
            $livraisonsData[] = [
                'id' => $livraison->getId()->toRfc4122(),
                'heurePrevue' => $livraison->getHeurePrevue()->format('Y-m-d H:i'),
                'statut' => $livraison->getStatut()->value,
                'client' => $livraison->getClient()->getNom(),
                'adresse' => $livraison->getAdresse()->getFullAdresse(),
            ];
        }

        return $this->json($livraisonsData);
    }

    #[Route('/tournees', methods: ['POST'])]
    #[IsGranted('ROLE_CHAUFFEUR')]
    public function createTournee(Request $request,EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $tournee = new Tournee();
        $tournee->setDate(new \DateTimeImmutable());

        if (in_array('ROLE_ADMIN', $user->getRoles())) {

            $chauffeurId = $data['chauffeur_id'] ?? null;

            if (!$chauffeurId) {
                return $this->json([
                    'message' => 'chauffeur_id requis pour un admin'
                ], 400);
            }

            $chauffeur = $em->getRepository(Chauffeur::class)
                ->find($chauffeurId);

            if (!$chauffeur) {
                return $this->json([
                    'message' => 'Chauffeur introuvable'
                ], 404);
            }

            $tournee->setChauffeur($chauffeur);
        }

        // CAS CHAUFFEUR
        elseif ($user->isChauffeur()) {
            $tournee->setChauffeur($user->getChauffeur());
        }

        // CAS INTERDIT
        else {
            return $this->json([
                'message' => 'Accès refusé'
            ], 403);
        }

        $em->persist($tournee);
        $em->flush();

        return $this->json([
            'id' => $tournee->getId()->toRfc4122(),
        ], 201);
    }
}
