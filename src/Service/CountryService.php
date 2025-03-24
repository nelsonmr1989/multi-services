<?php

namespace App\Service;

use App\Entity\Country;
use App\Service\Common\CollectionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CountryService extends BaseService
{

    function __construct(
        EntityManagerInterface $em,
        Security $security,
        ValidatorInterface $validator,
        CollectionService $collectionService
    )
    {
        parent::__construct($em, $security, $validator, $collectionService);
    }

    public function get($id): Country
    {
        return parent::_getObject($id, Country::class);
    }

    public function getAll()
    {
        return $this->em->getRepository(Country::class)->findBy(['_deleted' => null]);
    }

}
