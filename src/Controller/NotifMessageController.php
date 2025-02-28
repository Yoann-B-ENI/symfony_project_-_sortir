<?php

namespace App\Controller;

use App\Repository\NotifMessageRepository;
use App\Repository\UserRepository;
use App\Service\NotifMessageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NotifMessageController extends AbstractController
{
    #[Route('/notif', name: 'app_notif')]
    public function index(NotifMessageManager $notifManager): Response
    {
        $msgs = $notifManager->getPublicMessagesByRolesOfUser($this->getUser());
        return $this->render('notif_message/index.html.twig', [
            'messages' => $msgs
        ]);
    }
}
