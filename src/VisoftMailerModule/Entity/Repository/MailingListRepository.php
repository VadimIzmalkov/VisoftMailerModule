<?php

namespace VisoftMailerModule\Entity\Repository;

use Doctrine\ORM\EntityRepository; 

class MailingListRepository extends EntityRepository
{
    public function getCount()
    {
        $queryBuilder = $this->createQueryBuilder('mailingList');
        $queryBuilder->select('count(mailingList.id)');
        return  $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function findAllPaginated($paginationQuery = null)
    {
        $queryBuilder = $this->createQueryBuilder('mailingList');
        $queryBuilder->select('mailingList.id', 'mailingList.name');
        if(isset($paginationQuery['count-per-page']))
            $countPerPage = $paginationQuery['count-per-page'];
        else 
            $countPerPage = 10;
        $queryBuilder->setMaxResults($countPerPage);
        if(isset($paginationQuery['order']) && isset($paginationQuery['order-by']))
            $queryBuilder->orderBy('mailingList.' . $paginationQuery['order-by'], $paginationQuery['order']);
        if(isset($paginationQuery['page']))
            $offset = ($paginationQuery['page'] == 0) ? 0 : ($paginationQuery['page'] - 1) * $countPerPage;
        else
            $offset = 0;
        $queryBuilder->setFirstResult($offset);
        return $queryBuilder->getQuery()->getResult();
    }

    // public function findByMailingListPaginated($mailingList, $paginationQuery = null)
    // {
    //     $queryBuilder = $this->createQueryBuilder('mailingList');
    //     $queryBuilder->select('mailingList.id', 'mailingList.name');
    // }
}