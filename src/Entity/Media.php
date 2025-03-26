<?php

namespace App\Entity;

use App\Interfaces\IJsonArray;
use App\Repository\MediaRepository;
use App\Service\Common\CollectionService;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\Table(name: "media")]
#[ORM\Index(name: "filter_idx", fields: ["type", "relatedId", "relatedEntity"])]
class Media extends Base implements IJsonArray
{
    const TYPE_PRODUCT = 'PRODUCT';

    #[ORM\Id]
    #[ORM\Column(type: "string", length: 36, unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: "doctrine.uuid_generator")]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column(length: 100)]
    private ?string $relatedId = null;

    #[ORM\Column(length: 150)]
    private ?string $relatedEntity = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getRelatedId(): ?string
    {
        return $this->relatedId;
    }

    public function setRelatedId(string $relatedId): self
    {
        $this->relatedId = $relatedId;

        return $this;
    }

    public function getRelatedEntity(): ?string
    {
        return $this->relatedEntity;
    }

    public function setRelatedEntity(string $relatedEntity): self
    {
        $this->relatedEntity = $relatedEntity;

        return $this;
    }

    public function toArray(CollectionService $entity, $mode): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'path' => $this->getPath(),
            'relatedId' => $this->getRelatedId(),
            'relatedEntity' => $this->getRelatedEntity()
        ];
    }

    public function fromArray(CollectionService $entity, array $data)
    {
        // TODO: Implement fromArray() method.
    }
}
