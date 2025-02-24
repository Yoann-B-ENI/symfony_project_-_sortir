<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController{
    #[Route('/', name: 'main')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig', [
            'title' => 'Accueil',
        ]);
    }

    #[Route('/about', name: 'main_about')]
    public function about(): Response
    {
        return $this->render('main/about.html.twig', [
            'title' => 'A propos',
        ]);
    }

}
