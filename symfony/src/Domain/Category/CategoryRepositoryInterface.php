<?php
declare(strict_types = 1);

namespace App\Domain\Category;

use Ramsey\Uuid\UuidInterface;

interface CategoryRepositoryInterface
{
    public function findById(UuidInterface $id): ?Category;
    public function findByCode(string $code): ?Category;
}