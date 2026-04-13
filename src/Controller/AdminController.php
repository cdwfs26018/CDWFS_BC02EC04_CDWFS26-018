<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Chauffeur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[Route('/api/admin', name: 'api_admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/users', name: 'create_user', methods: ['POST'])]
    public function createUser(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): Response {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $role = $data['role'] ?? null;
        $allowedRoles = ['ROLE_CLIENT', 'ROLE_CHAUFFEUR'];

        if (!in_array($role, $allowedRoles)) {
            return $this->json(['message' => 'Rôle invalide'], 400);
        }
        $nom = $data['nom'] ?? null;
        $prenom = $data['prenom'] ?? null;
        $telephone = $data['telephone'] ?? null;

        if (!$email || !$password || !$role) {
            return $this->json(['message' => 'Champs requis manquants'], 400);
        }

        $existingUser = $em->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if ($existingUser) {
            return $this->json([
                'message' => 'Email déjà utilisé'
            ], 409);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles([$role]);
        $user->setPassword(
            $passwordHasher->hashPassword($user, $password)
        );

        // Création métier selon rôle
        if ($role === 'ROLE_CLIENT') {
            $client = new Client();
            $client->setNom($nom ?? 'Client');
            $client->setEmail($email);
            $client->setTelephone($telephone ?? '0000000000');

            $em->persist($client);

            $user->setClient($client);
        }

        if ($role === 'ROLE_CHAUFFEUR') {
            $chauffeur = new Chauffeur();
            $chauffeur->setNom($nom ?? 'Chauffeur');
            $chauffeur->setPrenom($prenom ?? '');
            $chauffeur->setEmail($email);
            $chauffeur->setTelephone($telephone ?? '0000000000');

            $em->persist($chauffeur);

            $user->setChauffeur($chauffeur);
        }

        $errors = $validator->validate($user);

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

        $em->persist($user);
        $em->flush();

        return $this->json([
            'message' => 'Utilisateur créé',
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()
        ], 201);
    }

    #[Route('/users/{id}/role', name: 'update_user_role', methods: ['PATCH'])]
    public function updateUserRole(
        User $user,
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        $data = json_decode($request->getContent(), true);

        $newRole = $data['role'] ?? null;

        if (!in_array($newRole, ['ROLE_CLIENT', 'ROLE_CHAUFFEUR'])) {
            return $this->json(['message' => 'Rôle invalide'], 400);
        }

        if ($user->getClient()) {
            $em->remove($user->getClient());
        }

        if ($user->getChauffeur()) {
            $em->remove($user->getChauffeur());
        }

        // Reset des relations métier
        $user->setClient(null);
        $user->setChauffeur(null);

        $user->setRoles([$newRole]);

        // Recréation métier selon rôle
        if ($newRole === 'ROLE_CLIENT') {
            $client = new Client();
            $client->setNom('Client');
            $client->setEmail($user->getEmail());
            $client->setTelephone('0000000000');

            $em->persist($client);
            $user->setClient($client);
        }

        if ($newRole === 'ROLE_CHAUFFEUR') {
            $chauffeur = new Chauffeur();
            $chauffeur->setNom('Chauffeur');
            $chauffeur->setPrenom('');
            $chauffeur->setEmail($user->getEmail());
            $chauffeur->setTelephone('0000000000');

            $em->persist($chauffeur);
            $user->setChauffeur($chauffeur);
        }

        $em->flush();

        return $this->json([
            'message' => 'Rôle mis à jour',
            'roles' => $user->getRoles()
        ]);
    }

    #[Route('/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(
        User $user,
        EntityManagerInterface $em,
    ): Response {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['message' => 'Impossible de supprimer un admin'], 400);
        }

        $admin = $this->getUser();

        // empêcher suppression de soi-même
        if ($admin === $user) {
            return $this->json([
                'message' => 'Impossible de se supprimer soi-même'
            ], 400);
        }

        $em->remove($user);
        $em->flush();

        return $this->json([
            'message' => 'Utilisateur supprimé'
        ]);
    }
}
