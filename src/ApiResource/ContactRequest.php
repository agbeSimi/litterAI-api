<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
// Import indispensable pour la validation
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO pour gérer les demandes de contact via l'API.
 */
#[ApiResource(
    operations: [
        new Post(),
    ]
)]
class ContactRequest
{
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private string $nom;

    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    private string $prenom;

    #[Assert\NotBlank(message: "L'adresse email est obligatoire.")]
    #[Assert\Email(message: "Le format de l'adresse email est invalide.")]
    private string $email;

    #[Assert\NotBlank(message: "L'objet est obligatoire.")]
    private string $objet;

    #[Assert\NotBlank(message: 'Le type de requête est manquant.')]
    private string $typeRequete;

    #[Assert\NotBlank(message: 'Le message ne peut pas être vide.')]
    #[Assert\Length(
        max: 1000,
        maxMessage: 'Le message ne peut pas dépasser {{ limit }} caractères.'
    )]
    private string $commentaire;

    // --- GETTERS ET SETTERS ---

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getObjet(): string
    {
        return $this->objet;
    }

    public function setObjet(string $objet): void
    {
        $this->objet = $objet;
    }

    public function getTypeRequete(): string
    {
        return $this->typeRequete;
    }

    public function setTypeRequete(string $typeRequete): void
    {
        $this->typeRequete = $typeRequete;
    }

    public function getCommentaire(): string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): void
    {
        $this->commentaire = $commentaire;
    }
}
