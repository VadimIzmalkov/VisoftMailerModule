<?php

namespace VisoftMailerModule\Entity\Repository;

use Doctrine\ORM\EntityRepository; 

class ContactRepository extends EntityRepository
{
    public function findByMailingListIds($mailingListIds)
    {
        $queryBuilder = $this->createQueryBuilder('contact');
        $queryBuilder
            ->select('contact.id', 'contact.email')
            ->leftJoin('contact.subscribedOnMailingLists', 'subscribedOnMailingLists')
            ->add('where', $queryBuilder->expr()->in('subscribedOnMailingLists', $mailingListIds));
        return $queryBuilder->getQuery()->getResult();
    }

    public function getCountByMailingListIds($mailingListIds)
    {
        $queryBuilder = $this->createQueryBuilder('contact');
        $queryBuilder
            ->select('count(contact.id)')
            ->leftJoin('contact.subscribedOnMailingLists', 'subscribedOnMailingLists')
            ->add('where', $queryBuilder->expr()->in('subscribedOnMailingLists', $mailingListIds));
        return  $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function findByMailingListIdsPaginated($mailingListIds, $paginationQuery = null)
    {
        $queryBuilder = $this->createQueryBuilder('contact');
        $queryBuilder
            ->select('contact.id', 'contact.email', 'state.name as stateName', 'contact.registerDate')
            ->leftJoin('contact.state', 'state')
            ->leftJoin('contact.subscribedOnMailingLists', 'subscribedOnMailingLists')
            ->add('where', $queryBuilder->expr()->in('subscribedOnMailingLists', $mailingListIds));
        if(isset($paginationQuery['count-per-page']))
            $countPerPage = $paginationQuery['count-per-page'];
        else 
            $countPerPage = 10;
        $queryBuilder->setMaxResults($countPerPage);
        if(isset($paginationQuery['order']) && isset($paginationQuery['order-by']))
            $queryBuilder->orderBy('contact.' . $paginationQuery['order-by'], $paginationQuery['order']);
        if(isset($paginationQuery['page']))
            $offset = ($paginationQuery['page'] == 0) ? 0 : ($paginationQuery['page'] - 1) * $countPerPage;
        else
            $offset = 0;
        $queryBuilder->setFirstResult($offset);
        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllPaginated($paginationQuery = null)
    {
        $queryBuilder = $this->createQueryBuilder('contact');
        $queryBuilder
            ->select('contact.id', 'contact.email', 'state.name as stateName', 'contact.registerDate')
            ->leftJoin('contact.state', 'state');        
        if(isset($paginationQuery['count-per-page']))
            $countPerPage = $paginationQuery['count-per-page'];
        else 
            $countPerPage = 10;
        $queryBuilder->setMaxResults($countPerPage);
        if(isset($paginationQuery['order']) && isset($paginationQuery['order-by']))
            $queryBuilder->orderBy('contact.' . $paginationQuery['order-by'], $paginationQuery['order']);
        if(isset($paginationQuery['page']))
            $offset = ($paginationQuery['page'] == 0) ? 0 : ($paginationQuery['page'] - 1) * $countPerPage;
        else
            $offset = 0;
        $queryBuilder->setFirstResult($offset);
        return $queryBuilder->getQuery()->getResult();
    }
}