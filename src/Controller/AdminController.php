<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminAddUserType;
use App\Form\AdminEditUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;


final class AdminController extends AbstractController{
    #[Route('/admin', name: 'admin')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/details/{id}', name: 'admin_details_user', requirements: ['id' => '\d+'])]
    public function details_user(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas.');
        }

        return $this->render('admin/detailsuser.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/delete/{id}', name: 'admin_delete_user', methods: ['POST'])]
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

    #[Route('/admin/edit/{id}', name: 'admin_edit_user', methods: ['GET','POST'])]
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

    #[Route('/admin/add', name: 'admin_add_user')]
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

}
