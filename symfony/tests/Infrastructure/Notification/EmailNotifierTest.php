<?php
declare(strict_types=1);

namespace App\Tests\Infrastructure\Notification;

use App\Infrastructure\Notification\EmailNotifier;
use App\Infrastructure\Notification\NotificationDTO;
use App\Domain\Product\Product;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailNotifierTest extends TestCase
{
    public function testNotifyProductCreatedSendsEmailWithCorrectData(): void
    {
        $from = 'test@test.pl';
        $to   = 'test@test.pl';
        $title = 'Nowy produkt';

        $product = $this->createMock(Product::class);
        $product->method('getName')->willReturn('Test Product');

        $twig = $this->createMock(Environment::class);
        $twig->expects($this->once())
            ->method('render')
            ->with(
                'emails/product_created.html.twig',
                $this->callback(fn(array $ctx) => isset($ctx['product']) && $ctx['product'] === $product)
            )
            ->willReturn('<p>HTML content</p>');

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method('send')
            ->with(
                $this->callback(function(Email $email) use ($from, $to, $title, $product) {
                    return
                        $email->getSubject() === sprintf('%s: %s', $title, $product->getName())
                        && $email->getFrom()[0]->getAddress() === $from
                        && $email->getTo()[0]->getAddress()   === $to
                        && $email->getHtmlBody()             === '<p>HTML content</p>';
                }),
                $this->anything() // Symfony Mailer zawsze dokleja drugi parametr Envelope
            );

        $notifier = new EmailNotifier($mailer, $twig);
        $dto = new NotificationDTO($title);

        $notifier->notifyProductCreated($product, $dto);
    }
}
