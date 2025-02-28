<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\AdminEditEventType;
use App\Form\AdminAddUserType;
use App\Form\AdminEditUserType;
use App\Form\EventFilterType;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;


final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin', methods: ['GET', 'POST'])]
    public function index(UserRepository $userRepository, EventRepository $eventRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $users = $userRepository->findAll();

        // Création du formulaire de filtre
        $form = $this->createForm(EventFilterType::class);
        $form->handleRequest($request);

        $events = $eventRepository->findAll();


        if ($form->isSubmitted() && $form->isValid()) {

            $organizer = $form->get('organizer')->getData();
            if ($organizer instanceof User) {
                $events = $eventRepository->findBy(['organizer' => $organizer->getId()]);

                return $this->render('admin/index.html.twig', [
                    'users' => $users,
                    'events' => $events,
                    'form' => $form,
                ]);
            }

        }
        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'events' => $events,
            'form' => $form,
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
    public function details_event(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Cet évènement n\'existe pas.');
        }

        return $this->render('admin/detailsevent.html.twig', [
            'event' => $event,
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
            if ($organizer && $organizer->getCampus()) {
                $event->setCampus($organizer->getCampus());
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
            'form' => $form->createView(),
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




}
