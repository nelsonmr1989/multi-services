<?php

namespace App\Controller\Recipient;

use App\Controller\BaseService;
use App\Controller\Recipient\Validations\CreateRecipientValidation;
use App\Entity\Recipient;
use App\Service\CollectionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RecipientService extends BaseService
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

    public function get($id): Recipient
    {
        return parent::_getObject($id, Recipient::class);
    }

    public function create(array $data): Recipient
    {
        // TODO: defined the way to send the fields: country, province, state

        $this->_validate(new CreateRecipientValidation(), $data);

        $obj = new Recipient();
        $obj->fromArray($this->collectionService, $data);

        $obj->setUser($this->security->getUser());

        $this->em->persist($obj);
        $this->em->flush();

        return $obj;
    }

    public function update(string $id, array $data): Recipient {

        // TODO: defined the way to send the fields: country, province, state

        $this->_validate(new CreateRecipientValidation(), $data);

        $obj = $this->get($id);
        $obj->fromArray($this->collectionService, $data);

        $this->em->persist($obj);
        $this->em->flush();

        return $obj;
    }

    public function list() {
        $recipients = $this->security->getUser()->getRecipients();
        return $recipients;
    }

    public function delete($id): bool {
        $obj = $this->get($id);

        $obj->setDeleted(new \DateTime());
        $this->em->persist($obj);
        $this->em->flush();

        return true;
    }

}
