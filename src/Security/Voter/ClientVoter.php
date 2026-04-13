<?php

namespace App\Security\Voter;

use App\Entity\Client;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ClientVoter extends Voter
{
    public const VIEW = 'CLIENT_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof Client;
    }

    protected function voteOnAttribute(string $attribute, mixed $client, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // Admin = accès total
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Client → uniquement lui-même
        if ($user->isClient()) {
            return $user->getClient() === $client;
        }

        return false;
    }
}
