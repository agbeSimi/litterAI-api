<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\ClasseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ClasseRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['classe:read']]
        ),
        new Get(
            normalizationContext: ['groups' => ['classe:read']]
        ),
        new Post(
            denormalizationContext: ['groups' => ['classe:write']],
            security: "is_granted('ROLE_PROFESSEUR') or is_granted('ROLE_ADMIN')"
        ),
        new Patch(
            denormalizationContext: ['groups' => ['classe:update']],
            security: "is_granted('ROLE_ADMIN') or (is_granted('ROLE_PROFESSEUR') and object.getProfesseur() == user)",
            securityMessage: 'Vous ne pouvez modifier que vos propres classes.'
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN') or (is_granted('ROLE_PROFESSEUR') and object.getProfesseur() == user)",
            securityMessage: 'Vous ne pouvez supprimer que vos propres classes.'
        ),
    ]
)]
class Classe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['classe:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['classe:read', 'classe:write'])]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Groups(['classe:read'])]
    private ?string $nomPurifie = null;

    #[ORM\Column]
    #[Groups(['classe:read', 'classe:write', 'classe:update'])]
    private ?int $effectif = null;

    #[ORM\ManyToOne(inversedBy: 'classes')]
    #[Groups(['classe:read', 'classe:write'])]
    private ?User $professeur = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'classe')]
    private Collection $eleves;

    #[ORM\Column(type: 'json')]
    #[Groups(['classe:read', 'classe:write', 'classe:update'])]
    private array $modulesAutoriser = [1, 2, 3, 4];
    #[ORM\Column(length: 255)]
    #[Groups(['classe:read', 'classe:write', 'classe:update'])]
    private ?string $modeApprentissage = 'complet';

    public function __construct()
    {
        $this->eleves = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNomPurifie(): ?string
    {
        return $this->nomPurifie;
    }

    public function setNomPurifie(string $nomPurifie): static
    {
        $this->nomPurifie = $nomPurifie;

        return $this;
    }

    public function getEffectif(): ?int
    {
        return $this->effectif;
    }

    public function setEffectif(int $effectif): static
    {
        $this->effectif = $effectif;

        return $this;
    }

    public function getProfesseur(): ?User
    {
        return $this->professeur;
    }

    public function setProfesseur(?User $professeur): static
    {
        $this->professeur = $professeur;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getEleves(): Collection
    {
        return $this->eleves;
    }

    public function addElefe(User $elefe): static
    {
        if (!$this->eleves->contains($elefe)) {
            $this->eleves->add($elefe);
            $elefe->setClasse($this);
        }

        return $this;
    }

    public function removeElefe(User $elefe): static
    {
        if ($this->eleves->removeElement($elefe)) {
            // set the owning side to null (unless already changed)
            if ($elefe->getClasse() === $this) {
                $elefe->setClasse(null);
            }
        }

        return $this;
    }

    public function getModulesAutoriser(): array
    {
        return $this->modulesAutoriser;
    }

    public function setModulesAutoriser(array $modulesAutoriser): static
    {
        $this->modulesAutoriser = $modulesAutoriser;

        return $this;
    }

    public function getModeApprentissage(): ?string
    {
        return $this->modeApprentissage;
    }

    public function setModeApprentissage(string $modeApprentissage): static
    {
        $this->modeApprentissage = $modeApprentissage;

        return $this;
    }
}
