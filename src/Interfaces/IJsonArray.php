<?php
namespace App\Interfaces;

use App\Service\Common\CollectionService;

interface IJsonArray
{
    public function toArray(CollectionService $entity, $mode);
    public function fromArray(CollectionService $entity, array $data);
}
