<?php

declare(strict_types=1);

namespace App\Application\Message;

use App\Infrastructure\Validation\Constraint\UniqueProductNameOnUpdate;
use Symfony\Component\Serializer\Annotation\Groups;;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueProductNameOnUpdate(groups: ['product:write'])]
final class ProductUpdatedMessage
{
    #[Groups(['product:write'])]
    public string $id;

    #[Groups(['product:read','product:write'])]
    #[Assert\NotBlank(groups: ['product:write'])]
    #[Assert\Length(min: 1, max: 255, groups: ['product:write'])]
    public string $name;

    #[Groups(['product:read','product:write'])]
    #[Assert\GreaterThanOrEqual(value: 0, groups: ['product:write'])]
    #[Assert\Type(
        type: 'numeric',
        groups: ['product:write']
    )]
    public float $price;

    /**
     * @var string[]
     */
    #[Groups(['product:write'])]
    #[Assert\Count(min: 1, minMessage: 'Przynajmniej jedna kategoria jest wymagana.')]
    public array $categories = [];
}