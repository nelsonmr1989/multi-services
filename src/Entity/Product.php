<?php

namespace App\Entity;

use App\Enum\NormalizeMode;
use App\Interfaces\IJsonArray;
use App\Repository\ProductRepository;
use App\Service\Common\CollectionService;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product extends Base implements IJsonArray
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 36, unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: "doctrine.uuid_generator")]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $category = null;

    #[ORM\Column]
    private ?bool $enabled = null;

    #[ORM\Column]
    private ?float $quantityInStock = null;

    #[ORM\Column]
    private ?float $minimumInStock = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function fromArray(CollectionService $entity, array $data)
    {
        $this->setName($data['name']);
        $this->setPrice($data['price']);
        $this->setDescription($data['description']);

        $enabled = isset($data['enabled']) ? $data['enabled'] : true;

        $this->setEnabled($enabled);
        $this->setQuantityInStock($data['quantityInStock']);
        $this->setMinimumInStock($data['minimumInStock']);

        if (isset($data['category']))  {
            $category = $entity->getEntityManager()->getReference(Category::class, $data['category']);
            $this->setCategory($category);
        }
    }

    public function toArray(CollectionService $entity, $mode)
    {
        $r =  [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'price' => $this->getPrice(),
            'description' => $this->getDescription(),
            'enabled' => $this->isEnabled(),
            'quantityInStock' => $this->getQuantityInStock(),
            'minimumInStock' => $this->getMinimumInStock(),
            'category' => $this->getCategory() instanceof Category ? $this->getCategory()->toArray($entity, $mode) : null,
        ];

        if ($mode == NormalizeMode::MEDIUM) {
            $r['images'] = $entity->getMediaService()->getImages($this->getId());
        }
        return $r;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getQuantityInStock(): ?float
    {
        return $this->quantityInStock;
    }

    public function setQuantityInStock(float $quantityInStock): static
    {
        $this->quantityInStock = $quantityInStock;

        return $this;
    }

    public function getMinimumInStock(): ?float
    {
        return $this->minimumInStock;
    }

    public function setMinimumInStock(float $minimumInStock): static
    {
        $this->minimumInStock = $minimumInStock;

        return $this;
    }
}
