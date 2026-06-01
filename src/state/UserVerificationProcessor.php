<?php

declare(strict_types=1);

namespace App\state;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserVerificationProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private UserRepository $userRepository,
        private JWTTokenManagerInterface $jwtManager,
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

            // 2. Vérification si le compte est déjà validé (évite de refaire le traitement)
            if (true === $userComplet->getStatutVerification()) {
                throw new BadRequestHttpException('Ce compte est déjà validé. Vous pouvez vous connecter.');
            }

            // 3. Match du code et contrôle du timing
            if ($data->getCodeVerif() === $userComplet->getCodeVerif()
                && new \DateTime() < $userComplet->getDateExpiration()) {
                $userComplet->setStatutVerification(true);
                $userComplet->setCodeVerif(null);
                $userComplet->setDateExpiration(null); // Optionnel : on nettoie la date

                // Sauvegarde de la validation
                $this->persistProcessor->process($userComplet, $operation, $uriVariables, $context);

                // Génération du Token de connexion automatique
                $token = $this->jwtManager->create($userComplet);

                return new JsonResponse([
                    'message' => 'Code vérifié avec succès. Connexion automatique réussie.',
                    'token' => $token,
                ], 200);
            }

            // Si le code est faux ou expiré, on lève une vraie erreur HTTP 400
            throw new BadRequestHttpException('Le code de vérification est incorrect ou a expiré.');
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
