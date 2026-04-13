<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
final class ApiAuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
        private ValidatorInterface $validator,
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
    // REGISTER
    // -------------------------
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json([
                'message' => 'Invalid JSON',
            ], Response::HTTP_BAD_REQUEST);
        }

        $email    = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return $this->json([
                'message' => 'email, username et password sont requis',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérif email déjà utilisé
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            return $this->json([
                'message' => 'Email déjà utilisé',
            ], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $password)
        );

        // Validation des contraintes de l'entité
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json([
                'message' => 'Erreurs de validation',
                'errors'  => $errorMessages,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->persist($user);
        $this->em->flush();

        $token = $this->jwtManager->create($user);

        return $this->json([
            'message' => 'Compte créé avec succès',
            'user'    => $user->getUserIdentifier(),
            'token'   => $token,
        ], Response::HTTP_CREATED);
    }

    // -------------------------
    // ME (profil connecté)
    // -------------------------
    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'Non authentifié',
            ], Response::HTTP_UNAUTHORIZED);
        }

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
    public function changePassword(
        Request $request,
        #[CurrentUser] ?User $user
    ): Response {
        if (null === $user) {
            return $this->json([
                'message' => 'Non authentifié',
            ], Response::HTTP_UNAUTHORIZED);
        }

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
    public function deleteAccount(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'Non authentifié',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $this->em->remove($user);
        $this->em->flush();

        return $this->json([
            'message' => 'Compte supprimé avec succès',
        ], Response::HTTP_OK);
    }
}
