<?php

declare(strict_types=1);

namespace App\Doctrine\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Classe;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class ClassesDuProfesseurExtension implements QueryCollectionExtensionInterface
{
    private Security $security;

    // On injecte le service Security de Symfony pour connaître l'utilisateur connecté
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (Classe::class !== $resourceClass) {
            return;
        }
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->security->getUser();
        if ($user && in_array('ROLE_USER_PROFESSEUR', $user->getRoles())) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder->andWhere($rootAlias.'.professeur = :prof');
            $queryBuilder->setParameter('prof', $user);
        }
    }
}
