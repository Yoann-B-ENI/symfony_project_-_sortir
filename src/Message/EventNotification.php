<?php

namespace App\Message;

enum NotificationType {
    case REGISTRATION;
    case REMINDER;
    case CANCELLATION;
}
class EventNotification
{
    public function __construct(
        private int              $eventId,
        private readonly ?int    $userId = null,
        private NotificationType $type = NotificationType::REGISTRATION

    ) {
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
    public function getType(): NotificationType
    {
        return $this->type;
    }
}