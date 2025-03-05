<?php

namespace App\EventListener;

use App\Entity\Event;
use App\Entity\User;
use App\Service\NotifMessageManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEntityListener(event: Events::prePersist, method: 'eventCreated', entity: Event::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'eventUpdated', entity: Event::class)]
#[AsEntityListener(event: Events::preRemove, method: 'eventRemoved', entity: Event::class)]
#[AsEntityListener(event: Events::postPersist, method: 'userCreated', entity: User::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'userUpdated', entity: User::class)]
#[AsEntityListener(event: Events::preRemove, method: 'userRemoved', entity: User::class)]
class NotifEventListener
{
    private NotifMessageManager $notifManager;
    public function __construct(NotifMessageManager $notifManager)
    {
        $this->notifManager = $notifManager;
    }


    // can be pre persist, user always already exists in DB
    public function eventCreated(Event $event): void
    {
        // Yes
        $this->notifManager->createMessage("L'évènement " . $event->getTitle() . " a été créé et assigné à " . $event->getOrganizer()->getUserIdentifier() . ".",
            false, ['ROLE_ADMIN'], null);
        // Yes
        $this->notifManager->createMessage("L'évènement " . $event->getTitle() . " a été créé et vous a été assigné.",
            false, ['ROLE_USER'], $event->getOrganizer());
        // No
    }


    // can be pre update, user and participants always already exist in DB?
    public function eventUpdated(Event $event, PreUpdateEventArgs $eventArgs): void
    {
        $body = "";
        if ($eventArgs->hasChangedField('title')){$body = $body . "titre, ";}
        if ($eventArgs->hasChangedField('startsAt')){$body = $body . "date de début, ";}
        if ($eventArgs->hasChangedField('endsAt')){$body = $body . "date de fin, ";}
        if ($eventArgs->hasChangedField('openUntil')){$body = $body . "date max d'inscription, ";}
        if ($eventArgs->hasChangedField('nbMaxParticipants')){$body = $body . "nombre max participants, ";}
        if ($eventArgs->hasChangedField('description')){$body = $body . "description, ";}
        if ($eventArgs->hasChangedField('img')){$body = $body . "image, ";}
        //if ($eventArgs->hasChangedField('status')){$body = $body . "statut, ";}

        $this->notifManager->createMessage("L'évènement " . $event->getTitle() . " a été modifié. " . $body,
            false, ['ROLE_ADMIN'], null);
        $this->notifManager->createMessage("L'évènement " . $event->getTitle() . " que vous organisez a été modifié. " . $body,
            false, ['ROLE_USER'], $event->getOrganizer());
        foreach ($event->getParticipants() as $p) {
            $this->notifManager->createMessage("L'évènement " . $event->getTitle() . " auquel vous participez a été modifié. " . $body,
                true, ['ROLE_USER'], $p);
        }
    }

    // only pre remove? we need the links
    public function eventRemoved(Event $event): void
    {
        $this->notifManager->createMessage("L'évènement " . $event->getTitle() . " a été supprimé.",
            false, ['ROLE_ADMIN'], null);
        $this->notifManager->createMessage("L'évènement " . $event->getTitle() . " que vous organisiez a été supprimé.",
            false, ['ROLE_USER'], $event->getOrganizer());
        foreach ($event->getParticipants() as $p) {
            $this->notifManager->createMessage("L'évènement " . $event->getTitle() . " auquel vous participiez a été supprimé.",
                true, ['ROLE_USER'], $p);
        }
    }





    // only post persist, we need user in DB
    public function userCreated(User $user): void
    {
        $this->notifManager->createMessage("L'utilisateur " . $user->getUserIdentifier() . " a été créé.",
            false, ['ROLE_ADMIN'], null);
        $this->notifManager->createMessage("Votre compte " . $user->getUserIdentifier() . " a été créé.",
            false, ['ROLE_USER'], $user);
    }

    // only post update
    public function userUpdated(User $user, PostUpdateEventArgs $eventArgs): void
    {
        // this ends up sending an admin notif every time a user receives a message
//        $this->notifManager->createMessage("L'utilisateur " . $user->getUserIdentifier() . " a été modifié.",
//            false, ['ROLE_ADMIN'], null);
        // INFINITE CALL ? sending a msg to a user is a db user update call
        // DO NOT SEND A MESSAGE TO A TARGET USER IN THE UPDATE LISTENER
//        $this->notifManager->createMessage("Votre compte " . $user->getUserIdentifier() . " a été modifié.",
//            false, ['ROLE_USER'], $user);
    }

    // can be pre or post? only symfony info
    public function userRemoved(User $user): void
    {
        $this->notifManager->createMessage("L'utilisateur " . $user->getUserIdentifier() . " a été supprimé.",
            false, ['ROLE_ADMIN'], null);
        // annoying and also makes no sense
//        $this->notifManager->createMessage("Votre compte " . $user->getUserIdentifier() . " a été supprimé.",
//            false, ['ROLE_USER'], $user);
    }




}
