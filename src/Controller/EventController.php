<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/event/', name: 'event')]
    public function index(): Response
    {
        return $this->render('event/index.html.twig', [
            'title' => 'Faites votre évènement',
        ]);
    }

    #[Route('/event/create', name: 'event_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($event);
            $entityManager->flush();
            return $this->redirectToRoute('event');

        }


        return $this->render('event/create.html.twig', [
            'form'=>$form,

        ]);
    }



}
