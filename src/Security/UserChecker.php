<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        // Cette méthode s'exécute AVANT la vérification du mot de passe (rien à faire ici)
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Cette méthode s'exécute APRÈS la vérification du mot de passe
        if (!$user instanceof User) {
            return;
        }

        // Si l'utilisateur est un professeur et qu'il n'est pas vérifié, on bloque !
        if (in_array('ROLE_USER_PROF', $user->getRoles()) && !$user->isStatutVerification()) {
            throw new CustomUserMessageAccountStatusException("Votre compte enseignant n'a pas encore été activé. Veuillez saisir le code envoyé par e-mail.");
        }
    }
}
