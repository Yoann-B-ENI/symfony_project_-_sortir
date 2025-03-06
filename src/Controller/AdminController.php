<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Status;
use App\Entity\User;
use App\Form\AdminAddEventType;
use App\Form\AdminEditEventType;
use App\Form\AdminAddUserType;
use App\Form\AdminEditUserType;
use App\Form\ChangePasswordFormType;
use App\Form\EventFilterType;
use App\Form\EventType;
use App\Form\LocationType;
use App\Repository\CampusRepository;
use App\Repository\EventRepository;
use App\Repository\LocationRepository;
use App\Repository\ResetPasswordRequestRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

final class AdminController extends AbstractController
{

    private $entityManager;
    private $resetPasswordHelper;

    // Injection de l'EntityManagerInterface et ResetPasswordHelperInterface via le constructeur
    public function __construct(EntityManagerInterface $entityManager, ResetPasswordHelperInterface $resetPasswordHelper)
    {
        $this->entityManager = $entityManager;
        $this->resetPasswordHelper = $resetPasswordHelper;
    }

    #[Route('/admin', name: 'admin', methods: ['GET', 'POST'])]
    public function index(UserRepository $userRepository, EventRepository $eventRepository, LocationRepository $locationRepo, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer les valeurs de recherche
        $searchTerm = $request->query->get('searchTerm');
        $searchLocation = $request->query->get('searchLocation');

        // Recherche des utilisateurs et événements
        if ($searchTerm) {
            // Recherche des utilisateurs par username, lastname ou email
            $users = $userRepository->createQueryBuilder('u')
                ->where('u.username LIKE :search OR u.lastname LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $searchTerm . '%')
                ->getQuery()
                ->getResult();

            // Recherche des événements liés à ces utilisateurs (en tant qu'organisateur)
            $events = $eventRepository->createQueryBuilder('e')
                ->join('e.organizer', 'o')
                ->where('o.username LIKE :search OR o.lastname LIKE :search OR o.email LIKE :search')
                ->setParameter('search', '%' . $searchTerm . '%')
                ->getQuery()
                ->getResult();
        } else {
            // Si pas de recherche, récupérer tous les utilisateurs et événements
            $users = $userRepository->findAll();
            $events = $eventRepository->findAll();
        }

        // Recherche des lieux (par zipcode ou townname)
        if ($searchLocation) {
            $locations = $locationRepo->createQueryBuilder('l')
                ->where('l.zipcode LIKE :search OR l.townname LIKE :search')
                ->setParameter('search', '%' . $searchLocation . '%')
                ->getQuery()
                ->getResult();
        } else {
            // Si aucune recherche, récupérer tous les lieux
            $locations = $locationRepo->findAll();
        }

        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'events' => $events,
            'locations' => $locations,
            'searchTerm' => $searchTerm,
            'searchLocation' => $searchLocation,
        ]);
    }


    #[Route('/admin/details/user/{id}', name: 'admin_details_user', requirements: ['id' => '\d+'])]
    public function details_user(int $id, UserRepository $userRepository, EventRepository $eventRepository): Response
    {
        $user = $userRepository->find($id);
        $events = $eventRepository->findBy(['organizer' => $user]);
        $eventParticipating = $eventRepository->findByParticipatingUser($user);

        if (!$user) {
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas.');
        }

        return $this->render('admin/detailsuser.html.twig', [
            'user' => $user,
            'events' => $events,
            'eventParticipating' => $eventParticipating,
        ]);
    }

    #[Route('/admin/delete/user/{id}', name: 'admin_delete_user', methods: ['POST'])]
    public function delete(int $id, UserRepository $userRepository, EventRepository $eventRepository, EntityManagerInterface $entityManager, ResetPasswordRequestRepository $resetPasswordRequestRepository): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas.');
        }

        // Récupérer les événements créés par l'utilisateur
        $events = $eventRepository->findBy(['organizer' => $user]);

        // Supprimer les événements
        foreach ($events as $event) {
            $entityManager->remove($event);
        }

        // Supprimer les tokens de réinitialisation de mot de passe de la base de données
        $resetPasswordRequests = $resetPasswordRequestRepository->findBy(['user' => $user]);

        foreach ($resetPasswordRequests as $resetPasswordRequest) {
            $entityManager->remove($resetPasswordRequest);
        }

        // Supprimer l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('error', 'Utilisateur, ses événements & participations supprimés avec succès.');

        return $this->redirectToRoute('admin');
    }

    #[Route('/admin/edit/user/{id}', name: 'admin_edit_user', methods: ['GET', 'POST'])]
    public function edit(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas.');
        }

        // Création du formulaire
        $form = $this->createForm(AdminEditUserType::class, $user);
        $form->handleRequest($request);

        // Traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('warning', 'Utilisateur modifié avec succès.');

            return $this->redirectToRoute('admin');
        }

        // Affichage du formulaire dans la vue
        return $this->render('admin/edituser.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }

    #[Route('/admin/add/user', name: 'admin_add_user')]
    public function addUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer, UrlGeneratorInterface $urlGenerator): Response
    {
        $user = new User();

        // Créer le formulaire
        $form = $this->createForm(AdminAddUserType::class, $user);
        $form->handleRequest($request);

        // Traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le mot de passe brut
            $plainPassword = $form->get('plainPassword')->getData();

            // Vérifier si le mot de passe est valide
            if ($plainPassword !== null) {
                // Hacher le mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                // Stocker le mot de passe haché dans la base de données
                $user->setPassword($hashedPassword);
            } else {
                // Gérer le cas où le mot de passe est null
                $this->addFlash('error', 'Le mot de passe est requis.');
                return $this->render('admin/adduser.html.twig', [
                    'form' => $form,
                ]);
            }

            // Définir l'utilisateur comme vérifié
            $user->setIsVerified(true);

            // Persister l'utilisateur
            $entityManager->persist($user);
            $entityManager->flush();

            // Générer un token de réinitialisation de mot de passe
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);

            // Récupérer le token réel sous forme de chaîne de caractères
            $resetTokenString = $resetToken->getToken(); // Utilisez getToken() pour obtenir la chaîne

            // Envoi de l'email
            $email = (new Email())
                ->from('noreply@sortir.com')
                ->to($user->getEmail())
                ->subject('Votre compte a été créé')
                ->html('<p>Votre compte a été créé. Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe lors de votre première connexion :</p>
        <a href="' . $urlGenerator->generate('reset_password', ['token' => $resetTokenString], UrlGeneratorInterface::ABSOLUTE_URL) . '">Réinitialiser mon mot de passe</a>');

            $mailer->send($email);

            // Message de succès
            $this->addFlash('success', 'Utilisateur ajouté avec succès.');

            // Rediriger vers la page admin
            return $this->redirectToRoute('admin');
        }

        // Affichage du formulaire dans la vue
        return $this->render('admin/adduser.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reset-password/{token}', name: 'reset_password')]
    public function resetPassword(
        string $token,
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Valider le token et récupérer l'utilisateur
        try {
            // Validation du token et récupération de l'utilisateur associé
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('error', 'Le lien de réinitialisation est invalide ou a expiré.');
            return $this->redirectToRoute('app_login'); // Rediriger ou afficher un message d'erreur
        }

        // Créer un formulaire de changement de mot de passe
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le mot de passe du formulaire
            $newPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword); // Hacher le mot de passe
            $user->setPassword($hashedPassword);

            // Supprimer le token de réinitialisation après utilisation
            $this->resetPasswordHelper->removeResetRequest($token);

            // Sauvegarder le nouveau mot de passe dans la base de données
            $this->entityManager->flush();

            // Nettoyer la session après reset
            $request->getSession()->remove('ResetPasswordToken');

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');

            // Rediriger vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        // Afficher le formulaire dans la vue
        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/admin/details/event/{id}', name: 'admin_details_event', requirements: ['id' => '\d+'])]
    public function details_event(int $id, EventRepository $eventRepository, UserRepository $userRepository, Request $request): Response
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Cet évènement n\'existe pas.');
        }

        // Récupérer la valeur de la recherche du formulaire GET pour les utilisateurs à ajouter (searchUsername)
        $searchUsername = $request->query->get('searchUsername', '');  // Valeur par défaut vide

        // Si une recherche est effectuée, on filtre les utilisateurs
        if ($searchUsername) {
            $users = $userRepository->createQueryBuilder('u')
                ->where('u.username LIKE :username OR u.lastname LIKE :lastName OR u.email LIKE :email')
                ->setParameter('username', '%' . $searchUsername . '%')
                ->setParameter('lastName', '%' . $searchUsername . '%')
                ->setParameter('email', '%' . $searchUsername . '%')
                ->getQuery()
                ->getResult();
        } else {
            // Si aucune recherche, on récupère tous les utilisateurs
            $users = $userRepository->findAll();
        }

        // Filtrer les utilisateurs qui ne sont pas encore participants
        $nonParticipants = array_filter($users, fn($user) => !$event->getParticipants()->contains($user));

        // Récupérer la valeur de la recherche du formulaire GET pour les participants (searchParticipant)
        $searchParticipant = $request->query->get('searchParticipant', '');  // Valeur par défaut vide

        // Si une recherche est effectuée parmi les participants, on filtre
        if ($searchParticipant) {
            $participants = $event->getParticipants()->filter(function ($participant) use ($searchParticipant) {
                return str_contains(strtolower($participant->getUsername()), strtolower($searchParticipant)) ||
                    str_contains(strtolower($participant->getLastName()), strtolower($searchParticipant)) ||
                    str_contains(strtolower($participant->getEmail()), strtolower($searchParticipant));
            });
        } else {
            // Sinon, on récupère tous les participants
            $participants = $event->getParticipants();
        }

        // Rendre la vue avec les résultats et les valeurs des recherches
        return $this->render('admin/detailsevent.html.twig', [
            'event' => $event,
            'users' => $nonParticipants, // Passer les utilisateurs non participants
            'participants' => $participants, // Passer les participants filtrés
            'searchUsername' => $searchUsername, // Passer la valeur de recherche des utilisateurs à ajouter
            'searchParticipant' => $searchParticipant, // Passer la valeur de recherche des participants
        ]);
    }

    #[Route('/admin/delete/event/{id}', name: 'admin_delete_event', methods: ['POST'])]
    public function deleteevent(int $id, EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Cet évènement n\'existe pas.');
        }

        $entityManager->remove($event);
        $entityManager->flush();
        $this->addFlash('error', 'Evènement supprimé avec succès.');

        return $this->redirectToRoute('admin');

    }

    #[Route('/admin/edit/event/{id}', name: 'admin_edit_event', methods: ['GET', 'POST'])]
    public function editevent(int $id, EventRepository $eventRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Cet évènement n\'existe pas.');
        }

        // Créer le formulaire
        $form = $this->createForm(AdminEditEventType::class, $event);
        $form->handleRequest($request);

        // Vérifie si un organisateur a été sélectionné et si son campus a changé
        $selectedOrganizer = $event->getOrganizer();
        $campus = $selectedOrganizer ? $selectedOrganizer->getCampus() : null;

        // Traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Si un organisateur a changé, on met à jour le campus
            $organizer = $event->getOrganizer();
            if ($organizer) {
                if ($organizer->getCampus()) {
                    $event->setCampus($organizer->getCampus());
                }
            }

            // Persister les changements
            $entityManager->flush();

            // Message de succès
            $this->addFlash('warning', 'Evènement modifié avec succès.');

            // Rediriger vers la page admin
            return $this->redirectToRoute('admin');
        }

        // Affichage du formulaire dans la vue
        return $this->render('admin/editevent.html.twig', [
            'form' => $form,
            'event' => $event,
            'selectedOrganizer' => $selectedOrganizer,
            'campus' => $campus, // Passe le campus à la vue pour qu'il soit affiché
        ]);
    }

    #[Route('/admin/add/event', name: 'admin_add_event', methods: ['GET', 'POST'])]
    public function addevent(EntityManagerInterface $entityManager, Request $request): Response
    {
        $event = new Event();

        // Créer le formulaire
        $form = $this->createForm(AdminAddEventType::class, $event);
        $form->handleRequest($request);

        // Traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Si un organisateur est sélectionné, mettre à jour le campus
            if ($event->getOrganizer()?->getCampus()) {
                $event->setCampus($event->getOrganizer()->getCampus());
            }

            // Persister l'événement
            $entityManager->persist($event);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Évènement ajouté avec succès.');

            // Rediriger vers la page admin
            return $this->redirectToRoute('admin');
        }

        // Affichage du formulaire dans la vue
        return $this->render('admin/addevent.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/admin/get-campus/{userId}', name: 'admin_get_campus', methods: ['GET'])] // Route pour précharger le campus (Admin - créer un Event)
    public function getCampus(UserRepository $userRepository, int $userId): JsonResponse
    {
        $user = $userRepository->find($userId);

        if (!$user || !$user->getCampus()) {
            return new JsonResponse(['campus' => null], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['campus' => $user->getCampus()->getName()]);
    }

    #[Route('/admin/event/{eventId}/addParticipant/{userId}', name: 'admin_event_add_participant', requirements: ['eventId' => '\d+', 'userId' => '\d+'])]
    public function adminAddParticipant(int $eventId, int $userId, EntityManagerInterface $em): RedirectResponse
    {
        $event = $em->getRepository(Event::class)->find($eventId);
        $user = $em->getRepository(User::class)->find($userId);

        // Vérification si l'événement et l'utilisateur existent
        if (!$event || !$user) {
            $this->addFlash('error', 'Événement ou utilisateur introuvable.');
            return $this->redirectToRoute('admin_details_event', ['id' => $eventId]);
        }

        // Vérification si le nombre maximal de participants est atteint
        if (count($event->getParticipants()) >= $event->getNbMaxParticipants()) {
            $this->addFlash('info', 'Le nombre maximal de participants a été atteint pour cet événement (Le participant n\'a pas été ajouté).');
            return $this->redirectToRoute('admin_details_event', ['id' => $eventId]);
        }

        // Vérification si l'utilisateur participe déjà à l'événement
        if (!$event->getParticipants()->contains($user)) {
            $event->addParticipant($user);
            $em->flush();
            $this->addFlash('success', 'Participant ajouté avec succès.');
        } else {
            $this->addFlash('warning', 'Cet utilisateur participe déjà à l\'événement.');
        }

        return $this->redirectToRoute('admin_details_event', [
            'id' => $eventId,
        ]);
    }

    #[Route('/admin/event/{eventId}/removeParticipant/{userId}', name: 'admin_event_remove_participant', requirements: ['eventId' => '\d+', 'userId' => '\d+'])]
    public function adminRemoveParticipant(int $eventId, int $userId, EntityManagerInterface $em): RedirectResponse
    {
        $event = $em->getRepository(Event::class)->find($eventId);
        $user = $em->getRepository(User::class)->find($userId);

        if (!$event || !$user) {
            $this->addFlash('error', 'Événement ou utilisateur introuvable.');
            return $this->redirectToRoute('admin_details_event', ['id' => $eventId]);
        }

        if ($event->getParticipants()->contains($user)) {
            $event->removeParticipant($user);
            $em->flush();
            $this->addFlash('error', 'Participant retiré avec succès.');
        } else {
            $this->addFlash('warning', 'Cet utilisateur ne participe pas à l\'événement.');
        }

        return $this->redirectToRoute('admin_details_event', ['id' => $eventId]);
    }

    #[Route('/admin/import-users', name: 'admin_import_users')]
    public function importUsersForm(): Response
    {
        return $this->render('admin/import_users.html.twig');
    }

    #[Route('/admin/import-users/upload', name: 'admin_import_users_upload', methods: ['POST'])]
    public function importUsers(
        Request $request,
        CampusRepository $campusRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): Response
    {
        $file = $request->files->get('csv_file');

        if ($file) {
            $handle = fopen($file->getRealPath(), 'r');

            // Ignorer la première ligne (en-têtes)
            fgetcsv($handle, 1000, ',');

            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                // Récupération du campus en fonction de son ID
                $campusId = (int) $data[0];  // Convertir en entier pour éviter les erreurs
                $campus = $campusRepository->find($campusId);

                if (!$campus) {
                    throw new \Exception("Le campus avec l'ID '{$campusId}' est introuvable.");
                }

                $user = new User();
                $user
                    ->setCampus($campus)
                    ->setEmail($data[1])
                    ->setRoles(explode(',', $data[2]))
                    ->setPassword($passwordHasher->hashPassword($user, $data[3])) // Hash du mot de passe
                    ->setLastname($data[4])
                    ->setFirstname($data[5])
                    ->setTelephone($data[6])
                    ->setUsername($data[7])
                    ->setIsVerified(true);

                // Validation de l'utilisateur
                $errors = $validator->validate($user);

                if (count($errors) > 0) {
                    // Si des erreurs de validation existent, on les ajoute aux messages flash
                    foreach ($errors as $error) {
                        $this->addFlash('error', $error->getMessage());
                    }
                } else {
                    // Si l'utilisateur est valide, persiste l'entité dans la base de données
                    $entityManager->persist($user);
                }
            }

            fclose($handle);
            $entityManager->flush();

            $this->addFlash('success', 'Les utilisateurs ont été importés avec succès.');
        }

        return $this->redirectToRoute('admin');
    }

    #[Route('/banni', name: 'banned_page')]
    public function banned(): Response
    {
        return $this->render('admin/banned.html.twig');
    }

    #[Route('/admin/user/{id}/ban', name: 'admin_ban_user', methods: ['POST'])]
    public function banUser(User $user, EntityManagerInterface $em, EventRepository $eventRepository, Request $request): Response
    {
        // Vérifier si l'utilisateur a le rôle "ROLE_BAN"
        if (in_array('ROLE_BAN', $user->getRoles())) {
            // Si oui, retirer le rôle "ROLE_BAN"
            $user->removeRole('ROLE_BAN');
            $this->addFlash('info', 'Utilisateur débanni avec succès.');
        } else {
            // Sinon, ajouter le rôle "ROLE_BAN"
            $user->addRole('ROLE_BAN');
            $this->addFlash('info', 'Utilisateur banni avec succès.');

            // Récupérer tous les événements où l'utilisateur est l'organisateur
            $createdEvents = $eventRepository->findBy(['organizer' => $user]);

            $statusCancelled = $em->getRepository(Status::class)->findOneBy(['name' => 'Annulé']);
                // Mettre à jour le statut de l'événement à "annulé"
            if ($statusCancelled) {
                foreach ($createdEvents as $event) {
                    $event->setStatus($statusCancelled);
                    }
            }

            // Récupérer tous les événements où l'utilisateur est un participant
            $participatingEvents = $eventRepository->findByParticipatingUser($user);

            foreach ($participatingEvents as $event) {
                // Retirer l'utilisateur de la liste des participants
                $event->removeParticipant($user);
            }
        }

        // Persister les modifications du rôle dans la base de données
        $em->persist($user);
        $em->flush();

        // Redirigez vers la liste des utilisateurs
        return $this->redirectToRoute('admin');
    }

    #[Route('/admin/add/location', name: 'admin_add_location', methods: ['GET', 'POST'])]
    public function addLocation(EntityManagerInterface $em, Request $request): Response
    {
        $location = new Location();
        $locForm = $this->createForm(LocationType::class, $location);

        $locForm->handleRequest($request);
        if ($locForm->isSubmitted() && $locForm->isValid()){
            $em->persist($location);
            $em->flush();
            return $this->redirectToRoute('admin');
        }

        return $this->render('location/update.html.twig', [
            'locForm' => $locForm,
            'title' => 'Ajout d\'une adresse',
        ]);
    }

    #[Route('/admin/details/location/{id}', name: 'admin_details_location', requirements: ['id' => '\d+'])]
    public function details_location(int $id, LocationRepository $locationRepository, EventRepository $eventRepository): Response
    {
        $location = $locationRepository->find($id);
        $events = $eventRepository->findBy(['location' => $location]);

        if (!$location) {
            throw $this->createNotFoundException('Cette adresse n\'existe pas.');
        }

        return $this->render('admin/detailslocation.html.twig', [
            'location' => $location,
            'events' => $events,
        ]);
    }

    #[Route('/admin/delete/location/{id}', name: 'admin_delete_location', methods: ['GET', 'POST'])]
    public function deleteLocation(int $id, LocationRepository $locationRepository, EntityManagerInterface $em, Request $request): Response
    {
        $location = $locationRepository->find($id);

        if (!$location) {
            throw $this->createNotFoundException('Cette adresse n\'existe pas.');
        }

        $em->remove($location);
        $em->flush();
        $this->addFlash('error', 'Adresse supprimée avec succès.');

        return $this->redirectToRoute('admin');

    }

    #[Route('/admin/edit/location/{id}', name: 'admin_edit_location', methods: ['GET', 'POST'])]
    public function editLocation(int $id, LocationRepository $locationRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $location = $locationRepository->find($id);

        if (!$location) {
            throw $this->createNotFoundException('Cette adresse n\'existe pas.');
        }

        // Création du formulaire
        $locForm = $this->createForm(LocationType::class, $location);
        $locForm->handleRequest($request);

        // Traitement du formulaire
        if ($locForm->isSubmitted() && $locForm->isValid()) {
            $entityManager->flush();
            $this->addFlash('warning', 'Adresse modifiée avec succès.');

            return $this->redirectToRoute('admin');
        }

        // Affichage du formulaire dans la vue
        return $this->render('location/update.html.twig', [
            'locForm' => $locForm,
            'location' => $location,
            'title' => 'Modification d\'une adresse',
        ]);
    }


}
