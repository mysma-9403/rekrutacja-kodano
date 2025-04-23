<?php

declare(strict_types = 1);

namespace App\Infrastructure\MessageHandler;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Application\Message\ProductCreatedMessage;
use App\Domain\Category\Category;
use App\Domain\Product\Product;
use App\Domain\Product\ProductRepositoryInterface;
use App\Infrastructure\Notification\NotificationDTO;
use App\Infrastructure\Notification\NotifierInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ProductCreatedMessageHandler
{
    /** @param NotifierInterface[] $notifiers */
    public function __construct(
        private ProductRepositoryInterface $productRepo,
        private IriConverterInterface      $iriConverter,
        private iterable                   $notifiers,
        private LoggerInterface            $logger
    ) {}

    public function __invoke(ProductCreatedMessage $msg): void
    {
        $product = new Product($msg->name, $msg->price);
        foreach ($msg->categories as $cat) {
            try {
                $this->logger->info('dasdasd', [$cat]);
                /** @var Category $category */
                $category = $this->iriConverter->getResourceFromIri($cat);
                $product->addCategory($category);
            } catch (\Exception $e) {
                $this->logger->error('Nie udało się wczytać Category z IRI', ['iri' => $cat]);
                continue;
            }
        }
        $this->productRepo->save($product);

        $messageBody = new NotificationDTO(
            title: 'Nowy produkt'
        );
        foreach ($this->notifiers as $notifier) {
            $notifier->notifyProductCreated($product, $messageBody);
        }

        $this->logger->info('Wysłano powiadomienia dla produktu', [
            'id' => (string) $product->getId(),
        ]);
    }
}