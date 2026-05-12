<?php

declare(strict_types=1);

namespace App\state;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\ContactRequest;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactRequestProcessor implements ProcessorInterface
{
    public function __construct(private MailerInterface $mailer,
        private ParameterBagInterface $params)
    {
    }

    public function getMailer(): MailerInterface
    {
        return $this->mailer;
    }

    public function getParams(): ParameterBagInterface
    {
        return $this->params;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof ContactRequest) {
            $mailExpediteur = $data->getEmail();
            $objet = $data->getObjet();
            $mailEnvoyer = null;
            if ('MATHS' === $data->getTypeRequete()) {
                $mailDestinataire = $this->params->get('MAIL_MATHS');
                $mailEnvoyer = (new Email())
                    ->from('noreply@litterIA.com')
                    ->to($mailDestinataire)
                    ->subject($objet)
                    ->replyTo($mailExpediteur)
                    ->html(sprintf(
                        '<h3>Nouveau message de contact</h3>'.
                        '<p><strong>Élève :</strong> %s %s</p>'.
                        '<p><strong>Message :</strong><br>%s</p>',
                        $data->getPrenom(),
                        $data->getNom(),
                        nl2br($data->getCommentaire())));
            } else {
                $mailDestinataire = $this->params->get('MAIL_TECH');
                $mailEnvoyer = (new Email())
                    ->from('noreply@litterIA.com')
                    ->to($mailDestinataire)
                    ->subject($objet)
                    ->replyTo($mailExpediteur)
                    ->html(sprintf(
                        '<h3>Nouveau message de contact</h3>'.
                        '<p><strong>Élève :</strong> %s %s</p>'.
                        '<p><strong>Message :</strong><br>%s</p>',
                        $data->getPrenom(),
                        $data->getNom(),
                        nl2br($data->getCommentaire())));
            }
            $this->mailer->send($mailEnvoyer);
        }

        return $data;
    }
}
