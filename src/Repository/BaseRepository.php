<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\DBAL\Types\Types;

abstract class BaseRepository extends ServiceEntityRepository
{
    protected function _filterCount($class, array $filters = [], User $user = null)
    {
        $parameters = [];
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('COUNT(o)')
            ->from($class, 'o');

        $obj = new $class();

        if ($user) {
            if (method_exists($obj, 'getUser')) {
                //has direct relation with User
                $qb->leftJoin('o.user', 'user');
            } else {
                if (!method_exists($obj, 'getRelationsUserPath') or !$userPath = $obj->getRelationsUserPath()) {
                    die("Please implement in $class the *getRelationsUserPath* method");
                }
                $eResult = explode('.', $userPath);
                $lastJoin = '';
                foreach ($eResult as $join) {
                    if ($lastJoin) {
                        $qb->leftJoin("$lastJoin.$join", $join);
                    } else {
                        $qb->leftJoin("o.$join", $join);
                    }
                    $lastJoin = $join;
                }
            }
            $qb->andWhere("user.id = :user_id  ");
            $parameters['user_id'] = $user->getId();
        }

        //Applying Basic Filters
        foreach ($filters as $index => $filter) {
            if ($filter['value'] === null) {
                if ($filter['operator'] == '=') {
                    $qb->andWhere("o." . $filter['field'] . " IS NULL");
                } else {
                    $qb->andWhere("o." . $filter['field'] . " IS NOT NULL");
                }
            }
            if ($filter['operator'] == 'IN') {
                if (is_array($filter['value'])) {
                    $stringParameters = "";
                    foreach ($filter['value'] as $inIndex => $value) {
                        $stringParameters .= ":inParam$inIndex,";
                        $parameters["inParam$inIndex"] = $value;
                    }

                    $stringParameters = substr($stringParameters, 0, -1);
                    $qb->andWhere("o." . $filter['field'] . " IN ($stringParameters)");
                } else {
                    $qb->andWhere("o." . $filter['field'] . " IN (:param$index)");
                    $parameters[":param$index"] = $filter['value'];
                }
            } else {
                if ($filter['operator'] == 'LIKE') {
                    $filter['value'] = str_replace(['*'], '%', $filter['value']);
                }
                $qb->andWhere("o." . $filter['field'] . " " . $filter['operator'] . " :param$index");
                $parameters["param$index"] = $filter['value'];
            }
        }

        if (count($parameters) > 0) {
            $qb->setParameters($parameters);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    protected function _filter($class, array $filters = [], $fields = [], $start = 0, $limit = 10, $orderBy = null, User $user = null, $hydratation = Query::HYDRATE_OBJECT)
    {
        $parameters = [];
        $select = 'o';
        if (is_array($fields) and count($fields) > 0) {
            $select = '';
            foreach ($fields as $value) {
                $select .= 'o.' . $value;
                if ($value != end($fields))
                    $select .= ', ';
            }

        }
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select($select)
            ->from($class, 'o');

        $obj = new $class();

        if ($user) {
            if (method_exists($obj, 'getUser')) {
                //has direct relation with User
                $qb->leftJoin('o.user', 'user');
            } else {
                if (!method_exists($obj, 'getRelationsUserPath') or !$userPath = $obj->getRelationsUserPath()) {
                    die("Please implement in $class the *getRelationsUserPath* method");
                }
                $eResult = explode('.', $userPath);
                $lastJoin = '';
                foreach ($eResult as $join) {
                    if ($lastJoin) {
                        $qb->leftJoin("$lastJoin.$join", $join);
                    } else {
                        $qb->leftJoin("o.$join", $join);
                    }
                    $lastJoin = $join;
                }
            }
            $qb->andWhere("user.id = :user_id");
            $parameters['user_id'] = $user->getId();
        }

        //getting relations fields and remove form $filters
        /*
        $relationFields = [];
        if (method_exists($obj, 'getRelationsFilterFields')) {
            $baseRelationFields = $obj->getRelationsFilterFields();
            foreach ($filters as $key => $filter) {
                if (array_key_exists($filter['field'], $baseRelationFields)) {
                    $eResult = explode('.', $baseRelationFields[$filter['field']]);
                    $joins = [];
                    for ($i = 0; $i < count($eResult) - 1; $i++)
                        $joins[] = $eResult[$i];

                    $relationFields[] = [
                        'field' => $eResult[count($eResult) - 1],
                        'joins' => $joins,
                        //'alias' => $filter['field'],
                        'operator' => $filter['operator'],
                        'value' => $filter['value']
                    ];
                    unset($filters[$key]);
                }
            }
        }
        */

        //Applying Basic Filters
        foreach ($filters as $index => $filter) {
            if ($filter['value'] === null) {
                if ($filter['operator'] == '=') {
                    $qb->andWhere("o." . $filter['field'] . " IS NULL");
                } else {
                    $qb->andWhere("o." . $filter['field'] . " IS NOT NULL");
                }
            } else if ($filter['operator'] == 'IN') {
                if (is_array($filter['value'])) {
                    $stringParameters = "";
                    foreach ($filter['value'] as $inIndex => $value) {
                        $stringParameters .= ":inParam$inIndex,";
                        $parameters["inParam$inIndex"] = $value;
                    }

                    $stringParameters = substr($stringParameters, 0, -1);
                    $qb->andWhere("o." . $filter['field'] . " IN ($stringParameters)");
                } else {
                    $qb->andWhere("o." . $filter['field'] . " IN (:param$index)");
                    $parameters[":param$index"] = $filter['value'];
                }
            } else {
                if ($filter['operator'] == 'LIKE') {
                    $filter['value'] = str_replace(['*'], '%', $filter['value']);
                }
                $qb->andWhere("o." . $filter['field'] . " " . $filter['operator'] . " :param$index");
                $parameters["param$index"] = $filter['value'];
            }
        }

        //Applying Relation Filters
        /*
        foreach ($relationFields as $key => $filter) {
            $lastJoinAlias = 'o.';
            foreach ($filter['joins'] as $join) {
                $qb->leftJoin($lastJoinAlias . $join, $join);
                $lastJoinAlias = $join . '.';
            }
            if ($filter['operator'] == '=') {
                $qb->andWhere($lastJoinAlias . $filter['field'] . " IN (" . $filter['value'] . ")");
            }
            $eResult = explode(',', $filter['value']);
            $qb->groupBy('o.id');
            $qb->andHaving('COUNT(o.id) = ' . count($eResult));
        }
        */

        if ($orderBy) {
            $qb->addOrderBy('o.' . $orderBy['field'], $orderBy['order']);
        }

        if (count($parameters) > 0) {
            $qb->setParameters($parameters);
        }

        $qb->setFirstResult($start);
        $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult($hydratation);
    }
}
