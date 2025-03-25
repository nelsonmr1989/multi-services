<?php

namespace App\Entity;

use App\Enum\NormalizeMode;
use App\Interfaces\IJsonArray;
use App\Repository\UserCodeRepository;
use App\Service\Common\CollectionService;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserCodeRepository::class)]
#[ORM\Table(name: "user_code")]
class UserCode extends Base implements IJsonArray
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 36, unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: "doctrine.uuid_generator")]
    private ?string $id = null;

    #[ORM\ManyToOne(inversedBy: 'codes')]
    #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id', nullable: false, onDelete: "CASCADE")]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    private ?string $code = null;

    #[ORM\Column(length: 25)]
    private ?string $reason = null;

    #[ORM\Column(nullable: true)]
    private ?int $try = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getTry(): ?int
    {
        return $this->try;
    }

    public function setTry(?int $try): self
    {
        $this->try = $try;

        return $this;
    }

    public function toArray(CollectionService $entity, $mode)
    {
        $r = [
            'id' => $this->getId(),
            'reason' => $this->getReason(),
            'code' => '*****',
        ];

        if ($mode == NormalizeMode::MEDIUM || $mode == NormalizeMode::FULL)
            $r['user_id'] = $this->getUser()->getId();
        if ( $mode == NormalizeMode::FULL)
            $r['code'] = $this->getCode();
        return $r;
    }

    public function fromArray(CollectionService $entity, array $data)
    {
        // TODO: Implement fromArray() method.
    }

    public function increaseTry(): int
    {
        $this->setTry($this->getTry() + 1);
        return $this->getTry();
    }
}
