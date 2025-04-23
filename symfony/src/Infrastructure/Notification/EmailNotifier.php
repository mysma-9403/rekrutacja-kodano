<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification;

use App\Domain\Product\Product;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailNotifier implements NotifierInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment     $twig,
    ) {}

    public function notifyProductCreated(Product $product, NotificationDTO $dto): void
    {
        $email = (new Email())
            ->from('test@test.pl') //@TODO move to DTO?
            ->to('test@test.pl') //@TODO move to DTO
            ->subject(sprintf('%s: %s', $dto->title, $product->getName()))
            ->html($this->twig->render('emails/product_created.html.twig', [
                'product' => $product,
            ]));

        $this->mailer->send($email);
    }
}