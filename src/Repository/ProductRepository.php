<?php

namespace App\Repository;

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

    public function getProductsByOriginIds($ids) {
        $em = $this->getEntityManager();

        $idsString = implode("','", $ids);
        $predicates = "p.originId IN ('".$idsString."')";

        return $em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->where($predicates)
            ->getQuery()
            ->getResult();
    }

    public function softDeleteByIds($ids) {
        $connection = $this->getEntityManager()->getConnection();

        $idsString = implode("','", $ids);

        $sql = "
            UPDATE product p
            SET p._deleted = NOW()
            WHERE p.origin_id NOT IN ('".$idsString."')
        ";

        $statement = $connection->prepare($sql);
        return $statement->executeQuery()->rowCount();
    }
}
