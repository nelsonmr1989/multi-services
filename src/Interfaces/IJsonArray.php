<?php
namespace App\Interfaces;

use App\Enum\NormalizeMode;
use App\Service\CollectionService;

interface IJsonArray
{
    public function toArray(CollectionService $entity, $mode);
    public function fromArray(CollectionService $entity, array $data);
}
