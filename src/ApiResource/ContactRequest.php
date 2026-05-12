<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\state\GroqProcessor;

#[ApiResource()]
class ContactRequest
{
    private string $nom;
    private string $prenom;
    private string $email;
    private string $objet;
    private string $typeRequete;

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
    private string $commentaire;

}
