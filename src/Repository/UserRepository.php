<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getUserByEmailOrPhone($email, $phone) {
        $connection = $this->getEntityManager()->getConnection();

        $sql = " 
                SELECT up.email, up.phone_number, up.id
                FROM user_app up
                WHERE up.email = :email OR up.phone_number = :phone
        ";

        $statement = $connection->prepare($sql);

        $statement->bindValue('email', $email);
        $statement->bindValue('phone', $phone);

        $result = $statement->executeQuery()->fetchAllAssociative();
        return !$result ? [] : $result;
    }
}
