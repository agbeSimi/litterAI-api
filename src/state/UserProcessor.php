<?php

declare(strict_types=1);

namespace App\state;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private UserPasswordHasherInterface $passwordHasher,
        private MailerInterface $mailer)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof User) {
            if ($data->getPassword()) {
                $hashedPassword = $this->passwordHasher->hashPassword($data, $data->getPassword());
                $data->setPassword($hashedPassword);
            }

            if (in_array('ROLE_USER_PROF', $data->getRoles())) {
                $emailProf = $data->getMailAcademique();
                if (!preg_match('/@(ac-[a-z]+|education)\.gouv?\.fr$/i', $emailProf)
                    || preg_match('/(eleve|etudiant|student|lycee|clg)/i', $emailProf)) {
                    throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("L'adresse email fournie n'est pas une adresse académique réservée aux enseignants.");
                }

                $codeVerification = (string) random_int(100000, 999999);
                $data->setCodeVerif($codeVerification);
                $expiration = (new \DateTime())->modify('+15 minutes');
                $data->setDateExpiration($expiration);
                $data->setStatutVerification(false);

                $email = (new Email())
                    ->from('noreply@litterIA.com')
                    ->to($emailProf)
                    ->subject('Votre code de vérification Enseignant')
                    ->html('<p>Bonjour,</p><p>Voici votre code de vérification pour finaliser votre inscription : <strong>'.$codeVerification.'</strong></p><p>Ce code est valable pendant 15 minutes.</p>');

                $this->mailer->send($email);
            } else {
                $data->setRoles(['ROLE_USER_ELEVE']);
                $data->setStatutVerification(true);


            }
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
