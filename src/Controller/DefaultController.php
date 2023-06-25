<?php

// Controlleur par défault qui rédirige vers la doc de l'API platforme 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route; 

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default')] // Route par défault qui redirige vers l'API platforme
    public function index(): Response
    {
        return $this->redirect('/api'); // Redirection vers / API 
    }
}