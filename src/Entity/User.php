<?php

namespace App\Entity;

use App\Enum\NormalizeMode;
use App\Interfaces\IGuard;
use App\Interfaces\IJsonArray;
use App\Repository\UserRepository;
use App\Service\CollectionService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user_app`')]
#[DoctrineAssert\UniqueEntity("email")]
#[DoctrineAssert\UniqueEntity("username")]
#[DoctrineAssert\UniqueEntity("phoneNumber")]
class User extends Base implements UserInterface, LegacyPasswordAuthenticatedUserInterface, IJsonArray, IGuard
{
    const ROLE_ADMIN = 'ROLE_ADMIN';

    #[ORM\Id]
    #[ORM\Column(type: "string", length: 36, unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: "doctrine.uuid_generator")]
    private ?string $id = null;

    #[ORM\Column(length: 100)]
    private ?string $username = null;

    #[ORM\Column(length: 50)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 150)]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private ?string $role = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column]
    private ?bool $isEnabled = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserCode::class, orphanRemoval: true)]
    private Collection $codes;

    public function __construct()
    {
        $this->codes = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

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

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): static
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getRoles(): array
    {
        return [$this->getRole()];
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @return Collection<int, UserCode>
     */
    public function getCodes(): Collection
    {
        return $this->codes;
    }

    public function addCode(UserCode $code): self
    {
        if (!$this->codes->contains($code)) {
            $this->codes->add($code);
            $code->setUser($this);
        }

        return $this;
    }

    public function removeCode(UserCode $code): self
    {
        if ($this->codes->removeElement($code)) {
            // set the owning side to null (unless already changed)
            if ($code->getUser() === $this) {
                $code->setUser(null);
            }
        }

        return $this;
    }

    public function toArray(CollectionService $entity, $mode)
    {
        $r = [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber
        ];

        if ($mode == NormalizeMode::MEDIUM) {
            $r['id'] = $this->getId();
            $r['role'] = $this->getRole();
        }

        return $r;
    }

    public function fromArray(CollectionService $entity, array $data)
    {
        $this->setFirstName($data['firstName']);
        $this->setLastName($data['lastName']);
        $this->setEmail($data['email']);
        $this->setPhoneNumber($data['phoneNumber']);
    }

    public function isOwner(User $user): bool
    {
        return $user->getRole() === User::ROLE_ADMIN || $this->getId() === $user->getId();
    }
}
