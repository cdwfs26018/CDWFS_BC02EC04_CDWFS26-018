<?php

namespace App\Controller;

use App\Entity\Adresse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class AdresseController extends AbstractController
{
    /**
     * Lister toutes les adresses
     */
    #[Route('/adresses', name: 'adresse_list', methods: ['GET'])]
    #[IsGranted('ROLE_CHAUFFEUR')]
    public function index(EntityManagerInterface $em): Response
    {
        $adresses = $em->getRepository(Adresse::class)->findAll();

        $data = [];

        foreach ($adresses as $adresse) {
            $data[] = [
                'id' => $adresse->getId(),
                'rue' => $adresse->getRue(),
                'ville' => $adresse->getVille(),
                'codePostal' => $adresse->getCodePostal(),
                'pays' => $adresse->getPays(),
            ];
        }

        return $this->json($data);
    }

    /**
     * Voir une adresse
     */
    #[Route('/adresses/{id}', name: 'adresse_show', methods: ['GET'])]
    #[IsGranted('ROLE_CHAUFFEUR')]
    public function show(Adresse $adresse): Response
    {
        return $this->json([
            'id' => $adresse->getId(),
            'rue' => $adresse->getRue(),
            'ville' => $adresse->getVille(),
            'codePostal' => $adresse->getCodePostal(),
            'pays' => $adresse->getPays(),
        ]);
    }

    /**
     * Créer une adresse
     */
    #[Route('/adresses', name: 'adresse_create', methods: ['POST'])]
    #[IsGranted('ROLE_CHAUFFEUR')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): Response {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $adresse = new Adresse();

        $adresse->setRue($data['rue'] ?? '');
        $adresse->setVille($data['ville'] ?? '');
        $adresse->setCodePostal($data['code_postal'] ?? '');
        $adresse->setPays($data['pays'] ?? '');

        // Validation Symfony
        $errors = $validator->validate($adresse);

        if (count($errors) > 0) {
            $messages = [];

            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json([
                'message' => 'Erreur de validation',
                'errors' => $messages
            ], 422);
        }

        $em->persist($adresse);
        $em->flush();

        return $this->json([
            'message' => 'Adresse créée',
            'id' => $adresse->getId()
        ], 201);
    }

    /**
     * Modifier une adresse
     */
    #[Route('/adresses/{id}', name: 'adresse_update', methods: ['PUT'])]
    #[IsGranted('ROLE_CHAUFFEUR')]
    public function update(
        Adresse $adresse,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): Response {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $adresse->setRue($data['rue'] ?? $adresse->getRue());
        $adresse->setVille($data['ville'] ?? $adresse->getVille());
        $adresse->setCodePostal($data['code_postal'] ?? $adresse->getCodePostal());
        $adresse->setPays($data['pays'] ?? $adresse->getPays());

        $errors = $validator->validate($adresse);

        if (count($errors) > 0) {
            $messages = [];

            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json([
                'message' => 'Erreur de validation',
                'errors' => $messages
            ], 422);
        }

        $em->flush();

        return $this->json([
            'message' => 'Adresse mise à jour'
        ]);
    }

    /**
     * Supprimer une adresse
     */
    #[Route('/adresses/{id}', name: 'adresse_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Adresse $adresse,
        EntityManagerInterface $em
    ): Response {
        $em->remove($adresse);
        $em->flush();

        return $this->json([
            'message' => 'Adresse supprimée'
        ]);
    }
}
