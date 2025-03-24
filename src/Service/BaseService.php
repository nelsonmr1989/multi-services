<?php

namespace App\Service;


use App\Entity\Base;
use App\Entity\User;
use App\Exception\Forbidden;
use App\Exception\NotFound;
use App\Exception\Validation as CustomValidation;
use App\Interfaces\IGuard;
use App\Interfaces\IValidator;
use App\Service\Common\CollectionService;
use App\Validation\Helper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BaseService
{
    protected EntityManagerInterface $em;
    protected ValidatorInterface $validator;
    protected Security $security;
    protected CollectionService $collectionService;

    function __construct(EntityManagerInterface $em, Security $security, ValidatorInterface $validator, CollectionService $collectionService)
    {
        $this->em = $em;
        $this->security = $security;
        $this->validator = $validator;
        $this->collectionService = $collectionService;
    }

    protected function _validate(
        IValidator $validation,
        array      $data,
                   $executeException = true
    ): array
    {
        $v = Helper::parse($data, $validation->getValidations());
        $collections = new Assert\Collection([
            'fields' => $validation->getValidations(),
            'allowMissingFields' => true,
            'allowExtraFields' => true
        ]);

        $violations = $this->validator->validate($v, $collections);

        $r = ['messages' => []];
        if (count($violations) > 0) {
            $r = [
                'messages' => Helper::parseMessage($violations)
            ];
            if ($executeException) {
                throw new CustomValidation(null, 400, $r);
            }
        }

        return $r;
    }

    protected function _getObject($id, $entity, $ignoreRole = false, $ignoreException = false)
    {
        try {
            if ($obj = $this->em->getRepository($entity)->findOneBy(['id' => $id])) {
                $isAdmin = $this->security->getUser()->getRole() == 'ROLE_ADMIN';
                if (!$isAdmin and !$ignoreRole and $obj instanceof IGuard) {
                    if (!$obj->isOwner($this->security->getUser())) {
                        throw new Forbidden('You do not have access to this resource', 403);
                    }
                }
                if (!$obj instanceof Base or ($obj instanceof Base && !$obj->isDelete())) {
                    return $obj;
                }
            }

            throw new NotFound('Not found any object with id: ' . $id, 404);
        } catch (\Exception $exception) {
            if ($ignoreException) {
                return null;
            } else throw $exception;
        }
    }

    protected function _deleteObject($id, $entity): bool
    {
        $obj = $this->_getObject($id, $entity);
        $this->em->remove($obj);
        $this->em->flush();

        return true;
    }
}
