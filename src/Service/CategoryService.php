<?php

namespace App\Service;

use App\Validation\Category\CreateCategoryValidation;
use App\Entity\Category;
use App\Entity\Country;
use App\Exception\Validation;
use App\Service\Common\CollectionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryService extends BaseService
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

    public function get($id): Category
    {
        return parent::_getObject($id, Category::class);
    }

    public function create(array $data) {
        $this->_validate(new CreateCategoryValidation(), $data);

        $exist = $this->em->getRepository(Category::class)->existExactlyName($data['name']);
        if ($exist) {
            throw new Validation('Ya tienes una categoría creada con este nombre.', 400);
        }

        $obj = new Category();
        $obj->fromArray($this->collectionService, $data);

        $this->em->persist($obj);
        $this->em->flush();

        return $obj;
    }

    public function update(string $id, array $data) {
        $this->_validate(new CreateCategoryValidation(), $data);

        $obj = $this->get($id);

        if ($data['name'] != $obj->getName()) {
            $exist = $this->em->getRepository(Category::class)->existExactlyName($data['name']);
            if ($exist) {
                throw new Validation('Ya tienes una categoría creada con este nombre.', 400);
            }
        }

        $obj->fromArray($this->collectionService, $data);

        $this->em->persist($obj);
        $this->em->flush();

        return $obj;
    }

    public function list() {
        return $this->em->getRepository(Category::class)->findBy(['_deleted' => null]);
    }

    public function delete(string $id): bool {
        //TODO implement this function

        return true;
    }

}
