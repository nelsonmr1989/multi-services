<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    function filter(array $filters = [], int $start = 0, int $limit = 10, $orderBy = null)
    {
        return parent::_filter(Category::class, $filters, null, $start, $limit, $orderBy);
    }

    public function existExactlyName(string $name): bool
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = " 
                SELECT EXISTS (
                    SELECT 1
                    FROM category
                    WHERE BINARY name = :name
                ) AS exists_result;
        ";

        $statement = $connection->prepare($sql);
        $statement->bindValue('name', $name);

        return $statement->executeQuery()->fetchOne() ? true : false;

    }

    public function getCategoriesByOriginIds($ids) {
        $em = $this->getEntityManager();

        $idsString = implode("','", $ids);
        $predicates = "c.originId IN ('".$idsString."')";

        return $em->createQueryBuilder()
            ->select('c')
            ->from(Category::class, 'c')
            ->where($predicates)
            ->getQuery()
            ->getResult();
    }

    public function softDeleteByIds($ids) {
        $connection = $this->getEntityManager()->getConnection();

        $idsString = implode("','", $ids);

        $sql = "
            UPDATE category c
            SET c._deleted = NOW()
            WHERE c.origin_id NOT IN ('".$idsString."')
        ";

        $statement = $connection->prepare($sql);
        return $statement->executeQuery()->rowCount();
    }
}
