<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Repository\UserRepository;
use App\state\UserProcessor;
use App\state\UserVerificationProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_LOGIN', fields: ['login'])]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/register',
            processor: UserProcessor::class
        ),

        new Post(
            uriTemplate: '/verify-code',
            denormalizationContext: ['groups' => ['user:verify']],
            processor: UserVerificationProcessor::class
        ),
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:verify'])]
    private ?string $login = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailAcademique = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:verify'])]
    private ?string $codeVerif = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateExpiration = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statutVerification = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->login;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Chaque utilisateur doit avoir au moins le ROLE_USER pour le système de sécurité de Symfony
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getMailAcademique(): ?string
    {
        return $this->mailAcademique;
    }

    public function setMailAcademique(?string $mailAcademique): static
    {
        $this->mailAcademique = $mailAcademique;

        return $this;
    }

    public function getCodeVerif(): ?string
    {
        return $this->codeVerif;
    }

    public function setCodeVerif(?string $codeVerif): static
    {
        $this->codeVerif = $codeVerif;

        return $this;
    }

    public function getDateExpiration(): ?\DateTime
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?\DateTime $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    public function isStatutVerification(): ?bool
    {
        return $this->statutVerification;
    }

    public function setStatutVerification(?bool $statutVerification): static
    {
        $this->statutVerification = $statutVerification;

        return $this;
    }
}
