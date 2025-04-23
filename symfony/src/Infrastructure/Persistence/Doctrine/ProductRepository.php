<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Product\Product;
use App\Domain\Product\ProductRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class ProductRepository extends ServiceEntityRepository implements ProductRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $product): void
    {
        $em = $this->getEntityManager();
        $em->persist($product);
        $em->flush();
    }

    public function remove(Product $product): void
    {
        $em = $this->getEntityManager();
        $em->remove($product);
        $em->flush();
    }

    public function findById(UuidInterface $id): ?Product
    {
        return parent::find($id);
    }

    public function existsByName(string $name): bool
    {
        $em = $this->getEntityManager();
        return (bool) $em->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->select('1')
            ->where('p.name = :name')
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByName(string $name): ?Product
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findOneBy(array $criteria, array|null $orderBy = null): ?Product
    {
        return parent::findOneBy($criteria, $orderBy);
    }
}
