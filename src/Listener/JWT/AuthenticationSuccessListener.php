<?php

namespace App\Listener\JWT;

use App\Entity\Business;
use App\Entity\User;
use App\Enum\NormalizeMode;
use App\Service\CollectionService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;


class AuthenticationSuccessListener
{
    private $entityManager;
    private $collectionService;

    public function __construct(EntityManagerInterface $entityManager, CollectionService $collectionService)
    {
        $this->entityManager = $entityManager;
        $this->collectionService = $collectionService;
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }
        $data['user'] = $user->toArray($this->collectionService, NormalizeMode::MEDIUM);

        $event->setData($data);
    }
}
