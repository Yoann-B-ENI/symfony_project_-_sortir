<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\ImageManagement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Route('/user', name: 'user_')]
final class UserController extends AbstractController
{
    //Affichage profil utilisateur
    #[Route('/', name: 'profile', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/details', name: 'details', requirements: ['id' => '\d+'])]
    public function details(User $user): Response
    {
        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }



    //modification profil utilisateur
    #[Route('/{id}/update', name: 'update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function update(User $user,  Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em,
    #[Autowire('%user_photo_dir%')] string $photoDir,
        #[Autowire('%user_photo_def_filename%')] string $filename,
        ImageManagement $imageManagement,
    ): Response
    {
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);




        if ($userForm->isSubmitted()) {
            $currentPassword = $userForm->get('currentPassword')->getData();
            $newPassword = $userForm->get('newPassword')->getData();
            if ($newPassword) {
                if (!$currentPassword) {
                    $this->addFlash('error', "Veuillez entrer votre mot de passe actuel pour le mettre à jour");
                    return $this->redirectToRoute('user_update', ['id' => $user->getId()]);
                } elseif (!$userPasswordHasher->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('error', "Le mot de passe actuel est incorrect.");
                    return $this->redirectToRoute('user_update', ['id' => $user->getId()]);
                } else {
                    $hashedPassword = $userPasswordHasher->hashPassword($user, $newPassword);
                    $user->setPassword($hashedPassword);
                    $this->addFlash('succes', 'Mot de passe mis à jour avec succès');
                }
            }

            $imageFile = $userForm->get('img')->getData();
            if ($imageFile) {
                $newImagePath = $imageManagement->updateImage(
                    $user->getImg(),  // L'ancienne image
                    $imageFile,        // La nouvelle image
                    $photoDir,         // Le répertoire de base
                    $user->getId(),   // L'ID de l'événement
                    $filename          // Nom de base du fichier
                );

                $user->setImg($newImagePath);
            }
            $em->persist($user);
            $em->flush();
            $this->addFlash('succes', 'Le profil a bien été modifié');
            return $this->redirectToRoute('user_details', ['id' => $user->getId()]);
        }
        return $this->render('user/update.html.twig', [
            'userForm' => $userForm,
            'user' => $user,
        ]);
    }

    //supprimer le profil (par utilisateur)
    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'])]
    public function delete(User $user, Request $request, EntityManagerInterface $em,
                           #[Autowire('%user_photo_dir%')] string $photoDir,
                           ImageManagement $imageManagement,
    )
: Response
    {
            $imageManagement->deleteImage($photoDir, $user->getId());
            $em->remove($user);
            $em->flush();

            return $this->redirectToRoute('app_login', ['id' => $user->getId()]);
    }
}
