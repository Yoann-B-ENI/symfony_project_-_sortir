<?php

namespace App\EventListener;

use App\Entity\Event;
use App\Service\NotifMessageManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEntityListener(event: 'prePersist', entity: Event::class)]
class NotifEventListener
{
    private NotifMessageManager $notifManager;
    public function __construct(NotifMessageManager $notifManager)
    {
        $this->notifManager = $notifManager;
    }


    //#[AsEventListener(event: 'Event.PrePersist')]
    public function prePersist(Event $event): void
    {
        dd($event);
        $this->notifManager->createMessage("L'évènement " . $event->getTitle() . " a été créé et assigné à " . $event->getOrganizer()->getUserIdentifier() . ".",
            false, ['ROLE_ADMIN'], null);
        $this->notifManager->createMessage("L'évènement " . $event->getTitle() . " a été créé et vous a été assigné.",
            false, ['ROLE_USER'], $event->getOrganizer());
    }
}
