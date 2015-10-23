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

use Doctrine\Common\Collections\Criteria;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class CategoryRepository extends ExtendedRepository
{

    /**
     *
     * @return array
     */
    public function getRootsAsArray($id = null, $timeline = null)
    {
        $res = array();
        foreach ($this->getRoots($id, $timeline) as $element) {
            $res[$element->getId()] = $element->getName();
        }
        return $res;
    }

    public function getRoots($id = null, $timeline = null)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->isNull('parent'));
        if ($timeline) {
            $criteria->andWhere(Criteria::expr()->eq('timeline', true));
        }
        if ($id) {
            $criteria->andWhere(Criteria::expr()->neq('id', $id));
        }
        $criteria->orderBy(array(
            'place' => Criteria::ASC
        ));
        $list = parent::matching($criteria);
        return $list;
    }

    public function getChilds($onlytimeline, $parentId = null)
    {
        if ($parentId) {
            $criteria = Criteria::create()->where(Criteria::expr()->eq('parent', parent::find($parentId)));
        } else {
            $criteria = Criteria::create()->where(Criteria::expr()->neq('parent', null));
        }
        if ($onlytimeline) {
            $criteria->andWhere(Criteria::expr()->eq('timeline', true));
        }
        $criteria->orderBy(array(
            'place' => Criteria::ASC
        ));
        $list = parent::matching($criteria);
        
        return $list;
    }

    public function getChildsAsArray($parentId = null)
    {
        $res = array();
        foreach ($this->getChilds($parentId) as $element) {
            $res[$element->getId()] = $element->getName();
        }
        return $res;
    }
}