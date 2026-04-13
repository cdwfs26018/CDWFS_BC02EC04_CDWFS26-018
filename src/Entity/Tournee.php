<?php

namespace App\Entity;

use App\Repository\TourneeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TourneeRepository::class)]
class Tournee
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private \DateTimeImmutable $date;

    #[ORM\ManyToOne(inversedBy: 'tournees')]
    #[ORM\JoinColumn(nullable: false)]
    private Chauffeur $chauffeur;

    /**
     * @var Collection<int, Livraison>
     */
    #[ORM\OneToMany(
        targetEntity: Livraison::class,
        mappedBy: 'tournee',
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $livraisons;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->livraisons = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getChauffeur(): Chauffeur
    {
        return $this->chauffeur;
    }

    public function setChauffeur(Chauffeur $chauffeur): static
    {
        $this->chauffeur = $chauffeur;

        return $this;
    }

    /**
     * @return Collection<int, Livraison>
     */
    public function getLivraisons(): Collection
    {
        return $this->livraisons;
    }

    public function addLivraison(Livraison $livraison): static
    {
        if (!$this->livraisons->contains($livraison)) {
            $this->livraisons->add($livraison);
            $livraison->setTournee($this);
        }

        return $this;
    }

    public function removeLivraison(Livraison $livraison): static
    {
        $this->livraisons->removeElement($livraison);

        return $this;
    }
}
