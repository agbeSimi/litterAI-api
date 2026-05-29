<?php

declare(strict_types=1);

namespace App\state;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserVerificationProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private UserRepository $userRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof User) {
            // 1. On récupère le vrai utilisateur complet en BDD grâce au login envoyé par React
            $userComplet = $this->userRepository->findOneBy(['login' => $data->getLogin()]);

            // Si l'utilisateur n'existe pas en base de données
            if (!$userComplet) {
                throw new NotFoundHttpException('Utilisateur non trouvé.');
            }

            if ($data->getCodeVerif() === $userComplet->getCodeVerif()
                && new \DateTime() < $userComplet->getDateExpiration()) {
                $userComplet->setStatutVerification(true);
                $userComplet->setCodeVerif(null);
            }
            return $this->persistProcessor->process($userComplet, $operation, $uriVariables, $context);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
