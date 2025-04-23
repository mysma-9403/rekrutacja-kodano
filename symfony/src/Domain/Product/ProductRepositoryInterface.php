<?php
declare(strict_types = 1);

namespace App\Domain\Product;

use Ramsey\Uuid\UuidInterface;

interface ProductRepositoryInterface
{
    public function save(Product $product): void;
    public function remove(Product $product): void;
    public function findById(UuidInterface $id): ?Product;

    public function existsByName(string $name): bool;

    public function findOneByName(string $name): ?Product;

    public function findOneBy(array $criteria, array|null $orderBy = null): ?Product;
}