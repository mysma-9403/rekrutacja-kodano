<?php
declare(strict_types=1);

namespace App\Infrastructure\Notification;
use App\Domain\Product\Product;

interface NotifierInterface
{
    public function notifyProductCreated(Product $product, NotificationDTO $dto): void;
}