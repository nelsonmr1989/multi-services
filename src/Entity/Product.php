<?php

namespace App\Entity;

use App\Interfaces\IJsonArray;
use App\Repository\ProductRepository;
use App\Service\Common\CollectionService;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Index(fields: ["originId"], name: "origin_idx")]
class Product extends Base implements IJsonArray
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 36, unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: "doctrine.uuid_generator")]
    private ?string $id = null;

    #[ORM\Column(length: 36, nullable: true)]
    private ?string $originId = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?array $images = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    protected ?\DateTime $originUpdate;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getOriginId(): ?string
    {
        return $this->originId;
    }

    public function setOriginId(?string $originId): static
    {
        $this->originId = $originId;

        return $this;
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

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(?array $images): static
    {
        $this->images = $images;

        return $this;
    }

    public function fromArray(CollectionService $entity, array $data)
    {
        $this->setOriginId($data['originId']);
        $this->setName($data['name']);
        $this->setImages($data['images']);
        $this->setPrice($data['price']);
        $this->setDescription($data['description']);
        $this->setOriginUpdate($data['originUpdate']);

        if (isset($data['originCategory'])) {
            $entity->getEntityManager()->getRepository(Category::class)->findOneByOriginId();
        } else if (isset($data['category']))  {
            $entity->getEntityManager()->getReference(Category::class, $data['category']);
        }
    }

    public function toArray(CollectionService $entity, $mode)
    {
        return [
            'id' => $this->getId(),
            'originId' => $this->getOriginId(),
            'name' => $this->getName(),
            'images' => $this->getImages(),
            'price' => $this->getPrice(),
            'description' => $this->getDescription(),
            'originUpdate' => $this->getOriginUpdate()
        ];
    }

    /**
     * @return \DateTime|null
     */
    public function getOriginUpdate(): ?\DateTime
    {
        return $this->originUpdate;
    }

    /**
     * @param \DateTime|null $originUpdate
     */
    public function setOriginUpdate(?\DateTime $originUpdate): void
    {
        $this->originUpdate = $originUpdate;
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
}
