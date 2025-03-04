<?php

namespace App\Service;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;

class EventStatusService{

    private $statusRepository;
    private $entityManager;

    public function __construct(StatusRepository $statusRepository,
                                EntityManagerInterface $entityManager){
        $this->statusRepository = $statusRepository;
        $this->entityManager = $entityManager;
    }

    public function checkAndUpdates(Event $event): bool
    {

        $now = new \DateTime();
        $changed = false;

        $statusEnCours = $this->statusRepository->findOneBy(['name' => 'En cours']);
        $statusArchive = $this->statusRepository->findOneBy(['name' => 'Archivé']);
        $statusAVenir = $this->statusRepository->findOneBy(['name' => 'Prévu']);

        if (!$statusEnCours || !$statusArchive || !$statusAVenir) {
            return false;
        }

        if ($event->getStatus()->getName() === 'Brouillon') {
            return false;
        }
        if ($event->getStatus()->getName() === 'Annulé') {
            return false;
        }


        // Vérifier si l'événement devrait être en cours
        if ($event->getStartsAt() <= $now && $event->getEndsAt() > $now
            && $event->getStatus()->getName() !== 'En cours') {
            $event->setStatus($statusEnCours);
            $changed = true;
        }
        // Vérifier si l'événement devrait être archivé
        elseif ($event->getEndsAt() < $now && $event->getStatus()->getName() !== 'Archivé') {
            $event->setStatus($statusArchive);
            $changed = true;
        }

        elseif($event->getStartsAt() > $now && $event->getStatus()->getName() !== 'Prévu') {
            $event->setStatus($statusAVenir);
            $changed = true;
        }

        if ($changed) {
            $this->entityManager->persist($event);
            $this->entityManager->flush();
        }

        return $changed;
    }

    }