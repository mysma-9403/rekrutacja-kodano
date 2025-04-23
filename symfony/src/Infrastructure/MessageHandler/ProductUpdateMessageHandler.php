<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Application\Message\ProductUpdatedMessage;
use App\Domain\Category\Category;
use App\Domain\Product\Product;
use App\Domain\Product\ProductRepositoryInterface;
use App\Infrastructure\Notification\NotificationDTO;
use App\Infrastructure\Notification\NotifierInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ProductUpdateMessageHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepo,
        private IriConverterInterface      $iriConverter,
        private iterable                   $notifiers,
        private LoggerInterface            $logger
    ) {}

    public function __invoke(ProductUpdatedMessage $msg): void
    {
        try {
            /** @var Product $product */
            $product = $this->productRepo->findOneBy(['id' => $msg->id]);
        } catch (\Exception $e) {
            $this->logger->error('Nie znalazłem produktu do aktualizacji', ['iri' => $msg->id]);
            return;
        }

        $product->setName($msg->name);
        $product->setPrice($msg->price);

        $existingIris = [];
        foreach ($product->getCategories() as $cat) {
            $existingIris[] = (string) $cat->getId();
        }

        $newIris = $msg->categories;

        $toRemove = array_diff($existingIris, $newIris);
        $toAdd    = array_diff($newIris, $existingIris);

        foreach ($toRemove as $iri) {
            try {
                /** @var Category $cat */
                $cat = $this->iriConverter->getResourceFromIri($iri);
                $product->removeCategory($cat);
            } catch (\Exception $e) {
                $this->logger->warning('Nie udało się usunąć Category z IRI', ['iri' => $iri]);
            }
        }

        foreach ($toAdd as $iri) {
            try {
                /** @var Category $cat */
                $cat = $this->iriConverter->getResourceFromIri($iri);
                $product->addCategory($cat);
            } catch (\Exception $e) {
                $this->logger->warning('Nie udało się dodać Category z IRI', ['iri' => $iri]);
            }
        }

        $this->productRepo->save($product);

        $messageBody = new NotificationDTO(title: 'Zaktualizowano produkt');
        foreach ($this->notifiers as $notifier) {
            $notifier->notifyProductCreated($product, $messageBody);
        }

        $this->logger->info('Zaktualizowano produkt i wysłano powiadomienia', [
            'id' => (string) $product->getId(),
        ]);
    }
}
