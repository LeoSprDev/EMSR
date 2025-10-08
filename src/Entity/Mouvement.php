<?php

namespace App\Entity;

use App\Entity\Enum\MouvementType;
use App\Repository\MouvementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MouvementRepository::class)]
#[ORM\Table(name: 'mouvements')]
#[ORM\HasLifecycleCallbacks]
class Mouvement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', enumType: MouvementType::class)]
    #[Assert\NotNull(message: 'Le type de mouvement est obligatoire.')]
    private ?MouvementType $type = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $prenom = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le numéro d\'agent est obligatoire.')]
    #[Assert\Length(
        max: 20,
        maxMessage: 'Le numéro d\'agent ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $numeroAgent = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'L\'emploi est obligatoire.')]
    #[Assert\Length(
        max: 150,
        maxMessage: 'L\'emploi ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $emploi = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le contrat est obligatoire.')]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Le contrat ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $contrat = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le service est obligatoire.')]
    #[Assert\Length(
        max: 150,
        maxMessage: 'Le service ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $service = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: 'La date d\'effet est obligatoire.')]
    #[Assert\Type(type: '\DateTime', message: 'La date d\'effet doit être une date valide.')]
    private ?\DateTime $dateEffet = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remarque = null;

    #[ORM\Column(length: 7)] // Format: YYYY-MM
    #[Assert\NotBlank(message: 'Le mois de référence est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^\d{4}-\d{2}$/',
        message: 'Le mois de référence doit être au format YYYY-MM.'
    )]
    private ?string $moisReference = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $priseEnCompteInfo = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'mouvementsCreated')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'mouvementsUpdated')]
    private ?User $updatedBy = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $priseEnCompteAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $priseEnCompteBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?MouvementType
    {
        return $this->type;
    }

    public function setType(MouvementType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getNumeroAgent(): ?string
    {
        return $this->numeroAgent;
    }

    public function setNumeroAgent(string $numeroAgent): static
    {
        $this->numeroAgent = $numeroAgent;
        return $this;
    }

    public function getEmploi(): ?string
    {
        return $this->emploi;
    }

    public function setEmploi(string $emploi): static
    {
        $this->emploi = $emploi;
        return $this;
    }

    public function getContrat(): ?string
    {
        return $this->contrat;
    }

    public function setContrat(string $contrat): static
    {
        $this->contrat = $contrat;
        return $this;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(string $service): static
    {
        $this->service = $service;
        return $this;
    }

    public function getDateEffet(): ?\DateTime
    {
        return $this->dateEffet;
    }

    public function setDateEffet(\DateTime $dateEffet): static
    {
        $this->dateEffet = $dateEffet;
        return $this;
    }

    public function getRemarque(): ?string
    {
        return $this->remarque;
    }

    public function setRemarque(?string $remarque): static
    {
        $this->remarque = $remarque;
        return $this;
    }

    public function getMoisReference(): ?string
    {
        return $this->moisReference;
    }

    public function setMoisReference(string $moisReference): static
    {
        $this->moisReference = $moisReference;
        return $this;
    }

    public function isPriseEnCompteInfo(): bool
    {
        return $this->priseEnCompteInfo;
    }

    public function setPriseEnCompteInfo(bool $priseEnCompteInfo): static
    {
        $this->priseEnCompteInfo = $priseEnCompteInfo;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): static
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }

    public function getPriseEnCompteAt(): ?\DateTimeImmutable
    {
        return $this->priseEnCompteAt;
    }

    public function setPriseEnCompteAt(?\DateTimeImmutable $priseEnCompteAt): static
    {
        $this->priseEnCompteAt = $priseEnCompteAt;
        return $this;
    }

    public function getPriseEnCompteBy(): ?User
    {
        return $this->priseEnCompteBy;
    }

    public function setPriseEnCompteBy(?User $priseEnCompteBy): static
    {
        $this->priseEnCompteBy = $priseEnCompteBy;
        return $this;
    }

    public function getDisplayName(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s - %s %s (%s)',
            $this->type?->getLabel() ?? 'Non défini',
            $this->prenom,
            $this->nom,
            $this->numeroAgent
        );
    }
}