<?php
declare(strict_types=1);

namespace App\Domain\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Put;
use App\Application\Message\ProductCreatedMessage;
use App\Application\Message\ProductUpdatedMessage;
use App\Infrastructure\Persistence\Doctrine\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Domain\Common\Timestampable;
use App\Domain\Category\Category;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: "product")]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['product:read']]),
        new GetCollection(normalizationContext: ['groups' => ['product:read']]),
        new Post(
            inputFormats: [
                'json'    => ['application/json'],
                'jsonld'  => ['application/ld+json'],
            ],
            outputFormats: [
                'json'   => ['application/json'],
                'jsonld' => ['application/ld+json'],
            ],
            normalizationContext: ['groups' => ['product:read']],
            denormalizationContext: ['groups' => ['product:write']],
            validationContext: ['groups' => ['product:write']],
            input: ProductCreatedMessage::class,
            messenger: 'input',
        ),
        new Put(
            uriTemplate: '/products',
            inputFormats: [
                'json'   => ['application/json'],
                'jsonld' => ['application/ld+json'],
            ],
            outputFormats: [
                'json'   => ['application/json'],
                'jsonld' => ['application/ld+json'],
            ],
            normalizationContext: ['groups' => ['product:read']],
            denormalizationContext: ['groups' => ['product:write']],
            validationContext: ['groups' => ['product:write']],
            input: ProductUpdatedMessage::class,
            messenger: 'input',
        ),
        new Delete()
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'name'             => 'partial',
    'categories.code'  => 'exact'
])]
#[ApiFilter(DateFilter::class, properties: [
    'createdAt',
    'updatedAt'
])]
#[ApiFilter(OrderFilter::class, properties: [
    'price',
    'createdAt'
], arguments: [
    'orderParameterName' => 'orderBy'
])]
class Product
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[Groups(['product:read','category:read'])]
    private UuidInterface $id;

    #[ORM\Column(type: "string")]
    #[Groups(['product:read','product:write'])]
    private string $name;

    #[ORM\Column(type: "float")]
    #[Groups(['product:read','product:write'])]

    private float $price;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'products')]
    #[ORM\JoinTable(name: 'product_category')]
    private Collection $categories;

    public function __construct(string $name, float $price)
    {
        $this->id         = Uuid::uuid4();
        $this->name       = $name;
        $this->price      = $price;
        $this->categories = new ArrayCollection();
        $this->initializeTimestamps();
    }

    #[ORM\PrePersist]
    protected function onPrePersist(): void
    {
        $this->initializeTimestamps();
    }

    #[ORM\PreUpdate]
    protected function onPreUpdate(): void
    {
        $this->touchUpdatedAt();
    }

    /**
     * @param Category $c
     * @return $this
     */
    public function addCategory(Category $c): self
    {
        if (!$this->categories->contains($c)) {
            $this->categories->add($c);
        }
        return $this;
    }

    public function removeCategory(Category $category): void
    {
        $this->categories = $this->categories->filter(
            fn(Category $c) => ! $c->getId()->equals($category->getId())
        );
        $this->touchUpdatedAt();
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }
}
