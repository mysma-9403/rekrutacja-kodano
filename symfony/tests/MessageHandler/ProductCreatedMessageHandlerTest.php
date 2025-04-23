<?php
declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Application\Message\ProductCreatedMessage;
use App\Domain\Category\Category;
use App\Domain\Product\Product;
use App\Domain\Product\ProductRepositoryInterface;
use App\Infrastructure\MessageHandler\ProductCreatedMessageHandler;
use App\Infrastructure\Notification\NotificationDTO;
use App\Infrastructure\Notification\NotifierInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ProductCreatedMessageHandlerTest extends TestCase
{
    public function testHandleSavesNewProductAndNotifiesAll(): void
    {
        $msg = new ProductCreatedMessage();
        $msg->name = 'My Product';
        $msg->price = 99.99;
        $msg->categories = [];

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Product $p) {
                return
                    $p->getName() === 'My Product'
                    && $p->getPrice() === 99.99
                    && $p->getCategories()->isEmpty();
            }));

        $iriConverter = $this->createMock(IriConverterInterface::class);

        $notifier1 = $this->createMock(NotifierInterface::class);
        $notifier1->expects($this->once())
            ->method('notifyProductCreated')
            ->with(
                $this->isInstanceOf(Product::class),
                $this->callback(fn(NotificationDTO $dto) => $dto->title === 'Nowy produkt')
            );

        $notifier2 = $this->createMock(NotifierInterface::class);
        $notifier2->expects($this->once())
            ->method('notifyProductCreated')
            ->with(
                $this->isInstanceOf(Product::class),
                $this->callback(fn(NotificationDTO $dto) => $dto->title === 'Nowy produkt')
            );

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with(
                'Wysłano powiadomienia dla produktu',
                $this->arrayHasKey('id')
            );

        $handler = new ProductCreatedMessageHandler(
            $repo,
            $iriConverter,
            [$notifier1, $notifier2],
            $logger
        );

        $handler($msg);
    }

    public function testHandleSkipsInvalidIriAndStillSavesAndNotifies(): void
    {
        $msg = new ProductCreatedMessage();
        $msg->name = 'Another Product';
        $msg->price = 5.5;
        $msg->categories = ['bad', 'good'];

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Product::class));

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getResourceFromIri')
            ->willReturnCallback(fn(string $iri) => $iri === 'good'
                ? $this->createMock(Category::class)
                : throw new \Exception('Invalid IRI')
            );


        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeastOnce())
            ->method('error')
            ->with(
                'Nie udało się wczytać Category z IRI',
                $this->arrayHasKey('iri')
            );

        $logger->expects($this->atLeastOnce())
            ->method('info')
            ->with(
                'Wysłano powiadomienia dla produktu',
                $this->arrayHasKey('id')
            );

        $notifier = $this->createMock(NotifierInterface::class);
        $notifier->expects($this->once())
            ->method('notifyProductCreated')
            ->with(
                $this->isInstanceOf(Product::class),
                $this->isInstanceOf(NotificationDTO::class)
            );

        $handler = new ProductCreatedMessageHandler(
            $repo,
            $iriConverter,
            [$notifier],
            $logger
        );

        $handler($msg);
    }
}
