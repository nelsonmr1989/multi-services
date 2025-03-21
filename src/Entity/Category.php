<?php

namespace App\Entity;

use App\Interfaces\IJsonArray;
use App\Repository\CategoryRepository;
use App\Service\CollectionService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Index(fields: ["originId"], name: "origin_idx")]
class Category extends Base implements IJsonArray
{
    const CATEGORY_OTHER_ID = '';

    #[ORM\Id]
    #[ORM\Column(type: "string", length: 36, unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: "doctrine.uuid_generator")]
    private ?string $id = null;

    #[ORM\Column(length: 180)]
    private ?string $name = null;

    #[ORM\Column(length: 36, nullable: true)]
    private ?string $originId = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    protected ?\DateTime $originUpdate;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Product::class, orphanRemoval: true)]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

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

    public function toArray(CollectionService $entity, $mode)
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'originId' => $this->getOriginId(),
            'originUpdated' => $this->getOriginUpdate()
        ];
    }

    public function fromArray(CollectionService $entity, array $data)
    {
        $this->setName($data['name']);
        $this->setOriginId($data['originId']);
        $this->setOriginUpdate($entity->getDateFromString($data['originUpdated']));
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

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }
}
