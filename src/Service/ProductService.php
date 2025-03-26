<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\Category;
use App\Service\Common\CollectionService;
use App\Validation\Product\CreateProductValidation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductService extends BaseService
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

    public function get($id): Product
    {
        return parent::_getObject($id, Product::class);
    }

    public function filter($filters, $start = 0, $limit = 10, $orderBy = null) {
        return $this->em->getRepository(Product::class)->filter($filters, $start, $limit, $orderBy);
    }

    public function create(array $data) {
        $this->_validate(new CreateProductValidation(), $data);

        $obj = new Product();
        $obj->fromArray($this->collectionService, $data);

        $this->em->persist($obj);
        $this->em->flush();

        return $obj;
    }

    public function update(string $id, array $data) {
        $this->_validate(new CreateProductValidation(), $data);

        $obj = $this->get($id);

        $obj->fromArray($this->collectionService, $data);

        $this->em->persist($obj);
        $this->em->flush();

        return $obj;
    }

    public function delete(string $id): bool
    {
        // TODO - When implement the save images in the file system, delete the images here
        return parent::_deleteObject($id, Product::class);
    }

}
