<?php

namespace App\Service\Common;

use App\Enum\NormalizeMode;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use App\Interfaces\IJsonArray;
use App\Service\MediaService;

class CollectionService
{
    private $em;
    private MediaService $mediaService;

    function __construct(EntityManagerInterface $em, MediaService $mediaService)
    {
        $this->em = $em;
        $this->mediaService = $mediaService;
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

    public function getMediaService()
    {
        return $this->mediaService;
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

    public function getDateFromString($stringDate, $isDateTime = true,  bool $convertToUtc = false, $format = null) {
        $date = null;
        if (!empty($stringDate)) {
            $format = ((!empty($format)) ? $format : $isDateTime) ? 'Y-m-d H:i:s' : 'Y-m-d';
            try {
                $date = \DateTime::createFromFormat($format, $stringDate);
                if(!$isDateTime) {
                    $date->setTime(00,00,00);
                }
                if ($convertToUtc) {
                    $date->setTimezone(new \DateTimeZone('UTC'));
                }
            } catch (\Exception $e) {
                $date = null;
            }
        }
        return (is_object($date)) ? $date : null;
    }
}
