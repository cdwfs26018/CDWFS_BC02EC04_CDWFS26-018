<?php

namespace App\Security\Voter;

use App\Entity\Livraison;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class LivraisonVoter extends Voter
{
    public const VIEW = 'LIVRAISON_VIEW';
    public const EDIT = 'LIVRAISON_EDIT';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof Livraison;
    }

    protected function voteOnAttribute(string $attribute, mixed $livraison, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // ADMIN = accès total
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($livraison, $user),
            self::EDIT => $this->canEdit($livraison, $user),
            default => false,
        };
    }

    private function canView(Livraison $livraison, User $user): bool
    {
        // Chauffeur → ses livraisons
        if ($user->isChauffeur()) {
            return $livraison->getTournee()->getChauffeur() === $user->getChauffeur();
        }

        // Client → ses livraisons
        if ($user->isClient()) {
            return $livraison->getClient() === $user->getClient();
        }

        return false;
    }

    private function canEdit(Livraison $livraison, User $user): bool
    {
        // Chauffeur peut modifier ses livraisons
        if ($user->isChauffeur()) {
            return $livraison->getTournee()->getChauffeur() === $user->getChauffeur();
        }

        return false;
    }
}
