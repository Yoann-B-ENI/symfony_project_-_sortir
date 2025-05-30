<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\EventFilterType;
use App\Form\EventType;
use App\Message\NotificationType;
use App\Repository\StatusRepository;
use App\Service\Censuror;
use App\Service\EventStatusService;
use App\Service\ImageManagement;
use App\Service\NotifMessageManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\EventNotification;
final class EventController extends AbstractController
{
    public function __construct(private NotifMessageManager $notifManager)
    {    }


    #[Route('/event/', name: 'event')]
    public function index(Request $request,  EntityManagerInterface $entityManager, EventStatusService $eventStatusService ): Response
    {

        $form = $this->createForm(EventFilterType::class , null, [
            'method' => 'GET',
        ]);
        $form->handleRequest($request);

        $campus = $form->get('campus')->getData();
        $organizer = $form->get('organizer')->getData();
        $categories = $form->get('category')->getData();
        $status = $form->get('status')->getData();

        $campusId = $campus ? $campus->getId() : null;
        $organizerId = $organizer ? $organizer->getId() : null;
        $categoryIds = $categories ? array_map(function($category) {
            return $category->getId();
        }, $categories->toArray()) : null;
        $statusId = $status ? $status->getId() : null;
        $userId = $this->getUser() ? $this->getUser()->getId() : null;


        $eventsList = $entityManager->getRepository(Event::class)->findByFilters($campusId, $organizerId, $categoryIds, $statusId, $userId);


        foreach ($eventsList as $event) {
            $eventStatusService->checkAndUpdates($event);
        }
        return $this->render('event/index.html.twig', [
            'eventsList' => $eventsList,
            'form' => $form,
            'currentUser' => $this->getUser(),
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
            $event->addParticipant($this->getUser());

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
        #[Autowire('%event_photo_def_filename%')] string $filename,
        ImageManagement $imageManagement,
    ): Response {

        if ($event->getStatus()->getName() === 'Annulé') {
            $this->addFlash('error', 'Cet événement a été annulé et ne peut plus être modifié.');
            return $this->redirectToRoute('event'); // Redirection vers la liste
        }

        if ($this->getUser() !== $event->getOrganizer()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cet événement.');
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('img')->getData();
            $event->setDescription($censuror->purify($event->getDescription()));
            $event->setTitle($censuror->purify($event->getTitle()));
            $event->setOrganizer($this->getUser());
            $event->setCampus($this->getUser()->getCampus());


            if ($imageFile) {
                $newImagePath = $imageManagement->updateImage(
                    $event->getImg(),  // L'ancienne image
                    $imageFile,        // La nouvelle image
                    $photoDir,         // Le répertoire de base
                    $event->getId(),   // L'ID de l'événement
                    $filename          // Nom de base du fichier
                );

                $event->setImg($newImagePath);
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
                           Event $event,
                           EntityManagerInterface $entityManager,
                           ImageManagement $imageManagement,
                           #[Autowire('%event_photo_dir%')] string $photoDir,
    ): Response
    {
        if ($this->getUser() !== $event->getOrganizer()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cet événement.');
        }

        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $imageManagement->deleteImage($photoDir, $event->getId());
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event');
    }

    #[Route('/event/archive{id}', name: 'event_archive')]
    public function ArchiveEvent(Event $event, StatusRepository $statusRepository, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Récupérer le statut "Archivé"
        $statusArchive = $statusRepository->findOneBy(['name' => 'Archivé']);

        // Vérifier si le statut existe
        if (!$statusArchive) {
            $this->addFlash('error', 'Le statut Archivé n\'existe pas.');
            return $this->redirectToRoute('event'); // Rediriger vers la liste des événements
        }

        // Modifier le statut de l'événement
        $event->setStatus($statusArchive);
        $entityManager->persist($event);
        $entityManager->flush();

        // move to the event listener later
        $this->notifManager->createMessage("L'évènement " . $event->getTitle() . " a été archivé. ",
            false, ['ROLE_ADMIN'], null);
        $this->notifManager->createMessage("Votre évènement " . $event->getTitle() . " a été archivé. ",
            true, ['ROLE_USER'], $event->getOrganizer());
        foreach ($event->getParticipants() as $p) {
            $this->notifManager->createMessage("L'évènement " . $event->getTitle() . " auquel vous avez participé a été archivé. ",
                false, ['ROLE_USER'], $p);
        }


        // Message de confirmation
        $this->addFlash('success', 'L\'événement a été archivé.');

        return $this->redirectToRoute('event'); // Rediriger après l'archivage
    }

    #[Route('/event/{id}', name: 'event_details', requirements: ['id' => '\d+'])]
    public function show(Event $event, EventStatusService $eventStatusService): Response
    {
        $statusUpdated = $eventStatusService->checkAndUpdates($event);


        if ($statusUpdated) {
            $this->addFlash('info', 'Le statut de l\'événement a été mis à jour automatiquement.');
        }

        $location = $event->getLocation();
        $hasValidCoordinates = $location &&
            $location->getLatitude() !== null &&
            $location->getLongitude() !== null;



        return $this->render('event/details.html.twig', [
            'event' => $event,
            'currentUser' => $this->getUser(),
            'hasValidCoordinates' => $hasValidCoordinates,
            'latitude' => $hasValidCoordinates ? floatval($location->getLatitude()) : null,
            'longitude' => $hasValidCoordinates ? floatval($location->getLongitude()) : null
        ]);
    }


    /** Gestion des participants aux évènements et envoie des mails
     * @param int $eventId
     * @param int $userId
     * @param EntityManagerInterface $em
     * @param MessageBusInterface $messageBus
     * @return RedirectResponse
     * @throws \DateMalformedStringException
     */

    #[Route('/event/cancel/{id}', name: 'event_cancel')]
    public function cancelEvent(
        Event $event,
        StatusRepository $statusRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ): RedirectResponse
    {
        // Récupérer le statut "Annulé"
        $statusAnnule = $statusRepository->findOneBy(['name' => 'Annulé']);

        // Vérifier si le statut existe
        if (!$statusAnnule) {
            $this->addFlash('error', 'Le statut Annulé n\'existe pas.');
            return $this->redirectToRoute('event'); // Rediriger vers la liste des événements
        }

        // Modifier le statut de l'événement
        $event->setStatus($statusAnnule);

        $entityManager->persist($event);
        $entityManager->flush();

        // move to the event listener later
        $this->notifManager->createMessage("L'évènement " . $event->getTitle() . " a été annulé. ",
            false, ['ROLE_ADMIN'], null);
        $this->notifManager->createMessage("Votre évènement " . $event->getTitle() . " a été annulé. ",
            true, ['ROLE_USER'], $event->getOrganizer());
        foreach ($event->getParticipants() as $p) {
            $this->notifManager->createMessage("L'évènement " . $event->getTitle() . " auquel vous participiez a été annulé. ",
                false, ['ROLE_USER'], $p);
        }
        //Envoie du mail d'annulation aux participants
        $messageBus->dispatch(new EventNotification(
            $event->getId(),
            null,
            NotificationType::CANCELLATION
        ));

        // Message de confirmation
        $this->addFlash('success', 'L\'événement a été annulé.');

        return $this->redirectToRoute('event'); // Rediriger après l'annulation
    }

    #[Route('event/addParticipant/{eventId}/{userId}', name: 'event_add_participant',
        requirements: ['eventId' => '\d+', 'userId' => '\d+'])]
    public function addParticipant(
        int $eventId,
        int $userId,
        EntityManagerInterface $em,
        MessageBusInterface $messageBus,

    ): RedirectResponse
    {

        // could become a kind of join
        $event = $em->getRepository(Event::class)->findOneBy(['id' => $eventId]);
        $user = $this->getUser();
        if ($user) {
            if (!$user->getId() == $userId) {
                $user = $em->getRepository(User::class)->findOneBy(['id' => $userId]);
            }
        } else {
            $user = $em->getRepository(User::class)->findOneBy(['id' => $userId]);
        }

        if (!$event) {
            dump('Error : event not found in addParticipant with eventid: ' . $eventId);
            return $this->redirectToRoute('event_details', ['id' => $eventId]);
        }
        if (!$user) {
            dump('Error : user not found in addParticipant with userid: ' . $userId);
            return $this->redirectToRoute('event_details', ['id' => $eventId]);
        }
        if (count($event->getParticipants()) >= $event->getNbMaxParticipants()) {
            dump('Warn : event has already maximum nb of participants');
            return $this->redirectToRoute('event_details', ['id' => $eventId]);
        }
        if ($event->getParticipants()->contains($user)) {
            dump('Warn : event already has this user as a participant');
            return $this->redirectToRoute('event_details', ['id' => $eventId]);
        }

        if ($event->getOrganizer()->getId() == $userId) {
            dump('Warn : event organizer added themselves');
        }

        $event->addParticipant($user);
        $em->flush();

        // move to the event listener later
        $this->notifManager->createMessage($user->getUsername() . "s'est inscrit à votre évènement " . $event->getTitle() . ". ",
            false, ['ROLE_USER'], $event->getOrganizer());
        $this->notifManager->createMessage("Vous êtes inscrit à l'évènement " . $event->getTitle() . ". ",
            false, ['ROLE_USER'], $user);

        // mail d'inscription à l'event
        $messageBus->dispatch(new EventNotification(
            $eventId,
            $user->getId(),
            NotificationType::REGISTRATION
        ));

        //Rappel mail 48h avant l'event
        $startsAt = $event->getStartsAt();
        $now = new \DateTimeImmutable();

        $reminderTime =  $startsAt instanceof \DateTimeImmutable
            ? $startsAt->sub(new \DateInterval('PT48H'))
            : (new \DateTimeImmutable($startsAt->format('Y-m-d H:i:s')))->sub(new \DateInterval('PT48H'));

        if ($reminderTime > $now && $startsAt > $now) {
            $messageBus->dispatch(new EventNotification(
                $eventId,
                $user->getId(),
                NotificationType::REMINDER
            ));
        }

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

        // move to the event listener later
        $this->notifManager->createMessage($user->getUsername() . "s'est désinscrit de votre évènement " . $event->getTitle() . ". ",
            false, ['ROLE_USER'], $event->getOrganizer());
        $this->notifManager->createMessage("Vous êtes retiré de l'évènement " . $event->getTitle() . ". ",
            true, ['ROLE_USER'], $user);

        return $this->redirectToRoute('event_details', ['id' => $eventId]);
    }
}