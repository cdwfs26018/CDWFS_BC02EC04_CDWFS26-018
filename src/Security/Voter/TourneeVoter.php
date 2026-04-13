<?php

namespace App\Security\Voter;

use App\Entity\Tournee;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TourneeVoter extends Voter
{
    public const VIEW = 'TOURNEE_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof Tournee;
    }

    protected function voteOnAttribute(string $attribute, mixed $tournee, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // ADMIN = accès total
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Chauffeur → ses tournées
        if ($user->isChauffeur()) {
            return $tournee->getChauffeur() === $user->getChauffeur();
        }

        return false;
    }
}
