<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. On crée un nouvel utilisateur vide
        $user = new User();

        // 2. On lui donne un pseudo et un rôle
        $user->setLogin('eleve_test');
        $user->setRoles(['ROLE_USER_ELEVE']);

        // 3. On crypte le mot de passe "1234" et on l'assigne
        $hashedPassword = $this->hasher->hashPassword($user, '1234');
        $user->setPassword($hashedPassword);

        // 4. On prépare la sauvegarde
        $manager->persist($user);

        // 5. On envoie tout dans la base de données MariaDB
        $manager->flush();
    }
}
