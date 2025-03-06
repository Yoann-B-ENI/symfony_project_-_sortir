<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\SecurityBundle\Security;

class BannedUserSubscriber implements EventSubscriberInterface
{
    private Security $security;
    private RouterInterface $router;

    public function __construct(Security $security, RouterInterface $router)
    {
        $this->security = $security;
        $this->router = $router;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Vérifie si l'utilisateur est connecté et a le rôle "ROLE_BAN"
        if ($this->security->getUser() && $this->security->isGranted('ROLE_BAN')) {
            // Vérifie qu'on n'est pas déjà sur la page de bannissement pour éviter une boucle infinie
            if ($request->getPathInfo() !== $this->router->generate('banned_page')) {
                $response = new RedirectResponse($this->router->generate('banned_page'));
                $event->setResponse($response);
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }
}
