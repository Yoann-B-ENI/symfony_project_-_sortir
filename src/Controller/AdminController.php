<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\AdminEditEventType;
use App\Form\AdminAddUserType;
use App\Form\AdminEditUserType;
use App\Form\EventFilterType;
use App\Form\EventType;
use App\Repository\CampusRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin', methods: ['GET', 'POST'])]
    public function index(UserRepository $userRepository, EventRepository $eventRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer la valeur de la recherche
        $searchTerm = $request->query->get('searchTerm');

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

        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'events' => $events,
            'searchTerm' => $searchTerm,
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
    public function delete(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas.');
        }

        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', 'Utilisateur supprimé avec succès.');

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
            $this->addFlash('success', 'Utilisateur modifié avec succès.');

            return $this->redirectToRoute('admin');
        }

        // Affichage du formulaire dans la vue
        return $this->render('admin/edituser.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }

    #[Route('/admin/add/user', name: 'admin_add_user')]
    public function addUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
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
                // Gérer le cas où le mot de passe est null (par exemple, ajouter un message d'erreur)
                $this->addFlash('error', 'Le mot de passe est requis.');
                return $this->render('admin/adduser.html.twig', [
                    'form' => $form,
                ]);
            }

            // Persister l'utilisateur
            $entityManager->persist($user);
            $entityManager->flush();

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

    #[Route('/admin/details/event/{id}', name: 'admin_details_event', requirements: ['id' => '\d+'])]
    public function details_event(int $id, EventRepository $eventRepository, UserRepository $userRepository, Request $request): Response
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Cet évènement n\'existe pas.');
        }

        // Récupérer la valeur de la recherche du formulaire GET pour les utilisateurs à ajouter
        $searchUsername = $request->query->get('searchUsername');

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

        // Récupérer la valeur de la recherche du formulaire GET pour les participants
        $searchParticipant = $request->query->get('searchParticipant');

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

        // Vérification de la limite de participants
        $currentParticipantsCount = count($event->getParticipants());
        if ($currentParticipantsCount >= $event->getNbMaxParticipants()) {
            // Ajout d'un message pour informer l'admin que la limite est atteinte
            $this->addFlash('error', 'Le nombre maximum de participants a été atteint.');
        }

        return $this->render('admin/detailsevent.html.twig', [
            'event' => $event,
            'users' => $nonParticipants, // Passer les utilisateurs non participants
            'participants' => $participants, // Passer les participants filtrés
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
        $this->addFlash('success', 'Evènement supprimé avec succès.');

        return $this->redirectToRoute('admin');

    }


    #[Route('/admin/edit/event/{id}', name: 'admin_edit_event', methods: ['GET', 'POST'])]
    public function editevent(int $id, EventRepository $eventRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $event = $eventRepository->find($id);
        $users = $userRepository->findAll();

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
            $this->addFlash('success', 'Evènement modifié avec succès.');

            // Rediriger vers la page admin
            return $this->redirectToRoute('admin');
        }

        // Affichage du formulaire dans la vue
        return $this->render('admin/editevent.html.twig', [
            'form' => $form,
            'event' => $event,
            'users' => $users,
            'selectedOrganizer' => $selectedOrganizer,
            'campus' => $campus, // Passe le campus à la vue pour qu'il soit affiché
        ]);
    }


    #[Route('/admin/add/event', name: 'admin_add_event', methods: ['GET', 'POST'])]
    public function addevent(EventRepository $eventRepository, EntityManagerInterface $entityManager, Request $request, UserRepository $userRepository): Response
    {
        $event = new Event();

        // Créer le formulaire
        $form = $this->createForm(AdminEditEventType::class, $event);
        $form->handleRequest($request);

        // Récupérer tous les utilisateurs pour le menu déroulant
        $users = $userRepository->findAll();  // Ou tu peux filtrer selon les rôles ou critères

        // Vérifie si un organisateur est déjà sélectionné
        $selectedOrganizer = $event->getOrganizer();

        // Si un organisateur est sélectionné, récupérer son campus
        $campus = null;
        if ($selectedOrganizer) {
            $campus = $selectedOrganizer->getCampus();
        }

        // Traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Si un organisateur est sélectionné et a un campus, assigner le campus à l'événement
            $organizer = $event->getOrganizer();
            if ($organizer && $organizer->getCampus()) {
                $event->setCampus($organizer->getCampus());
            }

            // Persister l'événement
            $entityManager->persist($event);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Evènement ajouté avec succès.');

            // Rediriger vers la page admin
            return $this->redirectToRoute('admin');
        }

        // Affichage du formulaire dans la vue
        return $this->render('admin/addevent.html.twig', [
            'form' => $form->createView(),
            'campus' => $campus,  // Passe le campus à la vue pour un premier affichage
            'users' => $users,    // Passe la liste des utilisateurs à la vue
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


        if (!$event || !$user) {
            $this->addFlash('error', 'Événement ou utilisateur introuvable.');
            return $this->redirectToRoute('admin_details_event', ['id' => $eventId]);
        }

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
            $this->addFlash('success', 'Participant retiré avec succès.');
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
    public function importUsers(Request $request, CampusRepository $campusRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): Response
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
                    ->setUsername($data[7]);

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

}
