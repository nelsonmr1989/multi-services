<?php

namespace App\Entity;

use App\Interfaces\IGuard;
use App\Interfaces\IJsonArray;
use App\Repository\RecipientRepository;
use App\Service\Common\CollectionService;
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

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

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

    #[ORM\Column(length: 150)]
    private ?string $state = null;

    #[ORM\Column(length: 150)]
    private ?string $city = null;

    public function getId(): ?string
    {
        return $this->id;
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

    /**
     * @return string|null
     */
    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    /**
     * @param string|null $fullName
     */
    public function setFullName(?string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function toArray(CollectionService $entity, $mode)
    {
        return [
            'id' => $this->getId(),
            'fullName' => $this->getFullName(),
            'state' => $this->getState(),
            'city' => $this->getCity(),
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
        $this->setFullName($data['fullName']);
        $this->setState($data['state']);
        $this->setCity($data['city']);
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

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }
}
