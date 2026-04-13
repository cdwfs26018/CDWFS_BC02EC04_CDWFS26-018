<?php

namespace App\Entity;

use App\Repository\MarchandiseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MarchandiseRepository::class)]
class Marchandise
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $nom;

    #[ORM\Column]
    #[Assert\Positive]
    private float $poids;

    #[ORM\Column]
    #[Assert\Positive]
    private float $volume;

    /**
     * @var Collection<int, LivraisonMarchandise>
     */
    #[ORM\OneToMany(targetEntity: LivraisonMarchandise::class, mappedBy: 'marchandise')]
    private Collection $livraisonMarchandises;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->livraisonMarchandises = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPoids(): float
    {
        return $this->poids;
    }

    public function setPoids(float $poids): static
    {
        if ($poids <= 0) {
            throw new \InvalidArgumentException('Le poids doit être positif');
        }

        $this->poids = $poids;

        return $this;
    }

    public function getVolume(): float
    {
        return $this->volume;
    }

    public function setVolume(float $volume): static
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * @return Collection<int, LivraisonMarchandise>
     */
    public function getLivraisonMarchandises(): Collection
    {
        return $this->livraisonMarchandises;
    }

    public function addLivraisonMarchandise(LivraisonMarchandise $livraisonMarchandise): static
    {
        if (!$this->livraisonMarchandises->contains($livraisonMarchandise)) {
            $this->livraisonMarchandises->add($livraisonMarchandise);
            $livraisonMarchandise->setMarchandise($this);
        }

        return $this;
    }

    public function removeLivraisonMarchandise(LivraisonMarchandise $livraisonMarchandise): static
    {
        $this->livraisonMarchandises->removeElement($livraisonMarchandise);

        return $this;
    }

    public function getDescription(): string
    {
        return "{$this->nom} ({$this->poids}kg / {$this->volume}m3)";
    }
}
