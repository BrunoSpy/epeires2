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
 */
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;
use Application\Entity\Organisation;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class QualificationZoneRepository extends EntityRepository
{

    /**
     *
     * @return array
     */
    public function getAllAsArray(Organisation $organisation = null)
    {
        $list = array();
        if ($organisation) {
            $criteria = Criteria::create()->where(Criteria::expr()->eq('organisation', $organisation));
            $list = parent::matching($criteria);
        } else {
            $list = parent::findAll();
        }
        $res = array();
        foreach ($list as $element) {
            $res[$element->getId()] = $element->getName();
        }
        return $res;
    }
}