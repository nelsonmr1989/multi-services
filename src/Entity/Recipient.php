<?php

namespace App\Entity;

use App\Interfaces\IGuard;
use App\Interfaces\IJsonArray;
use App\Repository\RecipientRepository;
use App\Service\CollectionService;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipientRepository::class)]
class Recipient extends Base implements IJsonArray, IGuard
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 36, unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: "doctrine.uuid_generator")]
    private ?string $id = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(length: 150)]
    private ?string $firstName = null;

    #[ORM\Column(length: 150)]
    private ?string $lastName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $idCard = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $alternatePhone = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $address = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'recipients')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getIdCard(): ?string
    {
        return $this->idCard;
    }

    public function setIdCard(?string $idCard): static
    {
        $this->idCard = $idCard;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAlternatePhone(): ?string
    {
        return $this->alternatePhone;
    }

    public function setAlternatePhone(?string $alternatePhone): static
    {
        $this->alternatePhone = $alternatePhone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function toArray(CollectionService $entity, $mode)
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'ci' => $this->getIdCard(),
            'email' => $this->getEmail(),
            'phoneNumber' => $this->getPhoneNumber(),
            'alternatePhone' => $this->getAlternatePhone(),
            'address' => $this->getAddress(),
            'notes' => $this->getNotes()
        ];
    }

    public function fromArray(CollectionService $entity, array $data)
    {
        $this->setName($data['name']);
        $this->setFirstName($data['firstName']);
        $this->setLastName($data['lastName']);
        $this->setIdCard($data['ci']);
        $this->setEmail($data['email']);
        $this->setPhoneNumber($data['phoneNumber']);
        $this->setAlternatePhone($data['alternatePhone']);
        $this->setAddress($data['address']);
        $this->setNotes($data['notes']);
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isOwner(User $user): bool
    {
        return $this->getUser()->isOwner($user);
    }
}
