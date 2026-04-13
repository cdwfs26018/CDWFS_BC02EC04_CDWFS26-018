<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
final class ApiAuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(#[CurrentUser] ?User $user): Response
    {
        // Cette méthode ne sera jamais exécutée
        // Le firewall json_login intercepte la requête avant
        // et lexik gère success/failure automatiquement
        throw new \LogicException('Ne devrait pas être appelé directement.');
    }

    // -------------------------
    // ME (profil connecté)
    // -------------------------
    #[Route('/me', name: 'me', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function me(#[CurrentUser] ?User $user): Response
    {

        return $this->json([
            'id'       => $user->getId(),
            'email'    => $user->getEmail(),
            'roles'    => $user->getRoles(),
        ], Response::HTTP_OK);
    }

    // -------------------------
    // CHANGE PASSWORD
    // -------------------------
    #[Route('/change-password', name: 'change_password', methods: ['PUT'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function changePassword(
        Request $request,
        #[CurrentUser] ?User $user
    ): Response {

        $data = json_decode($request->getContent(), true);

        $currentPassword = $data['current_password'] ?? null;
        $newPassword     = $data['new_password'] ?? null;

        if (!$currentPassword || !$newPassword) {
            return $this->json([
                'message' => 'current_password et new_password sont requis',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérif ancien mot de passe
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            return $this->json([
                'message' => 'Mot de passe actuel incorrect',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (strlen($newPassword) < 6) {
            return $this->json([
                'message' => 'Le nouveau mot de passe doit faire au moins 6 caractères',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $newPassword)
        );

        $this->em->flush();

        return $this->json([
            'message' => 'Mot de passe modifié avec succès',
        ], Response::HTTP_OK);
    }

    // -------------------------
    // DELETE ACCOUNT
    // -------------------------
    #[Route('/delete-account', name: 'delete_account', methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function deleteAccount(#[CurrentUser] ?User $user): Response
    {
        $this->em->remove($user);
        $this->em->flush();

        return $this->json([
            'message' => 'Compte supprimé avec succès',
        ], Response::HTTP_OK);
    }
}
