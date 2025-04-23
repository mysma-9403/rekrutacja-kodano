<?php
declare(strict_types = 1);

namespace App\Domain\Category;

use ApiPlatform\Metadata\Patch;
use App\Domain\Product\Product;
use App\Infrastructure\Persistence\Doctrine\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Common\Timestampable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use Symfony\Component\Validator\Constraints as SymfonyAssert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: "category")]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], groups: ['category:write'])]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['category:read']]
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['category:read']]
        ),
        new Post(
            normalizationContext: ['groups' => ['category:read']],
            denormalizationContext: ['groups' => ['category:write']],
            validationContext: ['groups' => ['category:write']]
        ),
        new Patch(
            normalizationContext: ['groups' => ['category:read']],
            denormalizationContext: ['groups' => ['category:write']],
            validationContext: ['groups' => ['category:write']]
        ),
        new Delete()
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'code' => 'partial'
])]
#[ApiFilter(DateFilter::class, properties: [
    'createdAt',
    'updatedAt'
])]
#[ApiFilter(OrderFilter::class, properties: [
    'code',
    'createdAt'
], arguments: [
    'orderParameterName' => 'orderBy'
])]
class Category
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[Groups(['category:read','product:read'])]
    private UuidInterface $id;

    #[ORM\Column(type: "string", length: 10, unique: true)]
    #[Groups(['category:read','category:write','product:write','product:read'])]
    #[SymfonyAssert\NotBlank(groups: ['category:write'])]
    #[SymfonyAssert\Length(
        max: 10,
        maxMessage: 'Code must be at most {{ limit }} characters long.',
        groups: ['category:write']
    )]
    private string $code;

    /**
     * @var Collection<int,Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'categories')]
    private Collection $products;
    public function __construct(string $code)
    {
        $this->id = Uuid::uuid4();
        $this->code = $code;
        $this->products = new ArrayCollection();
    }


    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->initializeTimestamps();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->touchUpdatedAt();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function addProduct(Product $p): self
    {
        if (!$this->products->contains($p)) {
            $this->products->add($p);
            // Nie musisz tu dodawać $p->addCategory($this) – to już robisz w Product::addCategory()
        }
        return $this;
    }

    public function removeProduct(Product $p): self
    {
        if ($this->products->contains($p)) {
            $this->products->removeElement($p);
        }
        return $this;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }
}
