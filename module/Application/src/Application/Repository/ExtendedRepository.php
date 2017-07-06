<?php
/*
 * This file is part of Epeires².
 * Epeires² is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Epeires² is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Epeires². If not, see <http://www.gnu.org/licenses/>.
 *
 * (c) Bruno Spyckerelle <bruno.spyckerelle@aviation-civile.gouv.fr>
 */
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class ExtendedRepository extends EntityRepository
{

    /**
     *
     * @return array
     */
    public function getAllAsArray($criteria = null)
    {
        $list = array();
        if ($criteria === null) {
            $list = parent::findAll();
        } else {
            if ($criteria instanceof Criteria) {
                $list = parent::matching($criteria);
            } else {
                $list = parent::findBy($criteria);
            }
        }
        $res = array();
        foreach ($list as $element) {
            $res[$element->getId()] = $element->getName();
        }
        return $res;
    }
    
    public function getItems($offset = 0, $limit = 10) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('f')
            ->from($this->getEntityName(), 'f')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        $query = $qb->getQuery();
        return new Paginator($query);
    }
    
    public function count()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select(array('u.id'))
            ->from($this->getEntityName(), 'u');
        
        $result = $query->getQuery()->getResult();
        
        return count($result);
    }
}