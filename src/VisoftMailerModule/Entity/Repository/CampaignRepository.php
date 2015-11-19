<?php

namespace VisoftMailerModule\Entity\Repository;

use Doctrine\ORM\EntityRepository; 

class CampaignRepository extends EntityRepository
{
    public function getCount()
    {
        $queryBuilder = $this->createQueryBuilder('campaign');
        $queryBuilder->select('count(campaign.id)');
        return  $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function findAllPaginated($paginationQuery = null)
    {
        $queryBuilder = $this->createQueryBuilder('campaign');
        $queryBuilder->select('campaign.id', 'campaign.name');
        if(isset($paginationQuery['count-per-page']))
            $countPerPage = $paginationQuery['count-per-page'];
        else 
            $countPerPage = 10;
        $queryBuilder->setMaxResults($countPerPage);
        if(isset($paginationQuery['order']) && isset($paginationQuery['order-by']))
            $queryBuilder->orderBy('campaign.' . $paginationQuery['order-by'], $paginationQuery['order']);
        if(isset($paginationQuery['page']))
            $offset = ($paginationQuery['page'] == 0) ? 0 : ($paginationQuery['page'] - 1) * $countPerPage;
        else
            $offset = 0;
        $queryBuilder->setFirstResult($offset);
        return $queryBuilder->getQuery()->getResult();
    }
}