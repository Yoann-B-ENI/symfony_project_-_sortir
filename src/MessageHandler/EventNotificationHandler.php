<?php

namespace App\MessageHandler;

use App\Message\EventNotification;
use App\Message\NotificationType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EventNotificationHandler
{
    public function __construct(
        private EventRepository $eventRepository,
        private UserRepository $userRepository,
        private MailerInterface $mailer,
    ) {
    }

    public function __invoke(EventNotification $message): void
    {
        $event = $this->eventRepository->find($message->getEventId());

        if (!$event) {
            return;
        }

        switch ($message->getType()) {
            case NotificationType::REGISTRATION:
                $this->handleRegistrationNotification($event, $message->getUserId());
                break;
            case NotificationType::REMINDER:
                $this->handleReminderNotification($event, $message->getUserId());
                break;
            case NotificationType::CANCELLATION:
                $this->handleCancellationNotification($event);
                break;
        }
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function handleRegistrationNotification($event, $userId): void
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return;
        }

        $email = (new Email())
            ->from('noreply@sortir.com')
            ->to($user->getEmail())
            ->subject('Confirmation d\'inscription : ' . $event->getTitle())
            ->html($this->renderConfirmationEmail($event, $user));

        $this->mailer->send($email);
    }

    /**
     * @throws \DateMalformedStringException
     * @throws TransportExceptionInterface
     */
    private function handleReminderNotification($event, $userId): void
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            return;
        }

        if ($event->getStatus()->getName() === 'Annulé') {
            return;
        }

        $now = new \DateTimeImmutable();
        $startsAt = $event->getStartsAt();

        $reminderStart = $startsAt->modify('-48 hours');
        $reminderEnd = $startsAt->modify('-47 hours');

        if ($now >= $reminderStart && $now <= $reminderEnd && $startsAt > $now) {

            $email = (new Email())
                ->from('noreply@sortir.com')
                ->to($user->getEmail())
                ->subject('Rappel : ' . $event->getTitle() . ' dans 48h')
                ->html($this->renderReminderEmail($event, $user));

            $this->mailer->send($email);
        }
    }

    private function handleCancellationNotification($event): void
    {
        foreach ($event->getParticipants() as $participant) {
            $email = (new Email())
                ->from('noreply@sortir.com')
                ->to($participant->getEmail())
                ->subject('Annulation de l\'événement : ' . $event->getTitle())
                ->html($this->renderCancellationEmail($event, $participant));

            $this->mailer->send($email);
        }
    }

    private function renderConfirmationEmail($event, $user): string
    {
        return "<h1>Confirmation d'inscription</h1>
                <p>Bonjour,</p>
                <p>Vous êtes bien inscrit à l'événement <strong>{$event->getTitle()}</strong>.</p>
                <p>Date : {$event->getStartsAt()->format('d/m/Y à H:i')}</p>
                <p>Lieu : {$event->getLocation()->getName()}</p>
                <p>À bientôt !</p>";
    }

    private function renderReminderEmail($event, $user): string
    {
        return "<h1>Rappel d'événement</h1>
                <p>Bonjour,</p>
                <p>Nous vous rappelons que l'événement <strong>{$event->getTitle()}</strong> 
                   aura lieu dans 48 heures.</p>
                <p>Date : {$event->getStartsAt()->format('d/m/Y à H:i')}</p>
                <p>Lieu : {$event->getLocation()->getName()}</p>
                <p>À bientôt !</p>";
    }

    private function renderCancellationEmail($event, $user): string
    {
        return "<h1>Annulation d'événement</h1>
                <p>Bonjour,</p>
                <p>Nous sommes désolés de vous informer que l'événement <strong>{$event->getTitle()}</strong> 
                   a été annulé.</p>
                <p>Nous vous prions de nous excuser pour ce désagrément.</p>";
    }
}