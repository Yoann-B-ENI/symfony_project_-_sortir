<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user', name: 'user_')]
final class UserController extends AbstractController
{
    //Affichage profil utilisateur
    #[Route('/', name: 'profile', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('user/profile.html.twig', [
            'title' => 'Profile',
        ]);
    }

    //modification profil utilisateur
    #[Route('/{id}/update', name: 'update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function update(User $user, Request $request, EntityManagerInterface $em): Response
    {

        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);
        if ($userForm->isSubmitted()) {
            $em->flush();
            $this->addFlash('succes', 'Le profil a bien été modifié');
            return $this->redirectToRoute('user_profile', ['id' => $user->getId()]);
        }
        return $this->render('user/update.html.twig', [
            'userForm' => $userForm,
            'user' => $user,
        ]);
    }

    //supprimer le profil (par utilisateur)
    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'])]
    public function delete(User $user, Request $request, EntityManagerInterface $em): Response
    {

            $em->remove($user);
            $em->flush();

            return $this->redirectToRoute('app_login', ['id' => $user->getId()]);
    }
}
