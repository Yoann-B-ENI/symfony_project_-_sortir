<?php

namespace App\Service;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;

class EventStatusService{

    private $statusRepository;
    private $entityManager;

    private $statusEnCours;
    private $statusPasse;
    private $statusAVenir;
    private $statusArchive;



    public function __construct(StatusRepository $statusRepository,
                                EntityManagerInterface $entityManager){
        $this->statusRepository = $statusRepository;
        $this->entityManager = $entityManager;

        $this->statusEnCours = $statusRepository->findOneBy(['name' => 'En cours']);
        $this->statusPasse = $statusRepository->findOneBy(['name' => 'Passé']);
        $this->statusAVenir = $statusRepository->findOneBy(['name' => 'Prévu']);
        $this->statusArchive = $statusRepository->findOneBy(['name' => 'Archivé']);
    }

    public function checkAndUpdates(Event $event): bool
    {
        $now = new \DateTime();
        $oneMonthAgo = (clone $now)->modify('-1 month');
        $changed = false;

        if (!$this->statusEnCours || !$this->statusPasse || !$this->statusAVenir || !$this->statusArchive) {
            return false;
        }

        if ($event->getStatus()->getName() === 'Brouillon') {
            return false;
        }
        if ($event->getStatus()->getName() === 'Annulé') {
            return false;
        }
        if ($event->getStatus()->getName() === 'Archivé') {
            return false;
        }


        // Vérifier si l'événement devrait être en cours
        if ($event->getStartsAt() <= $now && $event->getEndsAt() > $now
            && $event->getStatus()->getName() !== 'En cours') {
            $event->setStatus($this->statusEnCours);
            $changed = true;
        }
        // Vérifier si l'événement devrait être archivé
        elseif ($event->getEndsAt() < $now && $event->getEndsAt() > $oneMonthAgo&& $event->getStatus()->getName() !== 'Passé') {
            $event->setStatus($this->statusPasse);
            $changed = true;
        }

        elseif ($event->getEndsAt() < $oneMonthAgo && $event->getStatus()->getName() !== 'Archivé') {
            $event->setStatus($this->statusArchive);
            $changed = true;
        }

        elseif($event->getStartsAt() > $now && $event->getStatus()->getName() !== 'Prévu') {
            $event->setStatus($this->statusAVenir);
            $changed = true;
        }


        if ($changed) {
            $this->entityManager->persist($event);
            $this->entityManager->flush();
        }

        return $changed;
    }

    }