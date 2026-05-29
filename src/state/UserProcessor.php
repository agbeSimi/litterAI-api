<?php

declare(strict_types=1);

namespace App\state;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class UserProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof User) {
            if (in_array('ROLE_USER_PROF', $data->getRoles())) {
                $codeVerification = (string) random_int(100000, 999999);
                $data->setCodeVerif($codeVerification);
                $expiration = (new \DateTime())->modify('+15 minutes');
                $data->setDateExpiration($expiration);
                $data->setStatutVerification(false);
            }
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
