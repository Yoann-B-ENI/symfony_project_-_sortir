<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Service\Censuror;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{


    #[Route('/event/', name: 'event')]
    public function index(EntityManagerInterface $entityManager): Response
    {

        $eventsList = $entityManager->getRepository(Event::class)->findAll();
        return $this->render('event/index.html.twig', [

            'eventsList' => $eventsList,
        ]);
    }

    #[Route('/event/create', name: 'event_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, Censuror $censuror): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event->setDescription($censuror->purify($event->getDescription()));
            $imageFile = $form->get('img')->getData();

            if ($imageFile)
            {

                $filename = 'coverimg.jpg';
                $event->setImg($filename);
            }


            $entityManager->persist($event);
            $entityManager->flush();

            if ($imageFile) {

                $uploadDir = $this->getParameter('kernel.project_dir') . "/public/uploads/images/" . $event->getId() . "/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, true);
                }

                try {
                    $imageFile->move($uploadDir, $filename);
                } catch (FileException $e) {
                    throw new \Exception("Impossible de sauvegarder l'image.");
                }
            }





            return $this->redirectToRoute('event');

        }

        return $this->render('event/create.html.twig', [
            'form'=>$form,

        ]);
    }


    #[Route('/event/{id}/update', name: 'event_update')]
    public function update(Request $request, EntityManagerInterface $entityManager, Event $event): Response
    {

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('img')->getData();

            if ($imageFile) {
                $eventId = $event->getId();
                $uploadDir = $this->getParameter('kernel.project_dir') . "/public/uploads/images/$eventId/";

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, true);
                }

                $imageFile->move($uploadDir, 'coverimg.jpg');
                $event->setImg("coverimg.jpg");
            }

            $entityManager->flush();
            return $this->redirectToRoute('event');
        }
    return $this->render('event/update.html.twig', [
        'form'=>$form,
        'event'=>$event,
]);
    }

    #[Route('/event/{id}/delete', name: 'event_delete')]
public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

    return $this->redirectToRoute('event');
    }

    #[Route('/event/{id}', name: 'event_details', requirements: ['id' => '\d+'])]
    public function show(Event $event): Response
    {
        return $this->render('event/details.html.twig', [
            'event' => $event,
        ]);
    }


}
