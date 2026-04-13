<?php

namespace App\Entity;

use App\Repository\LivraisonMarchandiseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LivraisonMarchandiseRepository::class)]
class LivraisonMarchandise
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $id = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Positive]
    private int $quantite;

    #[ORM\ManyToOne(inversedBy: 'livraisonMarchandises')]
    #[ORM\JoinColumn(nullable: false)]
    private Livraison $livraison;

    #[ORM\ManyToOne(inversedBy: 'livraisonMarchandises')]
    #[ORM\JoinColumn(nullable: false)]
    private Marchandise $marchandise;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        if ($quantite <= 0) {
            throw new \InvalidArgumentException('La quantité doit être supérieure à 0');
        }

        $this->quantite = $quantite;

        return $this;
    }

    public function getLivraison(): Livraison
    {
        return $this->livraison;
    }

    public function setLivraison(Livraison $livraison): static
    {
        $this->livraison = $livraison;

        return $this;
    }

    public function getMarchandise(): Marchandise
    {
        return $this->marchandise;
    }

    public function setMarchandise(Marchandise $marchandise): static
    {
        $this->marchandise = $marchandise;

        return $this;
    }
}
