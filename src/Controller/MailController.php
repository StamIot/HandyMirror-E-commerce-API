<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailController extends AbstractController
{
    public function sendEmail(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('handymirrorproject@gmail.com')
            ->to('s.majorel@it-students.fr')
            ->subject('Test Email')
            ->text('Ceci est le début de la fin.');

        $mailer->send($email);

        return $this->render('email/sent.html.twig');
    }
}
