<?php


namespace App\Listener\Doctrine;


use App\Entity\Base;
use Doctrine\ORM\Event\OnFlushEventArgs;

class DoctrineListener
{
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        $date = new \DateTime();
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($this->setEntityAttr($entity, 'created', $date)) {
                $class = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($class, $entity);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($this->setEntityAttr($entity, 'updated', $date)) {
                $class = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($class, $entity);
            }
        }

    }

    private function setEntityAttr($entity, $attr, $value): bool
    {
        if ($entity instanceof Base) {
            if ($attr == 'created')
                $entity->setCreated($value);
            else
                $entity->setUpdated($value);
            return true;
        }
        return false;
    }

}
