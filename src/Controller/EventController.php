<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\EventType;
use App\Service\Censuror;

use App\Service\ImageManagement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
                           #[Autowire('%event_photo_dir%')] string $photoDir,
                           #[Autowire('%event_photo_def_filename%')] string $filename,
                           ImageManagement $imageManagement,
    )
    : Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event->setDescription($censuror->purify($event->getDescription()));
            $event->setTitle($censuror->purify($event->getTitle()));
            $event->setOrganizer($this->getUser());
            $event->setCampus($this->getUser()->getCampus());

            $entityManager->persist($event);
            $entityManager->flush();

            if (!$event->getId()) {
                throw new \Exception("Erreur : l'événement n'a pas pu être créé.");
            }

            $imageFile = $form->get('img')->getData();
            if ($imageFile) {
                $imagePath = $imageManagement->upload($imageFile, $photoDir, $event->getId(), $filename);
                $event->setImg($imagePath);
                $entityManager->flush();
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

    #[Route('/event/{id}', name: 'event_details', requirements: ['id' => '\d+'])]
    public function show(Event $event): Response
    {
        // database call in parameter name
        return $this->render('event/details.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('event/addParticipant/{eventId}/{userId}', name: 'event_add_participant',
        requirements: ['eventId' => '\d+', 'userId' => '\d+'])]
    public function addParticipant(int $eventId, int $userId, EntityManagerInterface $em): RedirectResponse
    {
        // could become a kind of join
        $event = $em->getRepository(Event::class)->findOneBy(['id' => $eventId]);
        $user = $this->getUser();
        if ($user){
            if (!$user->getId() == $userId){
                $user = $em->getRepository(User::class)->findOneBy(['id' => $userId]);
            }
        }
        else{$user = $em->getRepository(User::class)->findOneBy(['id' => $userId]);}

        if (!$event){
            dump('Error : event not found in addParticipant with eventid: ' . $eventId);
            return $this->redirectToRoute('event_details', ['id' => $eventId]);
        }
        if (!$user){
            dump('Error : user not found in addParticipant with userid: ' . $userId);
            return $this->redirectToRoute('event_details', ['id' => $eventId]);
        }
        if (count($event->getParticipants()) >= $event->getNbMaxParticipants() ){
            dump('Warn : event has already maximum nb of participants');
            return $this->redirectToRoute('event_details', ['id' => $eventId]);
        }
        if ($event->getParticipants()->contains($user)){
            dump('Warn : event already has this user as a participant');
            return $this->redirectToRoute('event_details', ['id' => $eventId]);
        }

        if ($event->getOrganizer()->getId() == $userId){dump('Warn : event organizer added themselves');}
        $event->addParticipant($user);
        $em->flush();

        return $this->redirectToRoute('event_details', ['id' => $eventId]);
    }

    #[Route('event/removeParticipant/{eventId}/{userId}', name: 'event_remove_participant',
        requirements: ['eventId' => '\d+', 'userId' => '\d+'])]
    public function removeParticipant(int $eventId, int $userId, EntityManagerInterface $em): RedirectResponse
    {
        // could become a kind of join
        $event = $em->getRepository(Event::class)->findOneBy(['id' => $eventId]);
        $user = $this->getUser();
        if ($user){
            if (!($user->getId() == $userId)){
                $user = $em->getRepository(User::class)->findOneBy(['id' => $userId]);
            }
        }
        else{$user = $em->getRepository(User::class)->findOneBy(['id' => $userId]);}

        if (!$event){
            dump('Error : event not found in addParticipant with eventid: ' . $eventId);
            return $this->redirectToRoute('event_details', ['id' => $eventId]);
        }
        if (!$user){
            dump('Error : user not found in addParticipant with userid: ' . $userId);
            return $this->redirectToRoute('event_details', ['id' => $eventId]);
        }
        if (count($event->getParticipants()) == 0){
            dump('Warn : event already had no participants');
            return $this->redirectToRoute('event_details', ['id' => $eventId]);
        }
        if (!$event->getParticipants()->contains($user)){
            dump('Warn : event already didn\'t have this user as a participant');
            return $this->redirectToRoute('event_details', ['id' => $eventId]);
        }

        if ($event->getOrganizer() === $user){dump('Warn : event organizer removed themselves');}
        $event->removeParticipant($user);
        $em->flush();

        return $this->redirectToRoute('event_details', ['id' => $eventId]);
    }



}
