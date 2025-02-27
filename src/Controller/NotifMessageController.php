<?php

namespace App\Controller;

use App\Repository\NotifMessageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NotifMessageController extends AbstractController
{
    #[Route('/notif', name: 'app_notif')]
    public function index(NotifMessageRepository $notifRepo, UserRepository $userRepo): Response
    {
        $msgs = $notifRepo->findBy([]);

        return $this->render('notif_message/index.html.twig', [
            'controller_name' => 'NotifMessageController',
            'messages' => $msgs
        ]);
    }
}
