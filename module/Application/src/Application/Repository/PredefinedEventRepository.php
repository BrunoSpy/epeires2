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

use Application\Entity\Category;
use Doctrine\Common\Collections\Criteria;
use Gedmo\Sortable\Entity\Repository\SortableRepository;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class PredefinedEventRepository extends SortableRepository
{

    /**
     * @param $category
     * @return array
     */
    public function getEventsWithCategoryAsArray(Category $category)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('category', $category));
        $criteria->andWhere(Criteria::expr()->isNull('parent'));
        $criteria->andWhere(Criteria::expr()->eq('listable', true));
        $criteria->orderBy(array(
            'place' => 'ASC'
        ));
        $list = parent::matching($criteria);
        $res = array();
        foreach ($list as $element) {
            $res[$element->getId()] = $element->getName();
        }
        return $res;
    }

    /**
     * Returns an array with mandatory predefined events from the children
     * of the given category
     * @param Category $category
     * @return array
     */
    public function getEventsFromCategoryAsArray(Category $category) {
        $res = array();
        foreach ($category->getChildren() as $cat) {
            $criteria = Criteria::create()->where(Criteria::expr()->eq('category', $cat));
            $criteria->andWhere(Criteria::expr()->isNull('parent'));
            $criteria->andWhere(Criteria::expr()->eq('listable', true));
            $criteria->andWhere(Criteria::expr()->eq('forceroot', true));
            $criteria->orderBy(array(
                'place' => 'ASC'
            ));
            $list = parent::matching($criteria);

            foreach ($list as $element) {
                $res[$element->getId()] = array('name' => $element->getName(), 'catid' => $cat->getId());
            }
        }
        return $res;
    }

    public function getChildsAsArray($parentid)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('parent', $id));
        $criteria->andWhere(Criteria::expr()->eq('listable', true));
        $criteria->orderBy('order');
        $list = parent::matching($criteria);
        $res = array();
        foreach ($list as $element) {
            $res[$element->getId()] = $element->getName();
        }
        return $res;
    }

    public function getRootsAsArray($id = null)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->isNull('parent'));
        if ($id) {
            $criteria->andWhere(Criteria::expr()->neq('id', $id));
        }
        $list = parent::matching($criteria);
        $res = array();
        foreach ($list as $element) {
            $res[$element->getId()] = $element->getName();
        }
        return $res;
    }
}