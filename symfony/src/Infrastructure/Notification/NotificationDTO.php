<?php
declare(strict_types=1);

namespace App\Infrastructure\Notification;

class NotificationDTO
{
    public function __construct(
        public string $title
    ) {}
}