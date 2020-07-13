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

use Application\Entity\AfisCategory;
use Application\Entity\AlertCategory;
use Application\Entity\AntennaCategory;
use Application\Entity\ATFCMCategory;
use Application\Entity\BrouillageCategory;
use Application\Entity\Category;
use Application\Entity\FieldCategory;
use Application\Entity\FlightPlanCategory;
use Application\Entity\FrequencyCategory;
use Application\Entity\InterrogationPlanCategory;
use Application\Entity\MilCategory;
use Application\Entity\RadarCategory;
use Doctrine\Common\Collections\Criteria;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class CategoryRepository extends ExtendedRepository
{

    /**
     * Return all categories matching criteria, ordered by place and add > to children
     * @param null $criteria
     * @return array|void
     */
    public function getAllAsArray($criteria = null)
    {
        //first get root categories
        $roots = array();
        if ($criteria instanceof Criteria) {
            $criteria->andWhere(Criteria::expr()->isNull('parent'))
                    ->orderBy(array('place'=> 'ASC'));
            $roots = parent::matching($criteria);
        } elseif (is_array($criteria)) {
            $criteria['parent'] = null;
            $roots = parent::findBy($criteria, array('place'=>'ASC'));
        } else {
            $newCriteria = Criteria::create()->where(Criteria::expr()->isNull('parent'))->orderBy(array('place'=> 'ASC'));
            $roots = parent::matching($newCriteria);
        }

        $res = array();
        foreach ($roots as $root) {
            $res[$root->getId()] = $root->getName();
            $children = array();
            if ($criteria instanceof Criteria) {
                $criteria->andWhere(Criteria::expr()->eq('parent', $root->getId()))
                    ->orderBy(array('place'=> 'ASC'));
                $children = parent::matching($criteria);
            } elseif (is_array($criteria)) {
                $criteria['parent'] = $root->getId();
                $children = parent::findBy($criteria, array('place'=>'ASC'));
            } else {
                $newCriteria = Criteria::create()->where(Criteria::expr()->eq('parent', $root))->orderBy(array('place'=> 'ASC'));
                $children = parent::matching($newCriteria);
            }
            foreach ($children as $child) {
                $res[$child->getId()] = " > " . $child->getName();
            }
        }
        return $res;
    }


    /**
     * @param null $id Exclude specific id from results
     * @param true $system If false, exclude system categories
     * @param null $archived If true, return archived categories
     * @return array
     */
    public function getRootsAsArray($id = null, $system = true, $archived = false)
    {
        $res = array();
        foreach ($this->getRoots($id, $system, $archived) as $element) {
            $res[$element->getId()] = $element->getName();
        }
        return $res;
    }

    
    public function getRoots($id = null, $system = true, $archived = false)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->isNull('parent'));
        
        if ($id) {
            $criteria->andWhere(Criteria::expr()->neq('id', $id));
        }
        if($system == false) {
            $criteria->andWhere(Criteria::expr()->eq('system', false));
        }
        if($archived) {
            $criteria->andWhere(Criteria::expr()->eq('archived', true));
        } else {
            $criteria->andWhere(Criteria::expr()->eq('archived', 0));
        }
        $criteria->orderBy(array(
            'place' => Criteria::ASC
        ));
        $list = parent::matching($criteria);
        return $list;
    }

    public function getAllRootsAsArray($system = false)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->isNull('parent'));
        $criteria->andWhere(Criteria::expr()->eq('system', false));
        $criteria->orderBy(array(
            'place' => Criteria::ASC
        ));
        $result = array();
        foreach (parent::matching($criteria) as $element) {
            $result[$element->getId()] = $element->getName();
        }
        return $result;
    }
    
    /**
     * @param null $parentId If null, returns root categories
     * @param null $archived If true, return archived categories
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChilds($parentId = null, $archived = null)
    {
        if ($parentId) {
            $criteria = Criteria::create()->where(Criteria::expr()->eq('parent', parent::find($parentId)));
        } else {
            $criteria = Criteria::create()->where(Criteria::expr()->neq('parent', null));
        }
        if($archived != null && $archived == true) {
            $criteria->andWhere(Criteria::expr()->eq('archived', true));
        } else {
            $criteria->andWhere(Criteria::expr()->eq('archived', false));
        }
        $criteria->orderBy(array(
            'place' => Criteria::ASC
        ));
        $list = parent::matching($criteria);
        
        return $list;
    }

    public function getAllChildsAsArray($parentId)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('parent', parent::find($parentId)));
        $criteria->orderBy(array(
            'place' => Criteria::ASC
        ));
        $result = array();
        foreach (parent::matching($criteria) as $element) {
            $result[$element->getId()] = $element->getName();
        }
        return $result;
    }

    public function getChildsAsArray($parentId = null, $archived = null)
    {
        $res = array();
        foreach ($this->getChilds($parentId, $archived) as $element) {
            $res[$element->getId()] = $element->getName();
        }
        return $res;
    }

    public function delete(Category $category)
    {
        $childs = $this->findBy(array(
            'parent' => $category->getId()
        ));
        foreach ($childs as $child) { // detach childs
            $child->setParent(null);
            $this->getEntityManager()->persist($child);
        }
        // delete fieldname to avoid loop
        $category->setFieldname(null);
        if ($category instanceof AntennaCategory) {
            $category->setAntennafield(null);
            $category->setFrequenciesField(null);
            $category->setStatefield(null);
        }
        if ($category instanceof RadarCategory) {
            $category->setRadarfield(null);
            $category->setStatefield(null);
        }
        if ($category instanceof FrequencyCategory) {
            $category->setCurrentAntennaField(null);
            $category->setStateField(null);
            $category->setFrequencyField(null);
            $category->setOtherFrequencyField(null);
            $category->setCauseField(null);
        }
        if ($category instanceof BrouillageCategory) {
            $category->setFrequencyField(null);
        }
        if ($category instanceof MilCategory) {
            $category->setLowerLevelField(null);
            $category->setUpperLevelField(null);
        }
        if($category instanceof AfisCategory) {
            $category->setAfisfield(null);
            $category->setStatefield(null);
        }
        if($category instanceof FlightPlanCategory) {
            $category->setAircraftidfield(null);
            $category->setDestinationfield(null);
            $category->setStartfield(null);
            $category->setAlertfield(null);
            $category->setEstimatedtimeofarrivalfield(null);
        }
        if($category instanceof AlertCategory) {
            $category->setTypeField(null);
            $category->setCauseField(null);
        }
        if($category instanceof InterrogationPlanCategory) {
            $category->setTypeField(null);
            $category->setLatField(null);
            $category->setLongField(null);
            $category->setAlertField(null);
        }
        if($category instanceof FieldCategory) {
            $category->setNameField(null);
            $category->setCodeField(null);
            $category->setLatField(null);
            $category->setLongField(null);
        }
        if($category instanceof ATFCMCategory) {
            $category->setReasonField(null);
            $category->setDescriptionField(null);
            $category->setInternalId(null);
            $category->setNormalRateField(null);
            $category->setRegulationStateField(null);
        }
        //suppression des customfields, cascade ne semble plus fonctionner
        foreach ($category->getCustomfields() as $cf) {
            $this->getEntityManager()->remove($cf);
        }
        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();
        // suppression des evts associés par cascade
        $this->getEntityManager()->remove($category);
        $this->getEntityManager()->flush();
    }
}