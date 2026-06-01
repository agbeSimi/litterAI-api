<?php

declare(strict_types=1);

namespace App\state;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Classe;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClasseProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private Security $security,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof Classe) {
            $professeur = $this->security->getUser();
            $data->setProfesseur($professeur);
            $nomNettoye = preg_replace('/[^a-zA-Z0-9]/', '', $data->getNom());
            $data->setNomPurifie($nomNettoye);
            $this->persistProcessor->process($data, $operation, $uriVariables, $context);

            $effectif = $data->getEffectif();
            for ($i = 1; $i <= $effectif; ++$i) {
                $numero = sprintf('%02d', $i);
                $loginAnonyme = 'litterai_'.strtolower($nomNettoye).'_'.$numero;
                $mdpAleatoire = bin2hex(random_bytes(4));
                $eleve = new User();
                $eleve->setLogin($loginAnonyme);
                $eleve->setPassword($this->passwordHasher->hashPassword($eleve, $mdpAleatoire));
                $eleve->setRoles(['ROLE_USER_ELEVE']);
                $eleve->setClasse($data);
                $this->persistProcessor->process($eleve, $operation, $uriVariables, $context);

                $listeElevesCrees[] = [
                    'login' => $loginAnonyme,
                    'motDePasse' => $mdpAleatoire,
                ];
            }

            return new JsonResponse([
                'message' => 'Classe et comptes élèves générés avec succès.',
                'classe' => [
                    'id' => $data->getId(),
                    'nom' => $data->getNom(),
                    'nomPurifie' => $data->getNomPurifie(),
                    'effectif' => $data->getEffectif(),
                ],
                'eleves' => $listeElevesCrees,
            ], 201);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
