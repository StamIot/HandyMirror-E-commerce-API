<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountConfirmationController extends AbstractController
{
    /**
     * @Route("/confirm-account/{token}", name="confirm_account")
     */
    public function confirmAccount(string $token): Response
    {
        // Recherche de l'utilisateur correspondant au token dans la base de données
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['confirmationToken' => $token]);

        // Vérifier si un utilisateur a été trouvé avec le token de confirmation
        if (!$user) {
            throw $this->createNotFoundException('Token de confirmation invalide');
        }

        // Marquer l'utilisateur comme confirmé
        $user->setConfirmed(true);
        $user->setConfirmationToken(null);
        $entityManager->flush();

        // Rediriger l'utilisateur vers une page de confirmation ou afficher un message de confirmation
        return $this->render('account_confirmation/confirmed.html.twig');
    }
}
