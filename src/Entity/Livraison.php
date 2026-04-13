<?php

namespace App\Entity;

use App\Repository\LivraisonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\LivraisonStatutEnum;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LivraisonRepository::class)]
class Livraison
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $id = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $heurePrevue;

    #[ORM\Column(enumType: LivraisonStatutEnum::class)]
    #[Assert\NotNull]
    private LivraisonStatutEnum $statut;

    #[ORM\ManyToOne(inversedBy: 'livraisons')]
    #[ORM\JoinColumn(nullable: false)]
    private Tournee $tournee;

    #[ORM\ManyToOne(inversedBy: 'livraisons')]
    #[ORM\JoinColumn(nullable: false)]
    private Client $client;

    #[ORM\ManyToOne(inversedBy: 'livraisons')]
    #[ORM\JoinColumn(nullable: false)]
    private Adresse $adresse;

    /**
     * @var Collection<int, LivraisonMarchandise>
     */
    #[ORM\OneToMany(
        targetEntity: LivraisonMarchandise::class,
        mappedBy: 'livraison',
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $livraisonMarchandises;

    public function __construct()
    {
        $this->statut = LivraisonStatutEnum::EN_ATTENTE;
        $this->id = Uuid::v4();
        $this->livraisonMarchandises = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getHeurePrevue(): \DateTimeImmutable
    {
        return $this->heurePrevue;
    }

    public function setHeurePrevue(\DateTimeImmutable $heurePrevue): static
    {
        $this->heurePrevue = $heurePrevue;

        return $this;
    }

    public function getStatut(): LivraisonStatutEnum
    {
        return $this->statut;
    }

    public function setStatut(LivraisonStatutEnum $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getTournee(): Tournee
    {
        return $this->tournee;
    }

    public function setTournee(Tournee $tournee): static
    {
        $this->tournee = $tournee;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getAdresse(): Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(Adresse $adresse): static
    {
        $this->adresse = $adresse;

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
            $livraisonMarchandise->setLivraison($this);
        }

        return $this;
    }

    public function removeLivraisonMarchandise(LivraisonMarchandise $livraisonMarchandise): static
    {
        $this->livraisonMarchandises->removeElement($livraisonMarchandise);

        return $this;
    }
}
