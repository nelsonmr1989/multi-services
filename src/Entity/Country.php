<?php

namespace App\Entity;

use App\Interfaces\IJsonArray;
use App\Repository\CountryRepository;
use App\Service\CollectionService;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country extends Base implements IJsonArray
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 2)]
    private ?string $code = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

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

    public function toArray(CollectionService $entity, $mode)
    {
        return [
            "code" => $this->getCode(),
            "name" => $this->getName()
        ];
    }

    public function fromArray(CollectionService $entity, array $data)
    {
        // TODO: Implement fromArray() method.
    }
}
