<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Service\Censuror;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

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
    public function create(Request $request, EntityManagerInterface $entityManager, Censuror $censuror,
                           #[Autowire('%event_photo_dir%')] string $photoDir, #[Autowire('%event_photo_def_filename%')] string $filename): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event->setDescription($censuror->purify($event->getDescription()));
            $event->setTitle($censuror->purify($event->getTitle()));
            $event->setOrganizer($this->getUser());
            $event->setCampus($this->getUser()->getCampus());

            $imageFile = $form->get('img')->getData();

            if ($imageFile) {
                // cover_img -> cover_img.jpg/png/...
                $filename = $filename . '.' . $imageFile->guessExtension();
                $event->setImg($filename);
            }
            $entityManager->persist($event);
            $entityManager->flush();

            if (!$event->getId()) {
                throw new \Exception("Erreur : l'événement n'a pas pu être créé.");
            }

            if ($imageFile) {
                // events/images -> events/images/5
                $photoDir = $photoDir . "/" . $event->getId();
                $imageFile->move($photoDir, $filename);
            }

            return $this->redirectToRoute('event');
        }

        return $this->render('event/create.html.twig', [
            'form'=>$form,
        ]);
    }


    #[Route('/event/{id}/update', name: 'event_update')]
    public function update(
        Request $request,
        EntityManagerInterface $entityManager,
        Event $event, // database call
        Censuror $censuror,
        #[Autowire('%event_photo_dir%')] string $photoDir,
        #[Autowire('%event_photo_def_filename%')] string $filename
    ): Response {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('img')->getData();
            $event->setDescription($censuror->purify($event->getDescription()));
            $event->setTitle($censuror->purify($event->getTitle()));
            $event->setOrganizer($this->getUser());
            $event->setCampus($this->getUser()->getCampus());

            if ($imageFile) {
                // uploads/events -> uploads/events/5
                $eventPhotoDir = $photoDir . "/" . $event->getId();
                // cover_img -> cover_img.jpg/png/...
                $filename = $filename . '.' . $imageFile->guessExtension();
                $event->setImg($filename);
                $imageFile->move($eventPhotoDir, $filename);
            }

            $entityManager->flush();
            return $this->redirectToRoute('event_details', ['id' => $event->getId()]);
        }

        return $this->render('event/update.html.twig', [
            'form' => $form,
            'event' => $event,
        ]);
    }


    #[Route('/event/{id}/delete', name: 'event_delete')]
    public function delete(Request $request,
                       Event $event, // database call
                       EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {

            $photoDir = $this->getParameter('kernel.project_dir') . '/public/uploads/events/' . $event->getId();
            $filesystem = new Filesystem();

            $filesystem->remove($photoDir);


            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event');
    }
            // THIS WORKS
          //  $tempDir = $this->getParameter('kernel.project_dir');
           // $filesystem->remove([$tempDir.'\public\uploads\events/'.$event->getId()]);

            // DELETES EVERYTHING
            // $filesystem->remove([$photoDir . '/' . $event->getId() . '/']);
            // $filesystem->remove([$tempDir.'\public\uploads\events\\'.$event->getId()]);
            // $filesystem->remove([$tempDir.'/public/uploads/events/' . $event->getId()]);

            // DELETES NOTHING
            // $filesystem->remove($event->getImg());


    //return $this->redirectToRoute('event');
    //}

    #[Route('/event/{id}', name: 'event_details', requirements: ['id' => '\d+'])]
    public function show(Event $event): Response
    {
        // database call in parameter name

        return $this->render('event/details.html.twig', [
            'event' => $event,
        ]);
    }


}
