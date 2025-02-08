<?php

namespace App\Service;

use App\Enum\NormalizeMode;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use App\Interfaces\IJsonArray;

class CollectionService
{
    private $em;

    function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function collectionToArray($entities, $mode = NormalizeMode::BASIC)
    {
        $data = [];
        foreach ($entities as $entity) {
            $tmp = $this->entityToArray($entity, $mode);
            if (!is_null($tmp)) {
                $data[] = $tmp;
            }
        }
        return $data;
    }

    public function entityToArray($entity, $mode = NormalizeMode::BASIC)
    {
        return $entity instanceof IJsonArray && is_object($entity) ? $entity->toArray($this, $mode) : null;
    }

    public function entityFromArray($entity, array $data)
    {
        if ($entity instanceof IJsonArray) {
            $entity->fromArray($this, $data);
        }
    }

    public function getEntityManager()
    {
        return $this->em;
    }

    public function collectionFromArray(
        Collection $collection,
        array      $data,
                   $classType,
                   $collectionKeyGetter = null,
                   $dataKeyGetter = null)
    {
        $collectionKeyGetter = $collectionKeyGetter ??
            function ($item) {
                return $item->getId();
            };

        $dataKeyGetter = $dataKeyGetter ??
            function ($item) {
                return $item['id'] ?? '';
            };

        $collectionKeys = [];
        $dataKeys = [];

        foreach ($data as $itemData) {
            array_push($dataKeys, $dataKeyGetter($itemData));
        }

        foreach ($collection as $item) {
            $entityKey = $collectionKeyGetter($item);
            $found = in_array($entityKey, $dataKeys);
            $collectionKeys[$item->getId()] = $collectionKeyGetter($item);
            if (!$found) {
                $collection->removeElement($item);
            }
        }

        foreach ($data as $itemData) {
            $entity = new $classType();
            $itemDataKey = $dataKeyGetter($itemData);
            $found = array_search($itemDataKey, $collectionKeys);
            if (!empty($found)) {
                $entity = $this->em->getReference($classType, $found);
            } else {
                $collection->add($entity);
            }

            $this->entityFromArray($entity, $itemData);
        }

        return $collection;
    }

    public function transformManyToManyRelations(array $data, Collection $objects, $class)
    {
        $transform = [];
        foreach ($data as $d) {
            if (isset($d['id'])) {
                $transform[$d['id']] = $d;
            }
        }
        foreach ($objects as $key => $obj) {
            if (isset($transform[$key])) {
                unset($transform[$key]);
            } else {
                $objects->remove($key);
            }
        }
        foreach ($transform as $key => $t) {
            $objects[$key] = $this->getEntityManager()->getReference($class, $key);
        }
    }

    public function getStringFromDate(?\DateTime $date, $format = null)
    {
        $format = (!empty($format)) ? $format : "Y-m-d";
        return ($date) ? $date->format($format) : null;
    }
}
