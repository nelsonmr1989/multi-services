<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    function filter(array $filters = [], int $start = 0, int $limit = 10, $orderBy = null)
    {
        return parent::_filter(Product::class, $filters, null, $start, $limit, $orderBy);
    }
}
